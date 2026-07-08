package main


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
	ScoreUpcycling int    `json:"score_upcycling"`
	OneSignalId    string `json:"onesignal_player_id"`
	Abonnement     string `json:"abonnement"`
	DateFinAbo       string `json:"date_fin_abonnement"`
	AbonnementAnnule int    `json:"abonnement_annule"`
	Solde            float64 `json:"solde"`
	Present          int     `json:"present"`
}

type Role struct {
	Id  int    `json:"id"`
	Lib string `json:"lib"`
}


type Categories struct {
	Id  int    `json:"id"`
	Nom string `json:"nom"` 
}

type Langue struct {
	Id   int    `json:"id"`
	Code string `json:"code"`
	Nom  string `json:"nom"`
}

type Prestations struct {
    Id               int     `json:"id"`
    Nom              string  `json:"nom"`
    Prix             float64 `json:"prix"`
    Desc             string  `json:"desc"`
    Photo            string  `json:"photo"`
    IdCreateur       int     `json:"id_createur"`
    Createur         string  `json:"createur"`
    StatutValidation int     `json:"statut_validation"`
    MotifRefus       string  `json:"motif_refus"`
	Vendu            int     `json:"vendu"`
}


type Evenements struct {
	Id               int     `json:"id"`
	Titre            string  `json:"titre"`
	Type             string  `json:"type"`
	Lieu             string  `json:"lieu"`
	Description      string  `json:"description"`
	Date             string  `json:"date"`
	DateFin          string  `json:"date_fin"`
	Prix             float64 `json:"prix"`
	Place            int     `json:"place"`
	IdAnim           int     `json:"id_anim"`
	Anim             string  `json:"anim"`
	StatutValidation int     `json:"statut_validation"`
	NbInscrits       int     `json:"nb_inscrits"`
	MotifRefus       string  `json:"motif_refus"`
}


type Box struct {
	Id            int    `json:"id"`
	Adresse       string `json:"adresse"`
	Ville         string `json:"ville"`
	CapaciteMax   int    `json:"capacite_max"`
	CasiersLibres int    `json:"casiers_libres"`
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
	MotifRefus       string  `json:"motif_refus"`
	Photo            string  `json:"photo"`
}


type ForumMessage struct {
    Id              int    `json:"id"`
    Contenu         string `json:"contenu"`
    Auteur          string `json:"auteur"`
    IdUser          int    `json:"id_user"`
    IdMessageParent int    `json:"id_message_parent"`
    Date            string `json:"date"`
    EstModere       int    `json:"est_modere"`
    Categorie       string `json:"categorie"`
    Epingle         int    `json:"epingle"`
    Titre           string `json:"titre"`
}

type Notif struct {
	Id      int    `json:"id"`
	Titre   string `json:"titre"`
	Message string `json:"message"`
	Date    string `json:"date"`
	EstLue  int    `json:"est_lue"`
}

type Transaction struct {
	Id        int     `json:"id"`
	IdUser    int     `json:"id_user"`
	Montant   float64 `json:"montant"`
	RefStripe string  `json:"ref_stripe"`
	Statut    string  `json:"statut"`
	Type      string  `json:"type"`
	Commission float64 `json:"commission"`
	Date       string  `json:"date"`
}

type Mouvement struct {
	Id          int     `json:"id"`
	IdUser      int     `json:"id_user"`
	Montant     float64 `json:"montant"`
	Type        string  `json:"type"`
	Description string  `json:"description"`
	Date        string  `json:"date"`
}

type TypeAbonnement struct {
	Id   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
}

type DemandeBox struct {
    Id            int    `json:"id"`
    IdUser        int    `json:"id_user"`
    IdObjet       int    `json:"id_objet"`
    IdBox         int    `json:"id_box"`
    IdCasier      int    `json:"id_casier"`
    IdArtisan     int    `json:"id_artisan"`
    IdAnnonce     int    `json:"id_annonce"`
    Description   string `json:"description"`
    Statut        string `json:"statut"`
    CodeOuverture string `json:"code_ouverture"`
    CodeBarre     string `json:"code_barre_scan"`
    CodeArtisan   string `json:"code_artisan"`
    MotifRefus    string `json:"motif_refus"`
    Date          string `json:"date"`
    NomUser       string `json:"nom_user"`
    AdresseBox    string `json:"adresse_box"`
    NumeroCasier  string `json:"numero_casier"`
    TitreAnnonce  string `json:"titre_annonce"`
}

type ObjetCatalogue struct {
    IdDemande      int     `json:"id_demande"`
    Titre          string  `json:"titre"`
    Description    string  `json:"description"`
    Categorie      string  `json:"categorie"`
    TypeOffre      string  `json:"type_offre"`
    Prix           float64 `json:"prix"`
    Photo          string  `json:"photo"`
    NomParticulier string  `json:"nom_particulier"`
    AdresseBox     string  `json:"adresse_box"`
    NumeroCasier   string  `json:"numero_casier"`
	Ville 		   string  `json:"ville"`
}


type Casier struct {
    Id     int    `json:"id"`
    Numero string `json:"numero"`
    Statut string `json:"statut"`
    IdBox  int    `json:"id_box"`
}


type Inscription struct {
	Id      int     `json:"id"`
	IdUser  int     `json:"id_user"`
	IdEvent int     `json:"id_event"`
	Titre   string  `json:"titre"`
	Date    string  `json:"date"`
	Prix    float64 `json:"prix"`
}

type ArticleConseil struct {
	Id       int    `json:"id"`
	Titre    string `json:"titre"`
	Contenu  string `json:"contenu"`
	Type     string `json:"type"`
	Date     string `json:"date"`
	Auteur   string `json:"auteur"`
	IdAuteur int    `json:"id_auteur"`
	Statut   int    `json:"statut"`
}

type Traduction struct {
    Id       int    `json:"id"`
    Cle      string `json:"cle"`
    IdLangue int    `json:"id_langue"`
    Texte    string `json:"texte"`
}

type Projet struct {
	Id                  int    `json:"id"`
	Titre               string `json:"titre"`
	Description         string `json:"description"`
	Adresse             string `json:"adresse"`
	Ville               string `json:"ville"`
	Statut              string `json:"statut"`
	PhotoCouverture     string `json:"photo_couverture"`
	OuvertParticipation int    `json:"ouvert_participation"`
	EstSponsorise       int    `json:"est_sponsorise"`
	IdCreateur          int    `json:"id_createur"`
	Createur            string `json:"createur"`
	DateDebut 			string `json:"date_debut"`
	DateFin   			string `json:"date_fin"`
}

type Etape struct {
	Id          int    `json:"id"`
	Titre       string `json:"titre"`
	Description string `json:"description"`
	Image       string `json:"image"`
	Ordre       int    `json:"ordre"`
	IdProjet    int    `json:"id_projet"`
}

type Participant struct {
	Id       int    `json:"id"`
	IdProjet int    `json:"id_projet"`
	IdUser   int    `json:"id_user"`
	Tache    string `json:"tache"`
	Statut   string `json:"statut"`
	NomUser  string `json:"nom_user"` 
}