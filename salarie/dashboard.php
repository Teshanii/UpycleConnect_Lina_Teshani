<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Salarié | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <span class="text-white me-3">Salarié : <?php echo $_SESSION['user_prenom']; ?></span>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);">Mon espace salarié</h4>
    <p class="text-muted">Animez la communauté UpcycleConnect.</p>

    <!-- KPIs rapides -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                <small class="text-muted">Mes ateliers</small>
                <h3 id="kpi-ateliers" class="fw-bold" style="color:var(--primary-green);">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <small class="text-muted">Mes articles</small>
                <h3 id="kpi-articles" class="fw-bold text-primary">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                <small class="text-muted">Prestations à valider</small>
                <h3 id="kpi-prestations" class="fw-bold" style="color:#f4a261;">0</h3>
            </div>
        </div>
    </div>

    <!-- Cartes navigation -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="mes_ateliers.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes ateliers</h5>
                    <p class="text-muted small">Créer et gérer vos ateliers</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="planning.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Planning</h5>
                    <p class="text-muted small">Voir les inscrits par atelier</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Conseils</h5>
                    <p class="text-muted small">Rédiger des articles</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="prestations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Validation prestations</h5>
                    <p class="text-muted small">Valider les services des artisans</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="forum.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Forum</h5>
                    <p class="text-muted small">Animer et modérer</p>
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

// Compter mes ateliers (filtre sur id_anim)
fetch('http://localhost:8080/api/evenements')
    .then(r => r.json())
    .then(data => {
        var mesAteliers = (data || []).filter(e => e.id_anim === userId);
        document.getElementById('kpi-ateliers').innerText = mesAteliers.length;
    });

// Compter mes articles (filtre par auteur)
fetch('http://localhost:8080/api/conseils?id_auteur=' + userId)
    .then(r => r.json())
    .then(data => {
        document.getElementById('kpi-articles').innerText = (data || []).length;
    });

// Compter les prestations EN ATTENTE de validation (statut 0)
fetch('http://localhost:8080/api/prestations')
    .then(r => r.json())
    .then(data => {
        var enAttente = (data || []).filter(p => p.statut_validation === 0);
        document.getElementById('kpi-prestations').innerText = enAttente.length;
    });
</script>

</body>
</html>