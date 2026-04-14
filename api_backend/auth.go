package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"regexp"

	"crypto/rand" // Pour générer un token aléatoire
    "fmt"
    "net/smtp"   // Pour l'envoi de mail

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

	// 1. Vérifications basiques
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
		json.NewEncoder(w).Encode(map[string]string{"error": "Erreur serveur lors du hachage."})
		return
	}

	// On génère 16 octets aléatoires et on les transforme en texte (hexadécimal)
	b := make([]byte, 16)
	rand.Read(b)
	token := fmt.Sprintf("%x", b)

	// 4. Insertion en base de données
	// On ajoute 'token_verification' et on met 'est_verifie' à 0 par défaut
	_, err = bd.Exec(`INSERT INTO utilisateurs 
        (nom, prenom, email, mot_de_passe, id_role, est_actif, token_verification, est_verifie) 
        VALUES (?,?,?,?,?,1,?,0)`,
		u.Nom, u.Pre, u.Mail, string(hash), u.IdRole, token)

	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Erreur lors de la création du compte."})
		return
	}

	// Le mot-clé 'go' permet d'envoyer le mail sans faire attendre l'utilisateur
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

	// 1. Récupérer l'utilisateur (On ajoute est_verifie à la sélection)
	var u User
	var hashMdp string
	var estVerifie int // Variable locale pour le test

	// On cherche l'utilisateur par mail uniquement pour le moment
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

	// 2. Comparer le mot de passe fourni avec le Hash en base
	err = bcrypt.CompareHashAndPassword([]byte(hashMdp), []byte(req.Mdp))
	if err != nil {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]string{"error": "Email ou mot de passe incorrect."})
		return
	}

	// Si le mot de passe est bon, on regarde si le compte est activé
	if estVerifie == 0 {
		w.WriteHeader(http.StatusForbidden) // 403 Forbidden
		json.NewEncoder(w).Encode(map[string]string{"error": "Votre compte n'est pas encore activé. Vérifiez vos emails."})
		return
	}

	u.Mdp = "" // Sécurité : on vide le mdp avant de renvoyer les infos
	json.NewEncoder(w).Encode(u)
}

func envoyerMailVerification(destinataire string, token string) {
    // Configuration de ton serveur de test (ex: Gmail ou Mailtrap pour tes tests locaux)
    from := "ton.email@gmail.com"
    password := "ton_mot_de_passe_application" // À générer dans ton compte Google

    smtpHost := "smtp.gmail.com"
    smtpPort := "587"

    // Corps du mail
    sujet := "Subject: UpcycleConnect - Activez votre compte\r\n"
    corps := "\r\nBienvenue sur UpcycleConnect !\r\n\r\n" +
             "Veuillez cliquer sur le lien ci-dessous pour activer votre compte :\r\n" +
             "http://localhost/UPCYCLECONNECT_LINA_TESHANI/verifier.php?token=" + token

    message := []byte(sujet + corps)

    // Authentification et envoi
    auth := smtp.PlainAuth("", from, password, smtpHost)
    err := smtp.SendMail(smtpHost+":"+smtpPort, auth, from, []string{destinataire}, message)
    
    if err != nil {
        fmt.Println("Erreur envoi mail:", err)
    }
}