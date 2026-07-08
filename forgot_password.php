<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/mail.php';

$message = '';
$type    = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        $stmt = $pdo->prepare("SELECT id_user, prenom FROM utilisateurs WHERE email = ? AND est_verifie = 1 AND est_actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // On génère un token + expiration dans 1 heure
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt2 = $pdo->prepare("UPDATE utilisateurs SET reset_token = ?, reset_token_expiry = ? WHERE id_user = ?");
            $stmt2->execute([$token, $expiry, $user['id_user']]);

            $lien = "/reset_password.php?token=" . $token;

            $contenu = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:30px; border:1px solid #eee; border-radius:10px;'>
                <h2 style='color:#2d6a4f;'>Réinitialisation de mot de passe</h2>
                <p>Bonjour <strong>" . htmlspecialchars($user['prenom']) . "</strong>,</p>
                <p>Clique sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                <a href='$lien' style='background:#2d6a4f; color:white; padding:14px 28px; border-radius:6px; text-decoration:none; display:inline-block; margin:20px 0;'>
                     Réinitialiser mon mot de passe
                </a>
                <p style='color:#888; font-size:13px;'>Ce lien expire dans <strong>1 heure</strong>. Si tu n'as pas fait cette demande, ignore ce mail.</p>
            </div>
            ";

            envoyerMail($email, "Réinitialisation de mot de passe - UpcycleConnect", $contenu);
        }

        
        $message = "Si cette adresse est associée à un compte, tu vas recevoir un email.";
        $type    = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow p-5" style="max-width:480px; width:100%; border:none;">
        <h4 class="text-center mb-1" style="color:var(--primary-green);">Mot de passe oublié</h4>
        <p class="text-muted text-center small mb-4">On t'envoie un lien pour réinitialiser ton mot de passe.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control" placeholder="ton@email.com" required>
            </div>
            <button type="submit" class="btn btn-primary-upcycle w-100 py-2">Envoyer le lien</button>
        </form>

        <p class="text-center mt-3 small">
            <a href="connexion.php" style="color:var(--primary-green);">← Retour à la connexion</a>
        </p>
    </div>
</div>
</body>
</html>