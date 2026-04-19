package main

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"golang.org/x/crypto/bcrypt"
)


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


func handleUsers(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")

	switch r.Method {
	case "GET":
		
		lignes, err := bd.Query(`
			SELECT u.id_user, u.nom, u.prenom, u.email, u.est_actif, r.libelle_role, u.id_role, u.score_upcycling, u.onesignal_player_id, u.est_verifie 
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
			lignes.Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail, &u.EstActif, &u.Role, &u.IdRole, &u.ScoreUpcycling, &u.OneSignalId, &u.EstVerifie)
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
			// On met à jour tous les champs y compris le statut de vérification mail
			bd.Exec(`UPDATE utilisateurs SET nom=?, prenom=?, email=?, id_role=?, est_actif=?, score_upcycling=?, onesignal_player_id=?, est_verifie=? WHERE id_user=?`,
				u.Nom, u.Pre, u.Mail, u.IdRole, u.EstActif, u.ScoreUpcycling, u.OneSignalId, u.EstVerifie, id)
			w.WriteHeader(http.StatusOK)
		} else {
			_, err := bd.Exec("DELETE FROM utilisateurs WHERE id_user=?", id)
			if err != nil {
				// Si la suppression échoue c'est probablement à cause de clés étrangères (annonces, events...)
				w.WriteHeader(http.StatusConflict)
				json.NewEncoder(w).Encode(map[string]string{"error": "L'utilisateur est lié à des annonces ou événements."})
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


func handlePrestations(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")
	switch r.Method {
	case "GET":
		lignes, _ := bd.Query("SELECT id_prestation, nom_prestation, prix, description FROM prestations")
		var res []Prestations
		for lignes.Next() {
			var p Prestations
			lignes.Scan(&p.Id, &p.Nom, &p.Prix, &p.Desc)
			res = append(res, p)
		}
		json.NewEncoder(w).Encode(res)
	case "POST":
		var p Prestations
		json.NewDecoder(r.Body).Decode(&p)
		bd.Exec("INSERT INTO prestations (nom_prestation, prix, description) VALUES (?,?,?)", p.Nom, p.Prix, p.Desc)
		w.WriteHeader(http.StatusCreated)
	case "PUT":
		var p Prestations
		json.NewDecoder(r.Body).Decode(&p)
		bd.Exec("UPDATE prestations SET nom_prestation=?, prix=?, description=? WHERE id_prestation=?", p.Nom, p.Prix, p.Desc, id)
		w.WriteHeader(http.StatusOK)
	case "DELETE":
		bd.Exec("DELETE FROM prestations WHERE id_prestation=?", id)
		w.WriteHeader(http.StatusOK)
	}
}


func handleEvenements(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")
	switch r.Method {
	case "GET":
		lignes, _ := bd.Query("SELECT e.id_event, e.titre, e.date_debut, e.prix_actuel, e.places_max, e.statut_validation, u.nom, e.id_animateur FROM evenements e JOIN utilisateurs u ON e.id_animateur = u.id_user")
		var res []Evenements
		for lignes.Next() {
			var e Evenements
			lignes.Scan(&e.Id, &e.Titre, &e.Date, &e.Prix, &e.Place, &e.StatutValidation, &e.Anim, &e.IdAnim)
			res = append(res, e)
		}
		json.NewEncoder(w).Encode(res)
	case "PUT":
		var e Evenements
		json.NewDecoder(r.Body).Decode(&e)
		bd.Exec("UPDATE evenements SET titre=?, date_debut=?, prix_actuel=?, places_max=?, id_animateur=? WHERE id_event=?", e.Titre, e.Date, e.Prix, e.Place, e.IdAnim, id)
		w.WriteHeader(http.StatusOK)
	case "DELETE":
		bd.Exec("DELETE FROM evenements WHERE id_event=?", id)
		w.WriteHeader(http.StatusOK)
	}
}

func handleValidation(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	bd.Exec("UPDATE evenements SET statut_validation = 1 WHERE id_event = ?", id)
	w.WriteHeader(http.StatusOK)
}


func handleAnnonces(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	w.Header().Set("Content-Type", "application/json")
	switch r.Method {
	case "GET":
		idUser := r.URL.Query().Get("id_user")
		var lignes *sql.Rows
		if idUser != "" {
			// Si on passe un id_user dans l'URL, on filtre les annonces de cet utilisateur
			// On récupère aussi la photo pour l'afficher dans mes_annonces.php
			lignes, _ = bd.Query("SELECT id_annonce, titre, description, categorie, type_annonce, prix, statut_validation, statut_annonce, photo FROM annonces WHERE id_user_auteur = ?", idUser)
			var res []Annonce
			for lignes.Next() {
				var a Annonce
				lignes.Scan(&a.Id, &a.Titre, &a.Description, &a.Categorie, &a.TypeOffre, &a.Prix, &a.StatutValidation, &a.StatutAnnonce, &a.Photo)
				res = append(res, a)
			}
			json.NewEncoder(w).Encode(res)
		} else {
			// Sans id_user, l'admin voit toutes les annonces avec le nom de l'auteur
			lignes, _ = bd.Query("SELECT a.id_annonce, a.titre, a.statut_validation, u.nom FROM annonces a JOIN utilisateurs u ON a.id_user_auteur = u.id_user")
			var res []Annonce
			for lignes.Next() {
				var a Annonce
				lignes.Scan(&a.Id, &a.Titre, &a.StatutValidation, &a.Auteur)
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
			// C'est l'admin qui valide l'annonce
			bd.Exec("UPDATE annonces SET statut_validation = 1 WHERE id_annonce = ?", id)
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
		bd.Exec("DELETE FROM box WHERE id_box = ?", id)
		w.WriteHeader(http.StatusOK)
	}
}


func handleTransactions(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method == "GET" {
		lignes, _ := bd.Query("SELECT id_transac, montant, reference_stripe, statut_paiement, date_transac FROM transactions")
		var res []Transaction
		for lignes.Next() {
			var t Transaction
			lignes.Scan(&t.Id, &t.Montant, &t.RefStripe, &t.Statut, &t.Date)
			res = append(res, t)
		}
		json.NewEncoder(w).Encode(res)
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
		lignes, _ := bd.Query("SELECT m.id_message, m.contenu, u.nom, m.est_modere FROM message_forums m JOIN utilisateurs u ON m.id_user_auteur = u.id_user")
		var res []ForumMessage
		for lignes.Next() {
			var m ForumMessage
			lignes.Scan(&m.Id, &m.Contenu, &m.Auteur, &m.EstModere)
			res = append(res, m)
		}
		json.NewEncoder(w).Encode(res)
	case "PUT":
		bd.Exec("UPDATE message_forums SET est_modere = 1 WHERE id_message = ?", id)
		w.WriteHeader(http.StatusOK)
	case "DELETE":
		bd.Exec("DELETE FROM message_forums WHERE id_message = ?", id)
		w.WriteHeader(http.StatusOK)
	}
}


func handleLangues(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method == "GET" {
		lignes, _ := bd.Query("SELECT id_langue, code_iso, nom_langue FROM langues")
		var res []Langue
		for lignes.Next() {
			var l Langue
			lignes.Scan(&l.Id, &l.Code, &l.Nom)
			res = append(res, l)
		}
		json.NewEncoder(w).Encode(res)
	} else if r.Method == "POST" {
		var l Langue
		json.NewDecoder(r.Body).Decode(&l)
		bd.Exec("INSERT INTO langues (code_iso, nom_langue) VALUES (?, ?)", l.Code, l.Nom)
		w.WriteHeader(http.StatusCreated)
	}
}


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
	w.Header().Set("Content-Type", "application/json")
	switch r.Method {
	case "GET":
		lignes, _ := bd.Query("SELECT id_demande, id_user, description, statut FROM demandes_box")
		var res []DemandeBox
		for lignes.Next() {
			var d DemandeBox
			lignes.Scan(&d.Id, &d.IdUser, &d.Description, &d.Statut)
			res = append(res, d)
		}
		json.NewEncoder(w).Encode(res)
	case "POST":
		var d DemandeBox
		json.NewDecoder(r.Body).Decode(&d)
		bd.Exec("INSERT INTO demandes_box (id_user, description) VALUES (?,?)", d.IdUser, d.Description)
		w.WriteHeader(http.StatusCreated)
	}
}


// Le particulier peut s'inscrire à un événement et voir ses inscriptions
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
		bd.Exec("INSERT INTO inscriptions (id_user, id_event) VALUES (?,?)", i.IdUser, i.IdEvent)
		w.WriteHeader(http.StatusCreated)
	}
}


func handleConseils(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	lignes, _ := bd.Query("SELECT id_article, titre, contenu, type, date_creation FROM article_conseil")
	var res []ArticleConseil
	for lignes.Next() {
		var a ArticleConseil
		lignes.Scan(&a.Id, &a.Titre, &a.Contenu, &a.Type, &a.Date)
		res = append(res, a)
	}
	json.NewEncoder(w).Encode(res)
}
