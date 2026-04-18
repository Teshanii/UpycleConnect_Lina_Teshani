<?php
/**
 * verifier.php 
 * Ce script est appelé lorsque l'utilisateur clique sur le lien reçu par mail.
 * Il transmet le token à l'API Go pour activer le compte.
 */

$token = $_GET['token'] ?? '';

if ($token) {
    // 1. Définition de l'URL de l'API Go (on utilise le nom du service Docker "upcycle_api")
    // On vise la route spécifique de vérification définie dans handlers.go
    $url = "http://upcycle_api:8080/api/verify/" . $token;

    // 2. Initialisation de CURL pour effectuer l'appel interne entre les conteneurs
    $ch = curl_init($url);

    // 3. Configuration de la requête
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // TRÈS IMPORTANT : La route handleVerify est enregistrée en PUT dans main.go
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
    
    // Optionnel : on précise qu'on attend du JSON en retour
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // 4. Exécution de l'appel vers le backend
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. Gestion de la réponse selon le code HTTP renvoyé par Go
    if ($httpCode === 200) {
        // Succès : L'API a trouvé le token et passé 'est_verifie' à 1
        // On redirige vers la connexion avec le message de succès
        header('Location: connexion.php?success=verified');
        exit;
    } else {
        // Erreur : Le token n'existe pas en base ou a déjà été validé
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h2>Lien d'activation invalide ou expiré</h2>";
        echo "<p>Ce lien ne semble plus être valide. Essayez de vous connecter ou de recréer un compte.</p>";
        echo "<a href='connexion.php' style='color: #2c5f2d;'>Retour à la page de connexion</a>";
        echo "</div>";
    }
} else {
    // Si aucun token n'est présent dans l'URL, on redirige vers l'accueil
    header('Location: index.php');
    exit;
}
?>