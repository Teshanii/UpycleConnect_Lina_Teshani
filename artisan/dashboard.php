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
    <title>Espace Artisan | UpcycleConnect</title>
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
    <h4 style="color:var(--primary-green);">Mon espace artisan</h4>
    <p class="text-muted">Trouvez de la matière première et valorisez vos créations.</p>

    <!-- KPIs rapides -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                <small class="text-muted">Mon score upcycling</small>
                <h3 id="kpi-score" class="fw-bold" style="color:var(--primary-green);">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <small class="text-muted">Objets disponibles</small>
                <h3 id="kpi-objets" class="fw-bold text-primary">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #e76f51 !important;">
                <small class="text-muted">Mes réservations en cours</small>
                <h3 id="kpi-recups" class="fw-bold" style="color:#e76f51;">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <small class="text-muted">Mon abonnement</small>
                <h3 id="kpi-abo" class="fw-bold" style="color:#6f42c1;">Gratuit</h3>
            </div>
        </div>
    </div>

    <!-- Cartes navigation -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="catalogue.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Catalogue</h5>
                    <p class="text-muted small">Trouver des objets à récupérer</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="mes_recuperations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes récupérations</h5>
                    <p class="text-muted small">Mes objets réservés à aller chercher</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="mes_creations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes créations</h5>
                    <p class="text-muted small">Documenter mes transformations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="mes_prestations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes prestations</h5>
                    <p class="text-muted small">Proposer mes services à vendre/donner</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="ateliers.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Ateliers</h5>
                    <p class="text-muted small">S'inscrire aux ateliers et formations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Conseils</h5>
                    <p class="text-muted small">Articles et tutoriels de la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="abonnement.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Abonnement</h5>
                    <p class="text-muted small">Passer Premium</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="forum.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Forum</h5>
                    <p class="text-muted small">Échanger avec la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="profil.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mon Profil</h5>
                    <p class="text-muted small">Modifier mes informations</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// Mon score upcycling
fetch('http://localhost:8080/api/users')
    .then(r => r.json())
    .then(data => {
        var moi = (data || []).find(u => u.id === userId);
        if (moi) document.getElementById('kpi-score').innerText = moi.score_upcycling;
    });

// Objets disponibles dans le catalogue
fetch('http://localhost:8080/api/catalogue-artisan')
    .then(r => r.json())
    .then(data => {
        document.getElementById('kpi-objets').innerText = (data || []).length;
    });

// Mes réservations en cours (objets réservés, pas encore récupérés)
fetch('http://localhost:8080/api/demandes_box')
    .then(r => r.json())
    .then(data => {
        var mesRecups = (data || []).filter(function(d) {
            return d.id_artisan === userId && d.statut !== 'recupere';
        });
        document.getElementById('kpi-recups').innerText = mesRecups.length;
    });
</script>

</body>
</html>