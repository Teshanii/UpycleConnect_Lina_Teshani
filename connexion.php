<?php
session_start();

// Déconnexion si logout=1
if (isset($_GET['logout'])) {
    session_destroy();
    session_unset();
}

// 1. Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 450px; border: none;">
            <div class="card-body">
                <ul class="nav nav-tabs justify-content-center mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="connexion.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inscription.php">Inscription</a>
                    </li>
                </ul>

                <h2 class="text-center mb-4" style="color: var(--primary-green);">Se connecter</h2>

                <?php if (isset($_GET['success'])): ?>
                    <?php if ($_GET['success'] == 'registered'): ?>
                        <div class="alert alert-warning small">
                            <strong>Inscription réussie !</strong><br>
                            Un mail d'activation vient de vous être envoyé. 
                            <strong>Vous devez cliquer sur le lien dans le mail</strong> avant de pouvoir vous connecter.
                        </div>
                    <?php elseif ($_GET['success'] == 'verified'): ?>
                        <div class="alert alert-success small">
                            <strong>Compte activé !</strong><br>
                            Votre email a été validé avec succès. Vous pouvez maintenant vous connecter ci-dessous.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'access_denied'): ?>
                    <div class="alert alert-danger small">
                        Accès refusé. Veuillez vous connecter avec un compte administrateur.
                    </div>
                <?php endif; ?>

                <div id="api-error" class="alert alert-danger d-none small"></div>

                <form id="form-login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="exemple@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="mdp" name="password" placeholder="********" required>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn btn-primary-upcycle w-100 py-2">SE CONNECTER</button>

                    <div class="text-center mt-2">
                        <a href="forgot_password.php" class="text-muted small">Mot de passe oublié ?</a>
                    </div>
                </form>

                <p class="text-center mt-4">
                    Pas encore membre ? <a href="inscription.php" style="color: var(--primary-green);">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("form-login").onsubmit = async (e) => {
            e.preventDefault();
            const msgErreur = document.getElementById("api-error");
            const btn = e.target.querySelector("button[type=submit]");
            msgErreur.classList.add("d-none");

            // On désactive le bouton pour éviter le double-clic
            btn.disabled = true;
            btn.innerText = "Connexion...";

            const payload = {
                email: document.getElementById("email").value,
                mdp: document.getElementById("mdp").value
            };

            try {
                // On envoie email + mdp à init_session.php.
                // C'est LUI qui appelle l'API Go côté serveur pour vérifier.
                const sessionRes = await fetch("init_session.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });

                const data = await sessionRes.json();

                if (sessionRes.ok) {
                    // Redirection selon le rôle renvoyé par le serveur
                    if (data.id_role === 1) {
                        window.location.href = "admin_backoffice/index.php";
                    } else {
                        window.location.href = "index.php";
                    }
                } else {
                    // Erreur renvoyée par Go (mdp faux, compte non activé...)
                    msgErreur.innerText = data.error || "Identifiants invalides.";
                    msgErreur.classList.remove("d-none");
                    btn.disabled = false;
                    btn.innerText = "SE CONNECTER";
                }
            } catch (error) {
                msgErreur.innerText = "Le serveur de gestion est actuellement injoignable.";
                msgErreur.classList.remove("d-none");
                btn.disabled = false;
                btn.innerText = "SE CONNECTER";
            }
        };
    </script>
</body>
</html>