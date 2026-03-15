package main

import (
	"encoding/json"
	"net/http"
)


func handleRoles(w http.ResponseWriter, r *http.Request) {
	if r.Method == "GET" {
		lignes, _ := bd.Query("SELECT id_role, libelle_role FROM roles")
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
    switch r.Method {
    case "GET":
        lignes, _ := bd.Query("SELECT u.id_user, u.nom, u.prenom, u.email, u.est_actif, r.libelle_role, u.id_role FROM utilisateurs u JOIN roles r ON u.id_role = r.id_role")
        var res []User
        for lignes.Next() {
            var u User
            lignes.Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail, &u.EstActif, &u.Role, &u.IdRole)
            res = append(res, u)
        }
        json.NewEncoder(w).Encode(res)

    case "PUT", "DELETE":
        var targetRoleID int
        err := bd.QueryRow("SELECT id_role FROM utilisateurs WHERE id_user = ?", id).Scan(&targetRoleID)
        if err == nil && targetRoleID == 1 {
            w.Header().Set("Content-Type", "application/json")
            w.WriteHeader(http.StatusForbidden)
            json.NewEncoder(w).Encode(map[string]string{"error": "Sécurité : Impossible de modifier ou supprimer un Administrateur."})
            return
        }

        if r.Method == "PUT" {
            var u User
            json.NewDecoder(r.Body).Decode(&u)
            bd.Exec("UPDATE utilisateurs SET nom=?, prenom=?, email=?, id_role=?, est_actif=? WHERE id_user=?", u.Nom, u.Pre, u.Mail, u.IdRole, u.EstActif, id)
            w.WriteHeader(200)
        } else {
            _, err := bd.Exec("DELETE FROM utilisateurs WHERE id_user=?", id)
            if err != nil {
                w.Header().Set("Content-Type", "application/json")
                w.WriteHeader(http.StatusConflict)
                json.NewEncoder(w).Encode(map[string]string{"error": "Erreur SQL : Impossible de supprimer cet utilisateur car il est lié à d'autres données."})
                return
            }
            w.WriteHeader(200)
        }

    case "POST":
        var u User
        json.NewDecoder(r.Body).Decode(&u)
        bd.Exec("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, id_role, est_actif) VALUES (?,?,?,?,?,1)", u.Nom, u.Pre, u.Mail, u.Mdp, u.IdRole)
        w.WriteHeader(201)
    }
}

func handleCategories(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
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
		w.WriteHeader(201)
	case "PUT":
		var c Categories
		json.NewDecoder(r.Body).Decode(&c)
		bd.Exec("UPDATE categories SET code_ref_cat=? WHERE id_cat=?", c.Nom, id)
	case "DELETE":
		bd.Exec("DELETE FROM categories WHERE id_cat=?", id)
	}
}

func handlePrestations(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
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
		w.WriteHeader(201)
	case "PUT":
		var p Prestations
		json.NewDecoder(r.Body).Decode(&p)
		bd.Exec("UPDATE prestations SET nom_prestation=?, prix=?, description=? WHERE id_prestation=?", p.Nom, p.Prix, p.Desc, id)
	case "DELETE":
		bd.Exec("DELETE FROM prestations WHERE id_prestation=?", id)
	}
}


func handleEvenements(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	switch r.Method {
	case "GET":
		query := "SELECT e.id_event, e.titre, e.date_debut, e.prix_actuel, e.places_max, e.statut_validation, u.nom, e.id_animateur FROM evenements e JOIN utilisateurs u ON e.id_animateur = u.id_user"
		lignes, _ := bd.Query(query)
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
	case "DELETE":
		bd.Exec("DELETE FROM evenements WHERE id_event=?", id)
	}
}

func handleValidation(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	bd.Exec("UPDATE evenements SET statut_validation = 1 WHERE id_event = ?", id)
	w.WriteHeader(200)
}


func handleAnnonces(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	switch r.Method {
	case "GET":
		lignes, _ := bd.Query("SELECT a.id_annonce, a.titre, a.statut_validation, u.nom FROM annonces a JOIN utilisateurs u ON a.id_user_auteur = u.id_user")
		var res []Annonce
		for lignes.Next() {
			var a Annonce
			lignes.Scan(&a.Id, &a.Titre, &a.StatutValidation, &a.Auteur)
			res = append(res, a)
		}
		json.NewEncoder(w).Encode(res)
	case "PUT":
		bd.Exec("UPDATE annonces SET statut_validation = 1 WHERE id_annonce = ?", id)
		w.WriteHeader(200)
	case "DELETE":
		bd.Exec("DELETE FROM annonces WHERE id_annonce = ?", id)
	}
}


func handleBox(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
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
		w.WriteHeader(201)
	case "DELETE":
		bd.Exec("DELETE FROM box WHERE id_box = ?", id)
	}
}


func handleTransactions(w http.ResponseWriter, r *http.Request) {
	if r.Method == "GET" {
		lignes, _ := bd.Query("SELECT id_transac, montant, type_transac, date_transac FROM transactions")
		var res []Transaction
		for lignes.Next() {
			var t Transaction
			lignes.Scan(&t.Id, &t.Montant, &t.Type, &t.Date)
			res = append(res, t)
		}
		json.NewEncoder(w).Encode(res)
	}
}

func handleAbonnements(w http.ResponseWriter, r *http.Request) {
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
		w.WriteHeader(200)
	case "DELETE":
		bd.Exec("DELETE FROM message_forums WHERE id_message = ?", id)
	}
}


func handleLangues(w http.ResponseWriter, r *http.Request) {
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
		w.WriteHeader(201)
	}
}