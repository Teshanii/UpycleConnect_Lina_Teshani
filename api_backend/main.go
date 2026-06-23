package main

import (
	"fmt"
	"net/http"
)

func main() {
	initDB()
	mux := http.NewServeMux()

	mux.HandleFunc("/api/users", handleUsers)
	mux.HandleFunc("/api/users/{id}", handleUsers)
	mux.HandleFunc("/api/profil/{id}", handleProfil) 

	mux.HandleFunc("/api/roles", handleRoles)

	mux.HandleFunc("/api/categories", handleCategories)
	mux.HandleFunc("/api/categories/{id}", handleCategories)

	mux.HandleFunc("/api/prestations", handlePrestations)
	mux.HandleFunc("/api/prestations/{id}", handlePrestations)

	mux.HandleFunc("/api/evenements", handleEvenements)
	mux.HandleFunc("/api/evenements/{id}", handleEvenements)

	mux.HandleFunc("/api/box", handleBox)
	mux.HandleFunc("/api/box/{id}", handleBox)
	mux.HandleFunc("/api/demandes_box", handleDemandesBox)
	mux.HandleFunc("/api/demandes_box/{id}", handleDemandesBox)
	mux.HandleFunc("/api/catalogue-artisan", handleCatalogueArtisan)
	mux.HandleFunc("/api/recuperation", handleRecuperation)
	mux.HandleFunc("/api/confirmer-recup", handleConfirmerRecup)
	mux.HandleFunc("/api/acheter-objet", handleAcheterObjet)

	mux.HandleFunc("/api/casiers", handleCasiers)
	mux.HandleFunc("/api/casiers/{id}", handleCasiers)

	mux.HandleFunc("/api/projets", handleProjets)
	mux.HandleFunc("/api/projets/{id}", handleProjets)

	mux.HandleFunc("/api/etapes", handleEtapes)
	mux.HandleFunc("/api/etapes/{id}", handleEtapes)

	mux.HandleFunc("/api/conseils", handleConseils)
	mux.HandleFunc("/api/conseils/{id}", handleConseils)

	mux.HandleFunc("/api/messages", handleMessages)
	mux.HandleFunc("/api/messages/{id}", handleMessages)

	mux.HandleFunc("/api/annonces", handleAnnonces)
	mux.HandleFunc("PUT /api/annonces/valider/{id}", handleAnnonces)
	mux.HandleFunc("/api/annonces/{id}", handleAnnonces)

	mux.HandleFunc("/api/inscriptions", handleInscriptions)
	mux.HandleFunc("/api/inscriptions/{id}", handleInscriptions)
	mux.HandleFunc("/api/inscrits-evenement/{id}", handleInscritsEvenement)

	mux.HandleFunc("/api/transactions", handleTransactions)
	mux.HandleFunc("/api/abonnements", handleAbonnements)
	mux.HandleFunc("/api/transactions/{id}", handleTransactions)
	mux.HandleFunc("/api/abonnement", handleAbonnement)

	mux.HandleFunc("/api/stats-artisan", handleStatsArtisan)

	mux.HandleFunc("/api/recompense", handleRecompense)

	mux.HandleFunc("/api/portefeuille", handlePortefeuille)
	mux.HandleFunc("/api/vente-prestation", handleVentePrestation)

	mux.HandleFunc("/api/langues", handleLangues)
	mux.HandleFunc("/api/traductions", handleTraductions)
	mux.HandleFunc("/api/langues/{id}", handleLangues)

	mux.HandleFunc("PUT /api/evenements/valider/{id}", handleValidation)

	mux.HandleFunc("POST /api/register", handleRegister)
	mux.HandleFunc("POST /api/login", handleLogin)



	mux.HandleFunc("PUT /api/verify/{token}", handleVerify)

	fmt.Println("Serveur pret sur http://localhost:8080")

	http.ListenAndServe(":8080", middlewareCORS(mux))
}

func middlewareCORS(h http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		if r.Method == "OPTIONS" {
			return
		}
		h.ServeHTTP(w, r)
	})
}
