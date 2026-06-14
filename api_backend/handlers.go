		package main

		import (
		"database/sql"
		"encoding/json"
		"math/rand"
		"net/http"
		"time"
		"golang.org/x/crypto/bcrypt"
	)
		// --- GESTION DES RÔLES ---
		func handleRoles(w http.ResponseWriter, r *http.Request) {
			if r.Method == "GET" {
				lignes, err := bd.Query("SELECT id_role, libelle_role FROM roles")
				if err != nil {
					http.Error(w, "Erreur lors de la récupération des rôles", http.StatusInternalServerError)
					return
				}
				var res []Role
				for lignes.Next() {
					var rl Role
					lignes.Scan(&rl.Id, &rl.Lib)
					res = append(res, rl)
				}
				w.Header().Set("Content-Type", "application/json")
				json.NewEncoder(w).Encode(res)
			}
		}

		// --- GESTION DES UTILISATEURS (Admin Total) ---
		func handleUsers(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")

			switch r.Method {
			case "GET":
				// On récupère tous les utilisateurs avec leur rôle (jointure avec la table roles)
				// On ajoute aussi est_verifie pour savoir si le compte est activé par mail
				lignes, err := bd.Query(`
					SELECT u.id_user, u.nom, u.prenom, u.email, u.est_actif, r.libelle_role, u.id_role, u.score_upcycling, COALESCE(u.onesignal_player_id, ''), u.est_verifie, COALESCE(u.solde,0)
					FROM utilisateurs u 
					JOIN roles r ON u.id_role = r.id_role`)
				if err != nil {
					http.Error(w, "Erreur SQL lors de la lecture", http.StatusInternalServerError)
					return
				}
				var res []User
				for lignes.Next() {
					var u User
					// On scanne tous les champs y compris est_verifie
					lignes.Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail, &u.EstActif, &u.Role, &u.IdRole, &u.ScoreUpcycling, &u.OneSignalId, &u.EstVerifie, &u.Solde)
					res = append(res, u)
				}
				json.NewEncoder(w).Encode(res)

			case "PUT", "DELETE":
				// Sécurité : on vérifie que l'utilisateur ciblé n'est pas un admin
				// Si c'est un admin, on bloque l'action
				var targetRoleID int
				err := bd.QueryRow("SELECT id_role FROM utilisateurs WHERE id_user = ?", id).Scan(&targetRoleID)
				if err == nil && targetRoleID == 1 {
					w.WriteHeader(http.StatusForbidden)
					json.NewEncoder(w).Encode(map[string]string{"error": "Sécurité : Impossible de modifier ou supprimer un Administrateur."})
					return
				}
				if r.Method == "PUT" {
					var u User
					json.NewDecoder(r.Body).Decode(&u)
					if u.Mdp != "" {
						
						hash, _ := bcrypt.GenerateFromPassword([]byte(u.Mdp), bcrypt.DefaultCost)
						bd.Exec(`UPDATE utilisateurs SET nom=?, prenom=?, email=?, id_role=?, est_actif=?, score_upcycling=?, onesignal_player_id=?, est_verifie=?, mot_de_passe=? WHERE id_user=?`,
							u.Nom, u.Pre, u.Mail, u.IdRole, u.EstActif, u.ScoreUpcycling, u.OneSignalId, u.EstVerifie, string(hash), id)
					} else {
						
						bd.Exec(`UPDATE utilisateurs SET nom=?, prenom=?, email=?, id_role=?, est_actif=?, score_upcycling=?, onesignal_player_id=?, est_verifie=? WHERE id_user=?`,
							u.Nom, u.Pre, u.Mail, u.IdRole, u.EstActif, u.ScoreUpcycling, u.OneSignalId, u.EstVerifie, id)
					}
					w.WriteHeader(http.StatusOK)
				} else {
					// On supprime toutes les données liées AVANT de supprimer l'utilisateur
					// (sinon les clés étrangères empêchent la suppression)
					// L'ordre est important : on supprime les "enfants" avant les "parents"

					// 1. On libère les casiers occupés par ses demandes de dépôt
					//    (les objets physiques seront retirés par les salariés sur le terrain)
					bd.Exec("UPDATE casiers SET statut = 'libre' WHERE id_casier IN (SELECT id_casier FROM demandes_depot WHERE id_user = ? AND id_casier IS NOT NULL)", id)

					// 2. On supprime les étapes des projets de l'artisan, puis les projets
					bd.Exec("DELETE FROM etapes_projet WHERE id_projet IN (SELECT id_projet FROM projets WHERE id_createur = ?)", id)
					bd.Exec("DELETE FROM projets WHERE id_createur = ?", id)

					// 3. On supprime ses prestations (créations en vente)
					bd.Exec("DELETE FROM prestations WHERE id_createur = ?", id)

					// 4. On supprime ses mouvements de portefeuille et transactions
					bd.Exec("DELETE FROM mouvements_portefeuille WHERE id_user = ?", id)
					bd.Exec("DELETE FROM transactions WHERE id_user = ?", id)

					// 5. On supprime ses inscriptions aux ateliers
					bd.Exec("DELETE FROM inscriptions WHERE id_user = ?", id)

					// 6. On supprime ses annonces
					bd.Exec("DELETE FROM annonces WHERE id_user_auteur = ?", id)

					// 7. On supprime ses messages de forum (et les réponses à ses messages)
					bd.Exec("DELETE FROM message_forums WHERE id_user_auteur = ?", id)

					// 8. On supprime ses demandes de dépôt (celles qu'il a créées en tant que particulier,
					//    et celles qu'il a réservées en tant qu'artisan)
					bd.Exec("DELETE FROM demandes_depot WHERE id_user = ?", id)
					bd.Exec("UPDATE demandes_depot SET id_artisan = NULL, code_artisan = NULL WHERE id_artisan = ?", id)

					// 9. Enfin, on supprime l'utilisateur lui-même
					_, err := bd.Exec("DELETE FROM utilisateurs WHERE id_user = ?", id)
					if err != nil {
						w.WriteHeader(http.StatusConflict)
						json.NewEncoder(w).Encode(map[string]string{"error": "Impossible de supprimer cet utilisateur."})
						return
					}
					w.WriteHeader(http.StatusOK)
				}

			case "POST":
				// Création d'un utilisateur par l'admin (différent de l'inscription normale)
				var u User
				if err := json.NewDecoder(r.Body).Decode(&u); err != nil {
					w.WriteHeader(http.StatusBadRequest)
					return
				}
				// On hashé le mot de passe avant de le stocker
				hash, err := bcrypt.GenerateFromPassword([]byte(u.Mdp), bcrypt.DefaultCost)
				if err != nil {
					w.WriteHeader(http.StatusInternalServerError)
					return
				}
				_, err = bd.Exec(`INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, id_role, est_actif, score_upcycling, onesignal_player_id, est_verifie) VALUES (?,?,?,?,?,1,?,?,?)`,
					u.Nom, u.Pre, u.Mail, string(hash), u.IdRole, u.ScoreUpcycling, u.OneSignalId, u.EstVerifie)
				if err != nil {
					w.WriteHeader(http.StatusInternalServerError)
					json.NewEncoder(w).Encode(map[string]string{"error": "Erreur SQL ou email déjà pris."})
					return
				}
				w.WriteHeader(http.StatusCreated)
			}
		}

		// --- GESTION DES CATÉGORIES ---
		func handleCategories(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				lignes, _ := bd.Query("SELECT id_cat, code_ref_cat FROM categories")
				var res []Categories
				for lignes.Next() {
					var c Categories
					lignes.Scan(&c.Id, &c.Nom)
					res = append(res, c)
				}
				json.NewEncoder(w).Encode(res)
			case "POST":
				var c Categories
				json.NewDecoder(r.Body).Decode(&c)
				bd.Exec("INSERT INTO categories (code_ref_cat) VALUES (?)", c.Nom)
				w.WriteHeader(http.StatusCreated)
			case "PUT":
				var c Categories
				json.NewDecoder(r.Body).Decode(&c)
				bd.Exec("UPDATE categories SET code_ref_cat=? WHERE id_cat=?", c.Nom, id)
				w.WriteHeader(http.StatusOK)
			case "DELETE":
				bd.Exec("DELETE FROM categories WHERE id_cat=?", id)
				w.WriteHeader(http.StatusOK)
			}
		}

		// --- GESTION DES PRESTATIONS ---
		func handlePrestations(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")

			switch r.Method {
			case "GET":
			// L'artisan/salarié voit ses propres prestations (même vendues), l'admin et particulier voient le catalogue (sans les vendues)
			idCreateur := r.URL.Query().Get("id_createur")
			var lignes *sql.Rows
			if idCreateur != "" {
				// L'artisan voit toutes ses prestations, y compris celles déjà vendues (pour son historique)
				lignes, _ = bd.Query("SELECT p.id_prestation, p.nom_prestation, p.prix, COALESCE(p.description,''), COALESCE(p.photo,''), COALESCE(p.id_createur,0), COALESCE(u.nom,''), p.statut_validation, COALESCE(p.motif_refus,''), COALESCE(p.vendu,0) FROM prestations p LEFT JOIN utilisateurs u ON p.id_createur = u.id_user WHERE p.id_createur = ?", idCreateur)
			} else {
				// Le catalogue ne montre que les prestations pas encore vendues
				lignes, _ = bd.Query("SELECT p.id_prestation, p.nom_prestation, p.prix, COALESCE(p.description,''), COALESCE(p.photo,''), COALESCE(p.id_createur,0), COALESCE(u.nom,''), p.statut_validation, COALESCE(p.motif_refus,''), COALESCE(p.vendu,0) FROM prestations p LEFT JOIN utilisateurs u ON p.id_createur = u.id_user WHERE p.vendu = 0")
			}
			var res []Prestations
			for lignes.Next() {
				var p Prestations
				lignes.Scan(&p.Id, &p.Nom, &p.Prix, &p.Desc, &p.Photo, &p.IdCreateur, &p.Createur, &p.StatutValidation, &p.MotifRefus, &p.Vendu)
				res = append(res, p)
			}
			json.NewEncoder(w).Encode(res)
			case "POST":
				var p Prestations
				json.NewDecoder(r.Body).Decode(&p)
				bd.Exec("INSERT INTO prestations (nom_prestation, prix, description, photo, id_createur) VALUES (?,?,?,?,?)", p.Nom, p.Prix, p.Desc, p.Photo, p.IdCreateur)
				w.WriteHeader(http.StatusCreated)

			case "PUT":
				var p Prestations
				json.NewDecoder(r.Body).Decode(&p)
				if p.MotifRefus != "" {
					// L'admin refuse avec un motif
					bd.Exec("UPDATE prestations SET statut_validation = 2, motif_refus = ? WHERE id_prestation = ?", p.MotifRefus, id)
				} else if p.Nom != "" {
					// Le créateur modifie sa prestation
					bd.Exec("UPDATE prestations SET nom_prestation=?, prix=?, description=?, photo=? WHERE id_prestation=?", p.Nom, p.Prix, p.Desc, p.Photo, id)
				} else {
					// L'admin valide
					bd.Exec("UPDATE prestations SET statut_validation = 1 WHERE id_prestation = ?", id)
				}
				w.WriteHeader(http.StatusOK)

			case "DELETE":
				bd.Exec("DELETE FROM prestations WHERE id_prestation=?", id)
				w.WriteHeader(http.StatusOK)
			}
		}

		// --- GESTION DES ÉVÉNEMENTS ---
		func handleEvenements(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				lignes, _ := bd.Query(`
				SELECT e.id_event, e.titre, e.date_debut, e.prix_actuel, e.places_max, 
				e.statut_validation, u.nom, e.id_animateur,
				COUNT(i.id_inscription) as nb_inscrits, e.motif_refus
				FROM evenements e 
				JOIN utilisateurs u ON e.id_animateur = u.id_user
				LEFT JOIN inscriptions i ON e.id_event = i.id_event
				GROUP BY e.id_event
		`)
				var res []Evenements
				for lignes.Next() {
					var e Evenements
					lignes.Scan(&e.Id, &e.Titre, &e.Date, &e.Prix, &e.Place, &e.StatutValidation, &e.Anim, &e.IdAnim, &e.NbInscrits, &e.MotifRefus)
					res = append(res, e)
				}
				json.NewEncoder(w).Encode(res)
			case "PUT":
				var e Evenements
				json.NewDecoder(r.Body).Decode(&e)
				if e.MotifRefus != "" {
					bd.Exec("UPDATE evenements SET statut_validation = 2, motif_refus = ? WHERE id_event = ?", e.MotifRefus, id)
				} else {
					bd.Exec("UPDATE evenements SET titre=?, date_debut=?, prix_actuel=?, places_max=?, id_animateur=? WHERE id_event=?", e.Titre, e.Date, e.Prix, e.Place, e.IdAnim, id)
				}
				w.WriteHeader(http.StatusOK)
			case "DELETE":
				bd.Exec("DELETE FROM evenements WHERE id_event=?", id)
				w.WriteHeader(http.StatusOK)

			case "POST":
				var e Evenements
				json.NewDecoder(r.Body).Decode(&e)
				bd.Exec("INSERT INTO evenements (titre, date_debut, prix_actuel, places_max, id_animateur) VALUES (?,?,?,?,?)",
					e.Titre, e.Date, e.Prix, e.Place, e.IdAnim)
				w.WriteHeader(http.StatusCreated)
			}

		}

		func handleValidation(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			bd.Exec("UPDATE evenements SET statut_validation = 1 WHERE id_event = ?", id)
			w.WriteHeader(http.StatusOK)
		}

		// --- GESTION DES ANNONCES ---
		func handleAnnonces(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				idUser := r.URL.Query().Get("id_user")
				var lignes *sql.Rows
				if idUser != "" {
					lignes, _ = bd.Query("SELECT id_annonce, titre, description, categorie, type_annonce, prix, statut_validation, statut_annonce, photo, motif_refus FROM annonces WHERE id_user_auteur = ?", idUser)
					var res []Annonce
					for lignes.Next() {
						var a Annonce
						lignes.Scan(&a.Id, &a.Titre, &a.Description, &a.Categorie, &a.TypeOffre, &a.Prix, &a.StatutValidation, &a.StatutAnnonce, &a.Photo, &a.MotifRefus)
						res = append(res, a)
					}
					json.NewEncoder(w).Encode(res)
				} else {
					// Sans id_user, l'admin voit toutes les annonces avec le nom de l'auteur
					lignes, _ = bd.Query(`SELECT a.id_annonce, a.titre, a.description, a.categorie, a.type_annonce, a.prix, a.photo, a.statut_validation, u.nom 
					FROM annonces a JOIN utilisateurs u ON a.id_user_auteur = u.id_user`)
					var res []Annonce
					for lignes.Next() {
						var a Annonce
						lignes.Scan(&a.Id, &a.Titre, &a.Description, &a.Categorie, &a.TypeOffre, &a.Prix, &a.Photo, &a.StatutValidation, &a.Auteur)
						res = append(res, a)
					}
					json.NewEncoder(w).Encode(res)
				}
			case "POST":
				var a Annonce
				json.NewDecoder(r.Body).Decode(&a)
				// On insère l'annonce avec la photo (chemin du fichier uploadé)
				bd.Exec("INSERT INTO annonces (titre, description, categorie, type_annonce, prix, id_user_auteur, photo) VALUES (?,?,?,?,?,?,?)",
					a.Titre, a.Description, a.Categorie, a.TypeOffre, a.Prix, a.IdUser, a.Photo)
				// On ajoute des points au score : +10 pour une vente, +20 pour un don
				points := 10
				if a.TypeOffre == "don" {
					points = 20
				}
				bd.Exec("UPDATE utilisateurs SET score_upcycling = score_upcycling + ? WHERE id_user = ?", points, a.IdUser)
				w.WriteHeader(http.StatusCreated)
			case "PUT":
				var a Annonce
				json.NewDecoder(r.Body).Decode(&a)
				if a.Titre != "" {
					if a.Photo != "" {
						// Si une nouvelle photo est fournie, on la met à jour aussi
						bd.Exec("UPDATE annonces SET titre=?, description=?, categorie=?, type_annonce=?, prix=?, photo=? WHERE id_annonce=?",
							a.Titre, a.Description, a.Categorie, a.TypeOffre, a.Prix, a.Photo, id)
					} else {
						// Pas de nouvelle photo, on touche pas à la photo existante
						bd.Exec("UPDATE annonces SET titre=?, description=?, categorie=?, type_annonce=?, prix=? WHERE id_annonce=?",
							a.Titre, a.Description, a.Categorie, a.TypeOffre, a.Prix, id)
					}
				} else {
					// L'admin valide ou refuse l'annonce
					bd.Exec("UPDATE annonces SET statut_validation = ? WHERE id_annonce = ?", a.StatutValidation, id)
				}
				w.WriteHeader(http.StatusOK)

			case "DELETE":
				// Avant de supprimer, on récupère le type et l'auteur pour déduire les points
				var idUser int
				var typeAnnonce string
				bd.QueryRow("SELECT id_user_auteur, type_annonce FROM annonces WHERE id_annonce = ?", id).Scan(&idUser, &typeAnnonce)
				points := 10
				if typeAnnonce == "don" {
					points = 20
				}
				// On retire les points du score avant de supprimer
				bd.Exec("UPDATE utilisateurs SET score_upcycling = score_upcycling - ? WHERE id_user = ?", points, idUser)
				bd.Exec("DELETE FROM annonces WHERE id_annonce = ?", id)
				w.WriteHeader(http.StatusOK)
			}
		}

		// --- GESTION DES BOX ---
		func handleBox(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				lignes, _ := bd.Query("SELECT id_box, adresse, capacite_max FROM box")
				var res []Box
				for lignes.Next() {
					var b Box
					lignes.Scan(&b.Id, &b.Adresse, &b.CapaciteMax)
					res = append(res, b)
				}
				json.NewEncoder(w).Encode(res)
			case "POST":
				var b Box
				json.NewDecoder(r.Body).Decode(&b)
				bd.Exec("INSERT INTO box (adresse, capacite_max) VALUES (?,?)", b.Adresse, b.CapaciteMax)
				w.WriteHeader(http.StatusCreated)
			case "DELETE":
		bd.Exec("DELETE FROM demandes_depot WHERE id_box = ?", id)
		bd.Exec("DELETE FROM box WHERE id_box = ?", id)
		w.WriteHeader(http.StatusOK)
			}
		}
		func handleTransactions(w http.ResponseWriter, r *http.Request) {
			w.Header().Set("Content-Type", "application/json")
			
			switch r.Method {
			case "GET":
				idUser := r.URL.Query().Get("id_user")
				var lignes *sql.Rows
				if idUser != "" {
					lignes, _ = bd.Query("SELECT id_transac, COALESCE(id_user,0), montant, COALESCE(reference_stripe,''), statut_paiement, COALESCE(type,''), COALESCE(commission,0), date_transac FROM transactions WHERE id_user = ?", idUser)
				} else {
					lignes, _ = bd.Query("SELECT id_transac, COALESCE(id_user,0), montant, COALESCE(reference_stripe,''), statut_paiement, COALESCE(type,''), COALESCE(commission,0), date_transac FROM transactions")
				}
				var res []Transaction
				for lignes.Next() {
					var t Transaction
					lignes.Scan(&t.Id, &t.IdUser, &t.Montant, &t.RefStripe, &t.Statut, &t.Type, &t.Commission, &t.Date)
					res = append(res, t)
				}
				json.NewEncoder(w).Encode(res)

			case "PUT":
				id := r.PathValue("id")
				var t Transaction
				json.NewDecoder(r.Body).Decode(&t)
				bd.Exec("UPDATE transactions SET statut_paiement = ? WHERE id_transac = ?", t.Statut, id)
				w.WriteHeader(http.StatusOK)
			}
		}

		func handleAbonnements(w http.ResponseWriter, r *http.Request) {
			w.Header().Set("Content-Type", "application/json")
			if r.Method == "GET" {
				lignes, _ := bd.Query("SELECT id_type_abo, nom_offre, prix_mensuel_actuel FROM types_abonnements")
				var res []TypeAbonnement
				for lignes.Next() {
					var a TypeAbonnement
					lignes.Scan(&a.Id, &a.Nom, &a.Prix)
					res = append(res, a)
				}
				json.NewEncoder(w).Encode(res)
			}
		}

		func handleMessages(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				lignes, _ := bd.Query(`SELECT m.id_message, m.contenu, u.nom, m.id_user_auteur, 
					COALESCE(m.id_message_parent, 0), m.date_message, m.est_modere 
					FROM message_forums m JOIN utilisateurs u ON m.id_user_auteur = u.id_user 
					ORDER BY m.date_message ASC`)
				var res []ForumMessage
				for lignes.Next() {
					var m ForumMessage
					lignes.Scan(&m.Id, &m.Contenu, &m.Auteur, &m.IdUser, &m.IdMessageParent, &m.Date, &m.EstModere)
					res = append(res, m)
				}
				json.NewEncoder(w).Encode(res)
			case "POST":
				var m ForumMessage
				json.NewDecoder(r.Body).Decode(&m)
				if m.IdMessageParent > 0 {
					// Réponse à un message
					bd.Exec("INSERT INTO message_forums (contenu, id_user_auteur, id_message_parent) VALUES (?, ?, ?)", m.Contenu, m.IdUser, m.IdMessageParent)
				} else {
					// Message principal
					bd.Exec("INSERT INTO message_forums (contenu, id_user_auteur) VALUES (?, ?)", m.Contenu, m.IdUser)
				}
				w.WriteHeader(http.StatusCreated)
			case "PUT":
				var m ForumMessage
				json.NewDecoder(r.Body).Decode(&m)
				bd.Exec("UPDATE message_forums SET est_modere = ? WHERE id_message = ?", m.EstModere, id)
				w.WriteHeader(http.StatusOK)
			case "DELETE":
				// Supprimer aussi les réponses
				bd.Exec("DELETE FROM message_forums WHERE id_message_parent = ?", id)
				bd.Exec("DELETE FROM message_forums WHERE id_message = ?", id)
				w.WriteHeader(http.StatusOK)
			}
		}
		// --- GESTION DES LANGUES ---
		func handleLangues(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				lignes, _ := bd.Query("SELECT id_langue, code_iso, nom_langue FROM langues")
				var res []Langue
				for lignes.Next() {
					var l Langue
					lignes.Scan(&l.Id, &l.Code, &l.Nom)
					res = append(res, l)
				}
				json.NewEncoder(w).Encode(res)
			case "POST":
				var l Langue
				json.NewDecoder(r.Body).Decode(&l)
				bd.Exec("INSERT INTO langues (code_iso, nom_langue) VALUES (?, ?)", l.Code, l.Nom)
				w.WriteHeader(http.StatusCreated)
			case "DELETE":
				// Supprimer les traductions liées d'abord
				bd.Exec("DELETE FROM traductions WHERE id_langue = ?", id)
				bd.Exec("DELETE FROM langues WHERE id_langue = ?", id)
				w.WriteHeader(http.StatusOK)
			}
		}


		func handleTraductions(w http.ResponseWriter, r *http.Request) {
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				idLangue := r.URL.Query().Get("id_langue")
				var lignes *sql.Rows
				if idLangue != "" {
					// Récupérer les traductions d'une langue précise (pour l'affichage côté front)
					lignes, _ = bd.Query("SELECT id_traduction, cle, id_langue, texte FROM traductions WHERE id_langue = ?", idLangue)
				} else {
					// Toutes les traductions (pour l'admin)
					lignes, _ = bd.Query("SELECT id_traduction, cle, id_langue, texte FROM traductions")
				}
				var res []Traduction
				for lignes.Next() {
					var t Traduction
					lignes.Scan(&t.Id, &t.Cle, &t.IdLangue, &t.Texte)
					res = append(res, t)
				}
				json.NewEncoder(w).Encode(res)

			case "POST":
				// Créer ou mettre à jour une traduction (un upsert)
				var t Traduction
				json.NewDecoder(r.Body).Decode(&t)
				bd.Exec("INSERT INTO traductions (cle, id_langue, texte) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE texte = ?",
					t.Cle, t.IdLangue, t.Texte, t.Texte)
				w.WriteHeader(http.StatusOK)
			}
		}

		// --- ACTIVATION DU COMPTE PAR TOKEN ---
		func handleVerify(w http.ResponseWriter, r *http.Request) {
			token := r.PathValue("token")
			// On cherche l'utilisateur avec ce token et on active son compte
			res, _ := bd.Exec("UPDATE utilisateurs SET est_verifie = 1 WHERE token_verification = ?", token)
			lignesImpactees, _ := res.RowsAffected()
			if lignesImpactees > 0 {
				w.WriteHeader(http.StatusOK) // Token trouvé, compte activé
			} else {
				w.WriteHeader(http.StatusNotFound) // Token non trouvé ou déjà utilisé
			}
		}

	func handleDemandesBox(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")

			switch r.Method {
			case "GET":
			lignes, _ := bd.Query(`
				SELECT d.id_demande, d.id_user, d.id_box, d.statut_check, 
				COALESCE(d.code_ouverture, ''), COALESCE(d.code_barre_scan, ''), 
				COALESCE(d.code_artisan, ''), COALESCE(d.motif_refus, ''), 
				COALESCE(d.date_demande, ''), u.nom, b.adresse, 
				COALESCE(o.description, ''), COALESCE(d.id_casier, 0),
				COALESCE(c.numero, ''), COALESCE(d.id_annonce, 0),
				COALESCE(a.titre, ''), COALESCE(d.id_artisan, 0)
				FROM demandes_depot d
				JOIN utilisateurs u ON d.id_user = u.id_user
				JOIN box b ON d.id_box = b.id_box
				JOIN objets o ON d.id_objet = o.id_objet
				LEFT JOIN casiers c ON d.id_casier = c.id_casier
				LEFT JOIN annonces a ON d.id_annonce = a.id_annonce
			`)
			var res []DemandeBox
			for lignes.Next() {
				var d DemandeBox
				lignes.Scan(&d.Id, &d.IdUser, &d.IdBox, &d.Statut, &d.CodeOuverture, &d.CodeBarre, &d.CodeArtisan, &d.MotifRefus, &d.Date, &d.NomUser, &d.AdresseBox, &d.Description, &d.IdCasier, &d.NumeroCasier, &d.IdAnnonce, &d.TitreAnnonce, &d.IdArtisan)
				res = append(res, d)
			}
			json.NewEncoder(w).Encode(res)

			case "POST":
				// Le particulier crée une demande de dépôt liée à une annonce validée
				var d DemandeBox
				json.NewDecoder(r.Body).Decode(&d)
				// On récupère la description et la photo depuis l'annonce pour créer l'objet
				var desc, photo string
				bd.QueryRow("SELECT COALESCE(description,''), COALESCE(photo,'') FROM annonces WHERE id_annonce = ?", d.IdAnnonce).Scan(&desc, &photo)
				// On crée l'objet
				result, _ := bd.Exec("INSERT INTO objets (description) VALUES (?)", desc)
				idObjet, _ := result.LastInsertId()
				// On crée la demande liée à l'annonce
				bd.Exec("INSERT INTO demandes_depot (id_user, id_objet, id_box, id_annonce) VALUES (?,?,?,?)", d.IdUser, idObjet, d.IdBox, d.IdAnnonce)
				w.WriteHeader(http.StatusCreated)

			case "PUT":
				var d DemandeBox
				json.NewDecoder(r.Body).Decode(&d)

				if d.MotifRefus != "" {
					// Admin refuse avec motif
					bd.Exec("UPDATE demandes_depot SET statut_check = 'refuse', motif_refus = ? WHERE id_demande = ?", d.MotifRefus, id)
				} else if d.Statut == "depose" {
					// Particulier confirme qu'il a déposé l'objet → l'annonce devient visible aux artisans
					bd.Exec("UPDATE demandes_depot SET statut_check = 'depose' WHERE id_demande = ?", id)
				} else {
					// Admin valide la demande : on cherche un casier libre, on génère les codes
					var idCasier int
					var numeroCasier string
					err := bd.QueryRow("SELECT id_casier, numero FROM casiers WHERE id_box = ? AND statut = 'libre' LIMIT 1", d.IdBox).Scan(&idCasier, &numeroCasier)
					if err != nil {
						w.WriteHeader(http.StatusBadRequest)
						json.NewEncoder(w).Encode(map[string]string{"error": "Aucun casier disponible dans cette box."})
						return
					}
					code := genererCode(6)
					codeBarre := genererCode(12)
					bd.Exec("UPDATE casiers SET statut = 'occupe' WHERE id_casier = ?", idCasier)
					bd.Exec("UPDATE demandes_depot SET statut_check = 'valide', code_ouverture = ?, code_barre_scan = ?, id_casier = ? WHERE id_demande = ?", code, codeBarre, idCasier, id)
					w.WriteHeader(http.StatusOK)
					json.NewEncoder(w).Encode(map[string]string{"code": code, "code_barre": codeBarre, "casier": numeroCasier})
					return
				}
				w.WriteHeader(http.StatusOK)

			case "DELETE":
				var idCasier int
				bd.QueryRow("SELECT id_casier FROM demandes_depot WHERE id_demande = ?", id).Scan(&idCasier)
				if idCasier > 0 {
					bd.Exec("UPDATE casiers SET statut = 'libre' WHERE id_casier = ?", idCasier)
				}
				bd.Exec("DELETE FROM demandes_depot WHERE id_demande = ?", id)
				w.WriteHeader(http.StatusOK)
			}
		}

	func genererCode(longueur int) string {
		const lettres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
		rand.Seed(time.Now().UnixNano())
		code := make([]byte, longueur)
		for i := range code {
			code[i] = lettres[rand.Intn(len(lettres))]
		}
		return string(code)
	}

	func handleCasiers(w http.ResponseWriter, r *http.Request) {
		id := r.PathValue("id")
		w.Header().Set("Content-Type", "application/json")
		switch r.Method {
		case "GET":
			idBox := r.URL.Query().Get("id_box")
			var lignes *sql.Rows
			if idBox != "" {
				lignes, _ = bd.Query("SELECT id_casier, numero, statut, id_box FROM casiers WHERE id_box = ?", idBox)
			} else {
				lignes, _ = bd.Query("SELECT id_casier, numero, statut, id_box FROM casiers")
			}
			var res []Casier
			for lignes.Next() {
				var c Casier
				lignes.Scan(&c.Id, &c.Numero, &c.Statut, &c.IdBox)
				res = append(res, c)
			}
			json.NewEncoder(w).Encode(res)
		case "POST":
			var c Casier
			json.NewDecoder(r.Body).Decode(&c)

			// 1. On vérifie que la box n'est pas déjà pleine (nombre de casiers < capacité max)
			var nbCasiers, capaciteMax int
			bd.QueryRow("SELECT COUNT(*) FROM casiers WHERE id_box = ?", c.IdBox).Scan(&nbCasiers)
			bd.QueryRow("SELECT capacite_max FROM box WHERE id_box = ?", c.IdBox).Scan(&capaciteMax)
			if nbCasiers >= capaciteMax {
				w.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(w).Encode(map[string]string{"error": "Cette box est pleine (capacité maximale atteinte)."})
				return
			}

			// 2. On vérifie qu'un casier avec ce numéro n'existe pas déjà dans cette box
			var existe int
			bd.QueryRow("SELECT COUNT(*) FROM casiers WHERE id_box = ? AND numero = ?", c.IdBox, c.Numero).Scan(&existe)
			if existe > 0 {
				w.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(w).Encode(map[string]string{"error": "Un casier avec ce numéro existe déjà dans cette box."})
				return
			}

			// Tout est bon, on crée le casier
			bd.Exec("INSERT INTO casiers (numero, id_box) VALUES (?,?)", c.Numero, c.IdBox)
			w.WriteHeader(http.StatusCreated)
		case "DELETE":
			bd.Exec("DELETE FROM casiers WHERE id_casier = ?", id)
			w.WriteHeader(http.StatusOK)
		}
	}

		// --- GESTION DES INSCRIPTIONS ---
		func handleInscriptions(w http.ResponseWriter, r *http.Request) {
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				idUser := r.URL.Query().Get("id_user")
				lignes, _ := bd.Query("SELECT i.id_inscription, i.id_event, e.titre, e.date_debut, e.prix_actuel FROM inscriptions i JOIN evenements e ON i.id_event = e.id_event WHERE i.id_user = ?", idUser)
				var res []Inscription
				for lignes.Next() {
					var i Inscription
					lignes.Scan(&i.Id, &i.IdEvent, &i.Titre, &i.Date, &i.Prix)
					res = append(res, i)
				}
				json.NewEncoder(w).Encode(res)

			case "POST":
				var i Inscription
				json.NewDecoder(r.Body).Decode(&i)

				// Vérifier qu'il reste des places
				var places int
				bd.QueryRow("SELECT places_max FROM evenements WHERE id_event = ?", i.IdEvent).Scan(&places)
				if places <= 0 {
					w.WriteHeader(http.StatusBadRequest)
					json.NewEncoder(w).Encode(map[string]string{"error": "Cet atelier est complet."})
					return
				}

				// Insérer l'inscription
				bd.Exec("INSERT INTO inscriptions (id_user, id_event) VALUES (?,?)", i.IdUser, i.IdEvent)
				// Décrémenter les places
				bd.Exec("UPDATE evenements SET places_max = places_max - 1 WHERE id_event = ?", i.IdEvent)
				w.WriteHeader(http.StatusCreated)

			case "DELETE":
				id := r.PathValue("id")
				// Récupérer l'id_event avant de supprimer
				var idEvent int
				bd.QueryRow("SELECT id_event FROM inscriptions WHERE id_inscription = ?", id).Scan(&idEvent)
				// Remettre la place
				bd.Exec("UPDATE evenements SET places_max = places_max + 1 WHERE id_event = ?", idEvent)
				// Supprimer l'inscription
				bd.Exec("DELETE FROM inscriptions WHERE id_inscription = ?", id)
				w.WriteHeader(http.StatusOK)
			}
		}

		func handleInscritsEvenement(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			lignes, _ := bd.Query(`
				SELECT u.id_user, u.nom, u.prenom, u.email 
				FROM inscriptions i 
				JOIN utilisateurs u ON i.id_user = u.id_user 
				WHERE i.id_event = ?`, id)
			var res []User
			for lignes.Next() {
				var u User
				lignes.Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail)
				res = append(res, u)
			}
			json.NewEncoder(w).Encode(res)
		}

		// --- GESTION DES CONSEILS ---
		func handleConseils(w http.ResponseWriter, r *http.Request) {
			id := r.PathValue("id")
			w.Header().Set("Content-Type", "application/json")
			switch r.Method {
			case "GET":
				idAuteur := r.URL.Query().Get("id_auteur")
				var lignes *sql.Rows
				if idAuteur != "" {
					// Le salarié voit seulement ses propres articles
					lignes, _ = bd.Query(`SELECT a.id_article, a.titre, a.contenu, a.type, a.date_creation, u.nom, a.id_auteur
						FROM article_conseil a JOIN utilisateurs u ON a.id_auteur = u.id_user
						WHERE a.id_auteur = ?`, idAuteur)
				} else {
					// Tout le monde voit tous les articles
					lignes, _ = bd.Query(`SELECT a.id_article, a.titre, a.contenu, a.type, a.date_creation, u.nom, a.id_auteur
						FROM article_conseil a JOIN utilisateurs u ON a.id_auteur = u.id_user`)
				}
				var res []ArticleConseil
				for lignes.Next() {
					var a ArticleConseil
					lignes.Scan(&a.Id, &a.Titre, &a.Contenu, &a.Type, &a.Date, &a.Auteur, &a.IdAuteur)
					res = append(res, a)
				}
				json.NewEncoder(w).Encode(res)

			case "POST":
				var a ArticleConseil
				json.NewDecoder(r.Body).Decode(&a)
				bd.Exec("INSERT INTO article_conseil (titre, contenu, type, id_auteur) VALUES (?,?,?,?)", a.Titre, a.Contenu, a.Type, a.IdAuteur)
				w.WriteHeader(http.StatusCreated)

			case "PUT":
				var a ArticleConseil
				json.NewDecoder(r.Body).Decode(&a)
				bd.Exec("UPDATE article_conseil SET titre=?, contenu=?, type=? WHERE id_article=?", a.Titre, a.Contenu, a.Type, id)
				w.WriteHeader(http.StatusOK)

			case "DELETE":
				bd.Exec("DELETE FROM article_conseil WHERE id_article=?", id)
				w.WriteHeader(http.StatusOK)
			}
		}


func handleCatalogueArtisan(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	// On récupère les annonces dont l'objet est déposé en box et pas encore réservé
	lignes, _ := bd.Query(`
		SELECT d.id_demande, a.titre, COALESCE(a.description,''), COALESCE(a.categorie,''), 
		COALESCE(a.type_annonce,''), COALESCE(a.prix,0), COALESCE(a.photo,''),
		u.nom, b.adresse, COALESCE(c.numero,'')
		FROM demandes_depot d
		JOIN annonces a ON d.id_annonce = a.id_annonce
		JOIN utilisateurs u ON d.id_user = u.id_user
		JOIN box b ON d.id_box = b.id_box
		LEFT JOIN casiers c ON d.id_casier = c.id_casier
		WHERE d.statut_check = 'depose' AND d.id_artisan IS NULL
	`)
	var res []ObjetCatalogue
	for lignes.Next() {
		var o ObjetCatalogue
		lignes.Scan(&o.IdDemande, &o.Titre, &o.Description, &o.Categorie, &o.TypeOffre, &o.Prix, &o.Photo, &o.NomParticulier, &o.AdresseBox, &o.NumeroCasier)
		res = append(res, o)
	}
	json.NewEncoder(w).Encode(res)
}

// --- RÉCUPÉRATION D'UN OBJET PAR L'ARTISAN ---
func handleRecuperation(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != "POST" {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}

	var d DemandeBox
	json.NewDecoder(r.Body).Decode(&d)

	// On vérifie que la demande est encore dispo (pas déjà réservée par un autre artisan)
	var idArtisanActuel int
	bd.QueryRow("SELECT COALESCE(id_artisan, 0) FROM demandes_depot WHERE id_demande = ?", d.Id).Scan(&idArtisanActuel)
	if idArtisanActuel != 0 {
		w.WriteHeader(http.StatusConflict)
		json.NewEncoder(w).Encode(map[string]string{"error": "Cet objet a déjà été réservé par un autre artisan."})
		return
	}

	// On génère un code d'ouverture pour l'artisan
	codeArtisan := genererCode(6)

	// On réserve l'objet pour cet artisan avec son code d'ouverture
	bd.Exec("UPDATE demandes_depot SET id_artisan = ?, code_artisan = ? WHERE id_demande = ?", d.IdArtisan, codeArtisan, d.Id)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{
		"message":      "Objet réservé. Voici votre code pour ouvrir le casier.",
		"code_artisan": codeArtisan,
	})
}

// --- CONFIRMATION SCAN CODE-BARRES (artisan a récupéré l'objet) ---
func handleConfirmerRecup(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != "POST" {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}

	var d DemandeBox
	json.NewDecoder(r.Body).Decode(&d)

	// On vérifie que le code-barres saisi correspond bien à celui de la demande
	var codeAttendu string
	var idCasier int
	bd.QueryRow("SELECT COALESCE(code_barre_scan,''), COALESCE(id_casier,0) FROM demandes_depot WHERE id_demande = ?", d.Id).Scan(&codeAttendu, &idCasier)

	if d.CodeBarre != codeAttendu {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Code-barres incorrect."})
		return
	}

	// On valide la récupération
	bd.Exec("UPDATE demandes_depot SET statut_check = 'recupere' WHERE id_demande = ?", d.Id)

	// On libère le casier
	if idCasier > 0 {
		bd.Exec("UPDATE casiers SET statut = 'libre' WHERE id_casier = ?", idCasier)
	}

	// L'artisan gagne +5 points
	bd.Exec("UPDATE utilisateurs SET score_upcycling = score_upcycling + 5 WHERE id_user = ?", d.IdArtisan)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{"message": "Objet récupéré ! +5 points."})
}

// --- GESTION DES PROJETS (créations des artisans) ---
func handleProjets(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")

	switch r.Method {
	case "GET":
		// L'artisan voit ses propres projets, sinon on voit tout
		idCreateur := r.URL.Query().Get("id_createur")
		var lignes *sql.Rows
		if idCreateur != "" {
			lignes, _ = bd.Query(`SELECT p.id_projet, p.titre, COALESCE(p.description_generale,''), p.est_sponsorise, p.id_createur, u.nom 
				FROM projets p JOIN utilisateurs u ON p.id_createur = u.id_user 
				WHERE p.id_createur = ?`, idCreateur)
		} else {
			lignes, _ = bd.Query(`SELECT p.id_projet, p.titre, COALESCE(p.description_generale,''), p.est_sponsorise, p.id_createur, u.nom 
				FROM projets p JOIN utilisateurs u ON p.id_createur = u.id_user`)
		}
		var res []Projet
		for lignes.Next() {
			var p Projet
			lignes.Scan(&p.Id, &p.Titre, &p.Description, &p.EstSponsorise, &p.IdCreateur, &p.Createur)
			res = append(res, p)
		}
		json.NewEncoder(w).Encode(res)

	case "POST":
		var p Projet
		json.NewDecoder(r.Body).Decode(&p)
		bd.Exec("INSERT INTO projets (titre, description_generale, id_createur) VALUES (?, ?, ?)", p.Titre, p.Description, p.IdCreateur)
		w.WriteHeader(http.StatusCreated)

	case "PUT":
		var p Projet
		json.NewDecoder(r.Body).Decode(&p)
		bd.Exec("UPDATE projets SET titre=?, description_generale=? WHERE id_projet=?", p.Titre, p.Description, id)
		w.WriteHeader(http.StatusOK)

	case "DELETE":
		// Les étapes sont supprimées en cascade grâce à ON DELETE CASCADE
		bd.Exec("DELETE FROM projets WHERE id_projet=?", id)
		w.WriteHeader(http.StatusOK)
	}
}

// --- GESTION DES ÉTAPES DE PROJET (avant/après photos) ---
func handleEtapes(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")

	switch r.Method {
	case "GET":
		// On récupère les étapes d'un projet précis
		idProjet := r.URL.Query().Get("id_projet")
		lignes, _ := bd.Query(`SELECT id_etape, COALESCE(titre_etape,''), COALESCE(description_etape,''), COALESCE(image_etape,''), COALESCE(ordre,0), id_projet 
			FROM etapes_projet WHERE id_projet = ? ORDER BY ordre ASC`, idProjet)
		var res []Etape
		for lignes.Next() {
			var e Etape
			lignes.Scan(&e.Id, &e.Titre, &e.Description, &e.Image, &e.Ordre, &e.IdProjet)
			res = append(res, e)
		}
		json.NewEncoder(w).Encode(res)

	case "POST":
		var e Etape
		json.NewDecoder(r.Body).Decode(&e)
		bd.Exec("INSERT INTO etapes_projet (titre_etape, description_etape, image_etape, ordre, id_projet) VALUES (?, ?, ?, ?, ?)",
			e.Titre, e.Description, e.Image, e.Ordre, e.IdProjet)
		w.WriteHeader(http.StatusCreated)

	case "DELETE":
		bd.Exec("DELETE FROM etapes_projet WHERE id_etape=?", id)
		w.WriteHeader(http.StatusOK)
	}
}

// --- GESTION DE L'ABONNEMENT ARTISAN (Freemium / Premium) ---
func handleAbonnement(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	switch r.Method {
	case "GET":
		idUser := r.URL.Query().Get("id_user")
		var abo string
		var dateFin sql.NullString
		var annule, recompenseReclamee int
		bd.QueryRow(`SELECT COALESCE(abonnement,'gratuit'), date_fin_abonnement, COALESCE(abonnement_annule,0), COALESCE(recompense_reclamee,0) 
			FROM utilisateurs WHERE id_user = ?`, idUser).Scan(&abo, &dateFin, &annule, &recompenseReclamee)

		// Faux cron : si Premium mais date de fin dépassée → on repasse en gratuit
		if abo == "premium" && dateFin.Valid {
			fin, err := time.Parse("2006-01-02 15:04:05", dateFin.String)
			if err == nil && time.Now().After(fin) {
				bd.Exec("UPDATE utilisateurs SET abonnement = 'gratuit', abonnement_annule = 0, date_fin_abonnement = NULL WHERE id_user = ?", idUser)
				abo = "gratuit"
				dateFin.Valid = false
				annule = 0
			}
		}

		dateFinStr := ""
		if dateFin.Valid {
			dateFinStr = dateFin.String
		}
		json.NewEncoder(w).Encode(map[string]interface{}{
			"abonnement":          abo,
			"date_fin":            dateFinStr,
			"abonnement_annule":   annule,
			"recompense_reclamee": recompenseReclamee,
		})

	

	case "PUT":
		var u User
		json.NewDecoder(r.Body).Decode(&u)

		if u.Abonnement == "annuler" {
			// Annulation : on coupe le renouvellement mais on garde l'accès jusqu'à la date de fin
			bd.Exec("UPDATE utilisateurs SET abonnement_annule = 1 WHERE id_user = ?", u.Id)
		} else {
			// Paiement Premium : on active et on met la date de fin à +1 mois
			finAbo := time.Now().AddDate(0, 1, 0).Format("2006-01-02 15:04:05")
			bd.Exec("UPDATE utilisateurs SET abonnement = 'premium', date_fin_abonnement = ?, abonnement_annule = 0 WHERE id_user = ?", finAbo, u.Id)
		}
		w.WriteHeader(http.StatusOK)
	}
}

// --- ACHAT D'UN OBJET EN VENTE PAR L'ARTISAN (avec paiement + commission) ---
func handleAcheterObjet(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != "POST" {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}

	var d DemandeBox
	json.NewDecoder(r.Body).Decode(&d)

	// On vérifie que l'objet est encore dispo (pas déjà réservé par un autre artisan)
	var idArtisanActuel int
	var idParticulier int
	var prix float64
	bd.QueryRow(`SELECT COALESCE(dd.id_artisan, 0), dd.id_user, COALESCE(a.prix, 0)
		FROM demandes_depot dd
		JOIN annonces a ON dd.id_annonce = a.id_annonce
		WHERE dd.id_demande = ?`, d.Id).Scan(&idArtisanActuel, &idParticulier, &prix)

	if idArtisanActuel != 0 {
		w.WriteHeader(http.StatusConflict)
		json.NewEncoder(w).Encode(map[string]string{"error": "Cet objet a déjà été réservé par un autre artisan."})
		return
	}

	// On génère le code d'ouverture pour l'artisan
	codeArtisan := genererCode(6)

	// On réserve l'objet pour cet artisan
	bd.Exec("UPDATE demandes_depot SET id_artisan = ?, code_artisan = ? WHERE id_demande = ?", d.IdArtisan, codeArtisan, d.Id)

	// On calcule la commission (7%) et la part du particulier (93%)
	commission := prix * 0.07
	partParticulier := prix - commission

	// On crédite le portefeuille du particulier
	bd.Exec("UPDATE utilisateurs SET solde = solde + ? WHERE id_user = ?", partParticulier, idParticulier)
	// On enregistre le mouvement dans le portefeuille du particulier
	bd.Exec("INSERT INTO mouvements_portefeuille (id_user, montant, type, description) VALUES (?, ?, 'vente_objet', 'Vente de votre objet (commission déduite)')", idParticulier, partParticulier)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{
		"message":      "Objet acheté et réservé.",
		"code_artisan": codeArtisan,
	})
}

// --- GESTION DU PORTEFEUILLE (particulier + artisan) ---
func handlePortefeuille(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	switch r.Method {
	case "GET":
		// On renvoie le solde + l'historique des mouvements d'un user
		idUser := r.URL.Query().Get("id_user")

		var solde float64
		bd.QueryRow("SELECT COALESCE(solde,0) FROM utilisateurs WHERE id_user = ?", idUser).Scan(&solde)

		lignes, _ := bd.Query("SELECT id_mouvement, id_user, montant, type, COALESCE(description,''), date_mouvement FROM mouvements_portefeuille WHERE id_user = ? ORDER BY date_mouvement DESC", idUser)
		var mouvements []Mouvement
		for lignes.Next() {
			var m Mouvement
			lignes.Scan(&m.Id, &m.IdUser, &m.Montant, &m.Type, &m.Description, &m.Date)
			mouvements = append(mouvements, m)
		}

		json.NewEncoder(w).Encode(map[string]interface{}{
			"solde":      solde,
			"mouvements": mouvements,
		})

	case "POST":
		// Demande de retrait : on vérifie que le solde est suffisant, puis on débite
		var m Mouvement
		json.NewDecoder(r.Body).Decode(&m)

		var solde float64
		bd.QueryRow("SELECT COALESCE(solde,0) FROM utilisateurs WHERE id_user = ?", m.IdUser).Scan(&solde)

		// On ne peut pas retirer plus que le solde, ni un montant négatif ou nul
		if m.Montant <= 0 || m.Montant > solde {
			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]string{"error": "Montant de retrait invalide ou solde insuffisant."})
			return
		}

		// On débite le solde
		bd.Exec("UPDATE utilisateurs SET solde = solde - ? WHERE id_user = ?", m.Montant, m.IdUser)
		// On enregistre le mouvement (montant négatif car c'est une sortie)
		bd.Exec("INSERT INTO mouvements_portefeuille (id_user, montant, type, description) VALUES (?, ?, 'retrait', 'Retrait vers votre compte bancaire')", m.IdUser, -m.Montant)

		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]string{"message": "Retrait effectué."})
	}
}

// --- CRÉDIT DE L'ARTISAN APRÈS VENTE D'UNE PRESTATION ---
func handleVentePrestation(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != "POST" {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}

	// On reçoit l'id de la prestation vendue et le montant payé
	var data struct {
		IdPrestation int     `json:"id_prestation"`
		Montant      float64 `json:"montant"`
	}
	json.NewDecoder(r.Body).Decode(&data)

	// On trouve l'artisan qui a créé cette prestation
	var idArtisan int
	bd.QueryRow("SELECT id_createur FROM prestations WHERE id_prestation = ?", data.IdPrestation).Scan(&idArtisan)

	// On calcule la part de l'artisan (93%) - la commission (7%) reste à la plateforme
	partArtisan := data.Montant - (data.Montant * 0.07)

	// On crédite le portefeuille de l'artisan + on trace le mouvement
	bd.Exec("UPDATE utilisateurs SET solde = solde + ? WHERE id_user = ?", partArtisan, idArtisan)
	bd.Exec("INSERT INTO mouvements_portefeuille (id_user, montant, type, description) VALUES (?, ?, 'vente_prestation', 'Vente de votre prestation (commission déduite)')", idArtisan, partArtisan)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{"message": "Artisan crédité."})
}

	// --- STATISTIQUES AVANCÉES POUR L'ARTISAN (Premium) ---
func handleStatsArtisan(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	idArtisan := r.URL.Query().Get("id_artisan")

	// 1. Nombre d'objets récupérés par cet artisan
	var nbRecups int
	bd.QueryRow("SELECT COUNT(*) FROM demandes_depot WHERE id_artisan = ?", idArtisan).Scan(&nbRecups)

	// 2. Nombre de prestations vendues par cet artisan
	var nbVentes int
	bd.QueryRow("SELECT COUNT(*) FROM prestations WHERE id_createur = ? AND vendu = 1", idArtisan).Scan(&nbVentes)

	// 3. Chiffre d'affaires de l'artisan (somme de ses mouvements de vente)
	var chiffreAffaires float64
	bd.QueryRow("SELECT COALESCE(SUM(montant),0) FROM mouvements_portefeuille WHERE id_user = ? AND montant > 0", idArtisan).Scan(&chiffreAffaires)


	// 5. Répartition par catégorie (pour le camembert)
	lignesCat, _ := bd.Query("SELECT c.code_ref_cat, COUNT(*) FROM demandes_depot d JOIN objets o ON d.id_objet = o.id_objet JOIN categories c ON o.id_cat = c.id_cat WHERE d.id_artisan = ? GROUP BY c.code_ref_cat", idArtisan)
	var categories []map[string]interface{}
	for lignesCat.Next() {
		var nom string
		var nb int
		lignesCat.Scan(&nom, &nb)
		categories = append(categories, map[string]interface{}{"nom": nom, "nb": nb})
	}

	// 6. Récupérations par mois (pour la courbe)
	lignesMois, _ := bd.Query("SELECT DATE_FORMAT(date_demande, '%Y-%m') AS mois, COUNT(*) FROM demandes_depot WHERE id_artisan = ? GROUP BY mois ORDER BY mois", idArtisan)
	var parMois []map[string]interface{}
	for lignesMois.Next() {
		var mois string
		var nb int
		lignesMois.Scan(&mois, &nb)
		parMois = append(parMois, map[string]interface{}{"mois": mois, "nb": nb})
	}
	json.NewEncoder(w).Encode(map[string]interface{}{
			"nb_recups":        nbRecups,
			"nb_ventes":        nbVentes,
			"chiffre_affaires": chiffreAffaires,
			"categories":       categories,
			"par_mois":         parMois,
		})
}

/// --- RÉCOMPENSE : tous les 100 points = 1 mois de Premium (artisan) ---
func handleRecompense(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != "POST" {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}

	var data struct {
		IdUser int `json:"id_user"`
	}
	json.NewDecoder(r.Body).Decode(&data)

	// On récupère le score et le nb de récompenses déjà prises
	var score, dejaPrises int
	bd.QueryRow("SELECT score_upcycling, COALESCE(recompense_reclamee,0) FROM utilisateurs WHERE id_user = ?", data.IdUser).Scan(&score, &dejaPrises)

	// Une récompense par tranche de 100 points
	meritees := score / 100

	// S'il a déjà tout réclamé → rien à donner
	if dejaPrises >= meritees {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Aucune récompense disponible. Cumulez encore des points !"})
		return
	}

	// On ajoute 1 mois en une seule requête (on part de la date de fin si future, sinon de maintenant)
	bd.Exec(`UPDATE utilisateurs 
		SET abonnement = 'premium', 
		    abonnement_annule = 0,
		    recompense_reclamee = recompense_reclamee + 1,
		    date_fin_abonnement = DATE_ADD(GREATEST(COALESCE(date_fin_abonnement, NOW()), NOW()), INTERVAL 1 MONTH)
		WHERE id_user = ?`, data.IdUser)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{"message": "Félicitations ! 1 mois de Premium ajouté à votre compte."})
}