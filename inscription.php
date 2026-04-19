<?php
session_start();
$_SESSION['captcha_question'] = "Quelle est la couleur principale d'UpcycleConnect ?";
$_SESSION['captcha_reponse'] = "vert";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 550px;">
            <div class="card-body">
                <h2 class="text-center mb-4" style="color: var(--primary-green);">Créer un compte</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger small">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="verification_inscription.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" name="prenom" required placeholder="Ex: Lina">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required placeholder="Ex: Chellala">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <input type="email" class="form-control" name="email" required placeholder="nom@exemple.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="password" required placeholder="6 caractères min.">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vous êtes :</label>
                        <select class="form-select" name="role" required>
                            <option value="" disabled selected>Choisissez votre profil...</option>
                            <option value="4">Un Particulier</option>
                            <option value="3">Un Professionnel / Artisan</option>
                            <option value="2">Un Salarié (Animateur/Formateur)</option>
                        </select>
                        <small class="text-muted">Votre espace sera adapté selon ce choix.</small>
                    </div>

                    <div class="captcha-box mb-3">
                        <p class="mb-1"><strong>Sécurité :</strong> <?php echo $_SESSION['captcha_question']; ?></p>
                        <input type="text" class="form-control" name="captcha_reponse" required placeholder="Votre réponse...">
                    </div>

                    <button type="submit" class="btn btn-primary-upcycle w-100 py-2">CRÉER MON COMPTE</button>
                </form>
                
                <p class="text-center mt-3 small">
                    Déjà inscrit ? <a href="connexion.php" style="color: var(--primary-green);">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>