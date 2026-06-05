<?php
session_start();
require_once 'includes/db.php';

$message = '';
$type    = 'danger';
$token   = trim($_GET['token'] ?? '');

if (empty($token)) {
    $message = "Lien invalide.";
} else {
    
    $stmt = $pdo->prepare("SELECT id_user FROM utilisateurs WHERE token_verification = ? AND est_verifie = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = "Ce lien est invalide ou a déjà été utilisé.";
    } else {
        
        $stmt2 = $pdo->prepare("UPDATE utilisateurs SET est_verifie = 1, token_verification = NULL WHERE id_user = ?");
        $stmt2->execute([$user['id_user']]);
        $message = "Ton adresse email a bien été vérifiée ! Tu peux maintenant te connecter.";
        $type    = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification email | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow p-5 text-center" style="max-width:500px; width:100%; border:none;">
        <h4 class="mb-3">Vérification de votre email</h4>
        <div class="alert alert-<?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <a href="connexion.php" class="btn btn-success mt-2">Se connecter →</a>
    </div>
</div>
</body>
</html>