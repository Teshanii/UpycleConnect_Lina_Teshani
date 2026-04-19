package main

// --- UTILISATEURS & SECURITE ---
type User struct {
	Id             int    `json:"id"`
	Nom            string `json:"nom"`
	Pre            string `json:"pre"`
	Mail           string `json:"mail"`
	Mdp            string `json:"mdp"`
	IdRole         int    `json:"id_role"`
	Role           string `json:"role"`
	EstActif       int    `json:"est_actif"`
	EstVerifie     int    `json:"est_verifie"`
	ScoreUpcycling int    `json:"score_upcycling"` // Suivi impact citoyen
	OneSignalId    string `json:"onesignal_id"`    // Pour les notifs push
}

type Role struct {
	Id  int    `json:"id"`
	Lib string `json:"lib"`
}

// --- RÉFÉRENTIEL & INTERNATIONALISATION ---
type Categories struct {
	Id  int    `json:"id"`
	Nom string `json:"nom"` // Code technique (ex: BOIS)
}

type Langue struct {
	Id   int    `json:"id"`
	Code string `json:"code"`
	Nom  string `json:"nom"`
}

// --- MÉTIER : PRESTATIONS & ÉVÉNEMENTS ---
type Prestations struct {
	Id   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
	Desc string  `json:"desc"` // Description du service
}

type Evenements struct {
	Id               int     `json:"id"`
	Titre            string  `json:"titre"`
	Date             string  `json:"date"`
	Prix             float64 `json:"prix"` // Entre 20€ et 100€
	Place            int     `json:"place"`
	IdAnim           int     `json:"id_anim"`
	Anim             string  `json:"anim"`
	StatutValidation int     `json:"statut_validation"` // 0=Attente, 1=Validé
}

// --- LOGISTIQUE : BOX & ANNONCES ---
type Box struct {
	Id          int    `json:"id"`
	Adresse     string `json:"adresse"`
	CapaciteMax int    `json:"capacite_max"`
}

type Annonce struct {
	Id               int     `json:"id"`
	Titre            string  `json:"titre"`
	Description      string  `json:"description"`
	TypeOffre        string  `json:"type_offre"`
	Categorie        string  `json:"categorie"`
	StatutValidation int     `json:"statut_validation"`
	Auteur           string  `json:"auteur"`
	IdUser           int     `json:"id_user"`
	Prix             float64 `json:"prix"`
	StatutAnnonce    string  `json:"statut_annonce"`
	Photo            string  `json:"photo"`
}

// --- COMMUNAUTÉ ---
type ForumMessage struct {
	Id        int    `json:"id"`
	Contenu   string `json:"contenu"`
	Auteur    string `json:"auteur"`
	Date      string `json:"date"` // Pour le suivi chronologique
	EstModere int    `json:"est_modere"`
}

// --- FINANCES (STRIPE) ---
type Transaction struct {
	Id        int     `json:"id"`
	Montant   float64 `json:"montant"`
	RefStripe string  `json:"ref_stripe"`
	Statut    string  `json:"statut"` // succeeded, pending...
	Type      string  `json:"type"`   // "abonnement", "formation", "commission"
	Date      string  `json:"date"`
}

type TypeAbonnement struct {
	Id   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
}

// --- DEMANDES DE BOX ---
type DemandeBox struct {
	Id          int    `json:"id"`
	IdUser      int    `json:"id_user"`
	IdBox       int    `json:"id_box"`
	Description string `json:"description"`
	Statut      string `json:"statut"`
	Code        string `json:"code"`
}

// --- INSCRIPTIONS ---
type Inscription struct {
	Id      int     `json:"id"`
	IdUser  int     `json:"id_user"`
	IdEvent int     `json:"id_event"`
	Titre   string  `json:"titre"`
	Date    string  `json:"date"`
	Prix    float64 `json:"prix"`
}

type ArticleConseil struct {
	Id      int    `json:"id"`
	Titre   string `json:"titre"`
	Contenu string `json:"contenu"`
	Type    string `json:"type"`
	Date    string `json:"date"`
}
