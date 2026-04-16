<?php
$token = $_GET['token'] ?? '';

if ($token) {
    // On appelle notre API Go via CURL (comme pour l'inscription)
    $ch = curl_init('http://upcycle_api:8080/api/register');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        // Succès : on redirige vers la connexion avec un message
        header('Location: connexion.php?success=verified');
    } else {
        echo "Lien d'activation invalide ou déjà utilisé.";
    }
} else {
    header('Location: index.php');
}
?>