<?php
session_start();

// 1. Validation du Captcha (Côté PHP)
if (strtolower(trim($_POST['captcha_reponse'])) !== $_SESSION['captcha_reponse']) {
    header('Location: inscription.php?error=Captcha incorrect');
    exit;
}

// 2. Préparation des données pour l'API Go
$data = [
    'nom' => $_POST['nom'],
    'pre' => $_POST['prenom'],
    'mail' => $_POST['email'],
    'mdp' => $_POST['password'],
    'id_role' => (int)$_POST['role']
];

// 3. Appel de l'API Go via CURL (Couplage demandé) 
$ch = curl_init('http://localhost:8080/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. Gestion de la réponse de Go
if ($httpCode === 201) {
    header('Location: connexion.php?success=registered');
} else {
    $resData = json_decode($response, true);
    $error = $resData['error'] ?? "Erreur lors de l'inscription";
    header('Location: inscription.php?error=' . urlencode($error));
}
exit;