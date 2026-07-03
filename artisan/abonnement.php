<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <span class="text-white me-3">Artisan : <?php echo $_SESSION['user_prenom']; ?></span>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Mon abonnement</h4>
    <p class="text-muted small">Passez Premium pour débloquer les statistiques avancées et les alertes prioritaires.</p>

    <!-- Bandeau si déjà Premium -->
    <div id="banniere-premium" class="alert alert-success d-none">
        <strong>Vous êtes Premium !</strong> Vous avez accès à toutes les fonctionnalités avancées.
    </div>

    <div id="msg"></div>

    <div class="row g-4 mt-2">
        <!-- Offre Gratuite -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Gratuit</h5>
                    <h2 class="my-3">0 €<small class="text-muted fs-6">/mois</small></h2>
                    <ul class="list-unstyled text-start small">
                        <li class="mb-2">✓ Accès au catalogue des objets</li>
                        <li class="mb-2">✓ Réserver et récupérer des objets</li>
                        <li class="mb-2">✓ Documenter ses créations</li>
                        <li class="mb-2 text-muted">✗ Statistiques avancées</li>
                        <li class="mb-2 text-muted">✗ Alertes prioritaires</li>
                    </ul>
                    <span id="badge-gratuit" class="badge bg-secondary d-none">Votre offre actuelle</span>
                </div>
            </div>
        </div>

        <!-- Offre Premium -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm" style="border: 2px solid var(--primary-green);">
                <div class="card-body text-center">
                    <h5 class="card-title" style="color:var(--primary-green);">Premium</h5>
                    <h2 class="my-3">15 €<small class="text-muted fs-6">/mois</small></h2>
                    <ul class="list-unstyled text-start small">
                        <li class="mb-2">✓ Tout le Gratuit, plus :</li>
                        <li class="mb-2">✓ Analyse de son impact écologique</li>
                        <li class="mb-2">✓ Remises sur les frais de services</li>
                        <li class="mb-2">✓ Mise en avant de ses créations</li>
                    </ul>
                    <span id="badge-premium" class="badge bg-success d-none">Votre offre actuelle</span>
                    <button id="btn-premium" class="btn btn-primary-upcycle w-100" onclick="passerPremium()">Passer Premium</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// On regarde l'abonnement actuel pour afficher le bon état
fetch('/api/abonnement?id_user=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.abonnement === 'premium') {
            // Déjà premium : on masque le bouton et on affiche le bandeau
            document.getElementById('banniere-premium').classList.remove('d-none');
            document.getElementById('badge-premium').classList.remove('d-none');
            document.getElementById('btn-premium').classList.add('d-none');
        } else {
            // Gratuit : on montre que c'est l'offre actuelle
            document.getElementById('badge-gratuit').classList.remove('d-none');
        }
    });

function passerPremium() {
    var btn = document.getElementById('btn-premium');
    btn.disabled = true;
    btn.innerText = 'Redirection vers le paiement...';

    // On crée la session de paiement Stripe (15€ = 1500 centimes)
    fetch('stripe_abonnement.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prix: 1500, titre: 'Abonnement Premium UpcycleConnect' })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.url) {
            // On redirige vers la page de paiement Stripe
            window.location.href = data.url;
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur lors de la création du paiement.</div>';
            btn.disabled = false;
            btn.innerText = 'Passer Premium';
        }
    })
    .catch(function() {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Le serveur de paiement est injoignable.</div>';
        btn.disabled = false;
        btn.innerText = 'Passer Premium';
    });
}
</script>

</body>
</html>