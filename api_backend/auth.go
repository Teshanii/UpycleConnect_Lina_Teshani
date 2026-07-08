package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"regexp"

	"golang.org/x/crypto/bcrypt"
)

type LoginRequest struct {
	Email string `json:"email"`
	Mdp   string `json:"mdp"`
}


func isEmailValid(e string) bool {
	emailRegex := regexp.MustCompile(`^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,4}$`)
	return emailRegex.MatchString(e)
}

func handleRegister(w http.ResponseWriter, r *http.Request) {
	if r.Method != "POST" {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var u User
	json.NewDecoder(r.Body).Decode(&u)

	w.Header().Set("Content-Type", "application/json")

	
	if u.Nom == "" || u.Pre == "" || u.Mail == "" || u.Mdp == "" || u.IdRole == 0 {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Tous les champs sont obligatoires."})
		return
	}

	if !isEmailValid(u.Mail) {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Format d'email invalide."})
		return
	}

	
	var existe int
	err := bd.QueryRow("SELECT COUNT(*) FROM utilisateurs WHERE email = ?", u.Mail).Scan(&existe)
	if err != nil || existe > 0 {
		w.WriteHeader(http.StatusConflict)
		json.NewEncoder(w).Encode(map[string]string{"error": "Cet email est déjà utilisé."})
		return
	}

	
	hash, err := bcrypt.GenerateFromPassword([]byte(u.Mdp), bcrypt.DefaultCost)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Erreur serveur lors du hachage."})
		return
	}

	
	_, err = bd.Exec(`INSERT INTO utilisateurs 
		(nom, prenom, email, mot_de_passe, id_role, est_actif, est_verifie) 
		VALUES (?,?,?,?,?,1,0)`,
		u.Nom, u.Pre, u.Mail, string(hash), u.IdRole)

	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Erreur lors de la création du compte."})
		return
	}

	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Inscription réussie.",
	})
}

func handleLogin(w http.ResponseWriter, r *http.Request) {
	if r.Method != "POST" {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var req LoginRequest
	json.NewDecoder(r.Body).Decode(&req)

	w.Header().Set("Content-Type", "application/json")

	var u User
	var hashMdp string
	var estVerifie int

	err := bd.QueryRow(`
		SELECT u.id_user, u.nom, u.prenom, u.email, u.mot_de_passe, u.id_role, r.libelle_role, u.est_verifie 
		FROM utilisateurs u 
		JOIN roles r ON u.id_role = r.id_role 
		WHERE u.email = ? AND u.est_actif = 1`, req.Email).
		Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail, &hashMdp, &u.IdRole, &u.Role, &estVerifie)

	if err != nil {
		if err == sql.ErrNoRows {
			w.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(w).Encode(map[string]string{"error": "Identifiants incorrects ou compte banni."})
			return
		}
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	
	err = bcrypt.CompareHashAndPassword([]byte(hashMdp), []byte(req.Mdp))
	if err != nil {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]string{"error": "Email ou mot de passe incorrect."})
		return
	}

	
	if estVerifie == 0 {
		w.WriteHeader(http.StatusForbidden)
		json.NewEncoder(w).Encode(map[string]string{"error": "Votre compte n'est pas encore activé. Vérifiez vos emails."})
		return
	}

	u.Mdp = ""
	json.NewEncoder(w).Encode(u)
}