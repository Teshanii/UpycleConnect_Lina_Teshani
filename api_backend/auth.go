package main

import (
	"encoding/json"
	"net/http"
	"regexp"

	"crypto/rand" 
	"fmt"
	"net/smtp"   

	"golang.org/x/crypto/bcrypt"
)

type LoginRequest struct {
	Email string `json:"email"`
	Mdp   string `json:"mdp"`
}

// Fonction pour vérifier le format de l'email avec regex
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

	// 1. Vérifications de base
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

	// 2. Vérifier si l'email existe déjà
	var existe int
	err := bd.QueryRow("SELECT COUNT(*) FROM utilisateurs WHERE email = ?", u.Mail).Scan(&existe)
	if err != nil || existe > 0 {
		w.WriteHeader(http.StatusConflict)
		json.NewEncoder(w).Encode(map[string]string{"error": "Cet email est déjà utilisé."})
		return
	}

	// 3. Hacher le mot de passe avec bcrypt
	hash, err := bcrypt.GenerateFromPassword([]byte(u.Mdp), bcrypt.DefaultCost)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	// Génération d'un token cryptographique hexadécimal unique
	b := make([]byte, 16)
	rand.Read(b)
	token := fmt.Sprintf("%x", b)

	// 4. Insertion avec 'token_verification' et 'est_verifie' à 0 (compte inactif)
	_, err = bd.Exec(`INSERT INTO utilisateurs 
        (nom, prenom, email, mot_de_passe, id_role, est_actif, token_verification, est_verifie) 
        VALUES (?,?,?,?,?,1,?,0)`,
		u.Nom, u.Pre, u.Mail, string(hash), u.IdRole, token)

	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Erreur lors de la création."})
		return
	}

	// Envoi du mail en arrière-plan (Goroutine) pour ne pas bloquer l'inscription
	go envoyerMailVerification(u.Mail, token)

	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Inscription réussie ! Veuillez consulter vos emails pour activer votre compte.",
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

	// On récupère est_verifie pour bloquer la connexion si le mail n'est pas validé
	err := bd.QueryRow(`
		SELECT u.id_user, u.nom, u.prenom, u.email, u.mot_de_passe, u.id_role, r.libelle_role, u.est_verifie 
		FROM utilisateurs u 
		JOIN roles r ON u.id_role = r.id_role 
		WHERE u.email = ? AND u.est_actif = 1`, req.Email).
		Scan(&u.Id, &u.Nom, &u.Pre, &u.Mail, &hashMdp, &u.IdRole, &u.Role, &estVerifie)

	if err != nil {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]string{"error": "Identifiants incorrects ou compte banni."})
		return
	}

	// Vérification du mot de passe haché
	err = bcrypt.CompareHashAndPassword([]byte(hashMdp), []byte(req.Mdp))
	if err != nil {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]string{"error": "Email ou mot de passe incorrect."})
		return
	}

	// BLOCAGE : Si le mail n'est pas vérifié, on renvoie une erreur 403
	if estVerifie == 0 {
		w.WriteHeader(http.StatusForbidden) 
		json.NewEncoder(w).Encode(map[string]string{"error": "Votre compte n'est pas encore activé. Vérifiez vos emails."})
		return
	}

	u.Mdp = "" 
	json.NewEncoder(w).Encode(u)
}

func envoyerMailVerification(destinataire string, token string) {
    // PARAMÈTRES RÉELS GMAIL
    from := "teshanifernandotf@gmail.com" 
    // Utilise un "Mot de passe d'application" de 16 caractères généré dans Google
    password := "zubw icfo hfuh lqbu" 

    smtpHost := "smtp.gmail.com"
    smtpPort := "587"

    // Lien cliquable pointant vers verifier.php
    // Note : Remplace 'localhost' par l'IP de ton serveur si tu déploies en ligne.
    lien := "http://127.0.0.1/verifier.php?token=" + token

    sujet := "Subject: UpcycleConnect - Activez votre compte\r\n"
    corps := "\r\nBonjour,\r\n\r\nBienvenue sur UpcycleConnect ! Pour valider votre inscription, cliquez sur le lien ci-dessous :\r\n\r\n" + lien + "\r\n\r\nMerci !"

    message := []byte(sujet + corps)
    auth := smtp.PlainAuth("", from, password, smtpHost)
    
    // Expédition réelle via SMTP
    err := smtp.SendMail(smtpHost+":"+smtpPort, auth, from, []string{destinataire}, message)
    
    if err != nil {
        fmt.Println("Erreur critique envoi mail :", err)
    }
}