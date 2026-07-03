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

<?php include __DIR__ . '/menu.php'; ?>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);">Mon espace salarié</h4>
    <p class="text-muted">Animez la communauté UpcycleConnect.</p>

    <!-- KPIs rapides (cliquables) -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <a href="mes_ateliers.php" class="text-decoration-none">
                <div class="card p-3 border-0 shadow-sm h-100" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Mes ateliers</small>
                    <h3 id="kpi-ateliers" class="fw-bold" style="color:var(--primary-green);">0</h3>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                    <small class="text-muted">Mes articles</small>
                    <h3 id="kpi-articles" class="fw-bold text-primary">0</h3>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="prestations.php" class="text-decoration-none">
                <div class="card p-3 border-0 shadow-sm h-100" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Prestations à valider</small>
                    <h3 id="kpi-prestations" class="fw-bold" style="color:#f4a261;">0</h3>
                </div>
            </a>
        </div>
    </div>

    <!-- Alertes (n'apparaissent que s'il y a quelque chose à faire) -->
    <div id="alertes" class="mb-4"></div>

    <!-- Cartes navigation -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="mes_ateliers.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes ateliers</h5>
                    <p class="text-muted small">Créer, gérer et voir les inscrits (présence)</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="planning.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Planning</h5>
                    <p class="text-muted small">Vue calendrier et taux de remplissage</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Conseils</h5>
                    <p class="text-muted small">Publier des tutoriels, des news et des conseils</p>
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

// Ateliers : total + alertes (en attente de validation, presque complets)
fetch('/api/evenements')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var mesAteliers = (data || []).filter(function(e) { return e.id_anim === userId; });
        document.getElementById('kpi-ateliers').innerText = mesAteliers.length;

        // En attente de validation (statut 0)
        var enAttente = mesAteliers.filter(function(e) { return e.statut_validation === 0; }).length;
        var refuses = mesAteliers.filter(function(e) { return e.statut_validation === 2; }).length;

        // Presque complets : validé, il reste 1 à 3 places
        var presqueComplet = mesAteliers.filter(function(e) {
            var restantes = e.place - e.nb_inscrits;
            return e.statut_validation === 1 && restantes > 0 && restantes <= 3;
        }).length;

        var html = '';
        if (refuses > 0) {
            html += '<a href="mes_ateliers.php" class="text-decoration-none">' +
                '<div class="alert alert-danger py-2 mb-2">' + refuses + ' atelier(s) refusé(s) — voir le motif</div></a>';
        }
        if (enAttente > 0) {
            html += '<a href="mes_ateliers.php" class="text-decoration-none">' +
                '<div class="alert alert-warning py-2 mb-2">' + enAttente + ' atelier(s) en attente de validation</div></a>';
        }
        if (presqueComplet > 0) {
            html += '<a href="mes_ateliers.php" class="text-decoration-none">' +
                '<div class="alert alert-info py-2 mb-2">' + presqueComplet + ' atelier(s) presque complet(s)</div></a>';
        }
        document.getElementById('alertes').innerHTML = html;
    });

// Mes articles (filtre par auteur)
fetch('/api/conseils?id_auteur=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('kpi-articles').innerText = (data || []).length;
    });

// Prestations EN ATTENTE de validation (statut 0)
fetch('/api/prestations')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var enAttente = (data || []).filter(function(p) { return p.statut_validation === 0; });
        document.getElementById('kpi-prestations').innerText = enAttente.length;
    });
</script>

</body>
</html>