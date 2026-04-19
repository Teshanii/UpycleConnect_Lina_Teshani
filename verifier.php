<?php


$token = $_GET['token'] ?? '';

if ($token) {
    $url = "http://upcycle_api:8080/api/verify/" . $token;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        header('Location: connexion.php?success=verified');
        exit;
    } else {
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h2>Lien d'activation invalide ou expiré</h2>";
        echo "<p>Ce lien ne semble plus être valide. Essayez de vous connecter ou de recréer un compte.</p>";
        echo "<a href='connexion.php' style='color: #2c5f2d;'>Retour à la page de connexion</a>";
        echo "</div>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>