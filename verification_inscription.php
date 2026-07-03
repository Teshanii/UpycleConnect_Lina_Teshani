<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/mail.php';

// 1. Validation du captcha
if (!isset($_SESSION['captcha_reponse']) || strtolower(trim($_POST['captcha_reponse'] ?? '')) !== $_SESSION['captcha_reponse']) {
    header('Location: inscription.php?error=Captcha incorrect');
    exit;
}


$data = [
    'nom'     => trim($_POST['nom']),
    'pre'     => trim($_POST['prenom']),
    'mail'    => trim($_POST['email']),
    'mdp'     => $_POST['password'],
    'id_role' => (int)$_POST['role']
];


$ch = curl_init('http://upcycle_api:8080/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if ($httpCode !== 201) {
    $resData = json_decode($response, true);
    $error   = $resData['error'] ?? "Erreur inscription (code: $httpCode)";
    header('Location: inscription.php?error=' . urlencode($error));
    exit;
}


$email = $data['mail'];
$token = bin2hex(random_bytes(32)); // token unique de 64 caractères

$stmt = $pdo->prepare("UPDATE utilisateurs SET token_verification = ?, est_verifie = 0 WHERE email = ?");
$stmt->execute([$token, $email]);


$lien = "/verify.php?token=" . $token;

$contenu = "
<div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:30px; border:1px solid #eee; border-radius:10px;'>
    <h2 style='color:#2d6a4f;'>Bienvenue sur UpcycleConnect !</h2>
    <p>Bonjour <strong>" . htmlspecialchars($data['pre']) . "</strong>,</p>
    <p>Pour activer ton compte, clique sur le bouton ci-dessous :</p>
    <a href='$lien' style='background:#2d6a4f; color:white; padding:14px 28px; border-radius:6px; text-decoration:none; display:inline-block; margin:20px 0;'>
         Vérifier mon adresse email
    </a>
    <p style='color:#888; font-size:13px;'>Si tu n'as pas créé de compte sur UpcycleConnect, ignore ce mail.</p>
</div>
";

envoyerMail($email, "Activez votre compte UpcycleConnect", $contenu);

// 7. Redirection vers connexion avec message de succès
header('Location: connexion.php?success=registered');
exit;