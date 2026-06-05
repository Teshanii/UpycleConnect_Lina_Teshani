<?php
session_start();
require_once 'includes/db.php';

$message = '';
$type    = 'danger';
$token   = trim($_GET['token'] ?? '');
$valide  = false;
$user    = null;

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id_user FROM utilisateurs WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $valide = true;
    } else {
        $message = "Ce lien est invalide ou expiré.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valide) {
    $mdp         = $_POST['password']  ?? '';
    $mdp_confirm = $_POST['password2'] ?? '';

    if (strlen($mdp) < 8) {
        $message = "Le mot de passe doit faire au moins 8 caractères.";
    } elseif ($mdp !== $mdp_confirm) {
        $message = "Les deux mots de passe ne correspondent pas.";
    } else {
        $hash  = password_hash($mdp, PASSWORD_BCRYPT);
        $stmt2 = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id_user = ?");
        $stmt2->execute([$hash, $user['id_user']]);
        $message = "Mot de passe mis à jour ! Tu peux maintenant te connecter.";
        $type    = 'success';
        $valide  = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow p-5" style="max-width:480px; width:100%; border:none;">
        <h4 class="text-center mb-4" style="color:var(--primary-green);">Nouveau mot de passe</h4>

        <?php if ($message): ?>
            <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($valide): ?>
        <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 8 caractères" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="password2" class="form-control" placeholder="Répète le mot de passe" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary-upcycle w-100 py-2">Enregistrer</button>
        </form>
        <?php else: ?>
            <div class="text-center mt-2">
                <a href="connexion.php" class="btn btn-success">Se connecter →</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>