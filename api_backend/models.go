package main

type User struct {
	Id      int    `json:"id"`
	Nom     string `json:"nom"`
	Pre     string `json:"pre"`
	Mail    string `json:"mail"`
	Mdp     string `json:"mdp"`
	IdRole  int    `json:"id_role"`
	Role    string `json:"role"`
	EstActif int   `json:"est_actif"` 
}

type Role struct {
	Id  int    `json:"id"`
	Lib string `json:"lib"`
}

type Categories struct {
	Id  int    `json:"id"`
	Nom string `json:"nom"`
}

type Prestations struct {
	Id    int     `json:"id"`
	Nom   string  `json:"nom"`
	Prix  float64 `json:"prix"`
	Desc  string  `json:"desc"`
}

type Evenements struct {
	Id               int     `json:"id"`
	Titre            string  `json:"titre"`
	Date             string  `json:"date"`
	Prix             float64 `json:"prix"`
	Place            int     `json:"place"`
	IdAnim           int     `json:"id_anim"`
	Anim             string  `json:"anim"`
	StatutValidation int     `json:"statut_validation"` 
}


type Langue struct {
	Id   int    `json:"id"`
	Code string `json:"code"`
	Nom  string `json:"nom"`
}


type Box struct {
	Id          int    `json:"id"`
	Adresse     string `json:"adresse"`
	CapaciteMax int    `json:"capacite_max"`
}

type Annonce struct {
	Id               int    `json:"id"`
	Titre            string `json:"titre"`
	StatutValidation int    `json:"statut_validation"`
	Auteur           string `json:"auteur"`
}

type ForumMessage struct {
	Id        int    `json:"id"`
	Contenu   string `json:"contenu"`
	Auteur    string `json:"auteur"`
	EstModere int    `json:"est_modere"`
}

type Transaction struct {
	Id      int     `json:"id"`
	Montant float64 `json:"montant"`
	Type    string  `json:"type"`
	Date    string  `json:"date"`
	Libelle string  `json:"libelle"`
}

type TypeAbonnement struct {
	Id   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
}