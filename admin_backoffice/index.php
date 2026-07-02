<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ../connexion.php?error=access_denied');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style_admin.css">
    <style>
        .kpi-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .kpi-card h2 { font-size: 2.5rem; font-weight: bold; margin: 0; }
        .kpi-card p { margin: 0; opacity: 0.85; font-size: 0.9rem; }
        .kpi-card a { color: white; text-decoration: underline; font-size: 0.85rem; }
        .kpi-card small { opacity: 0.8; font-size: 0.75rem; }
        .kpi-vert { background: linear-gradient(135deg, #2d6a4f, #40916c); }
        .kpi-bleu { background: linear-gradient(135deg, #1565c0, #1976d2); }
        .kpi-orange { background: linear-gradient(135deg, #e65100, #f57c00); }
        .kpi-rouge { background: linear-gradient(135deg, #b71c1c, #c62828); }

        .alerte-card {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
        }
        .alerte-card.rouge {
            border-left-color: #ef4444;
            background: #fff5f5;
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">

    <!-- ENTÊTE -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tableau de bord</h2>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small" id="heure-maj"></span>
            <button class="btn btn-sm btn-outline-secondary" onclick="chargerStats()">Actualiser</button>
            <span class="badge bg-dark p-2"><?= htmlspecialchars($_SESSION['user_prenom']) ?></span>
        </div>
    </div>

    <!-- ALERTES -->
    <div id="zone-alertes" class="mb-4"></div>

    <!-- 4 KPIs PRINCIPAUX -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card kpi-bleu">
                <p>Utilisateurs inscrits</p>
                <h2 id="kpi-users">-</h2>
                <a href="utilisateurs.php">Gérer les comptes →</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card kpi-vert">
                <p>Objets recyclés</p>
                <h2 id="kpi-recycles">-</h2>
                <small>annonces validées + dépôts</small>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="kpi-card kpi-rouge">
                <p>Casiers occupés</p>
                <h2 id="kpi-box">-</h2>
                <small id="kpi-box-detail">sur 0 casiers au total</small>
            </div>
        </div>
    </div>

    <!-- TACHES A TRAITER -->
    <h5 class="mb-3">Tâches à traiter</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Annonces en attente</p>
                        <h3 id="tache-annonces" class="mb-0 text-warning">-</h3>
                    </div>
                    <a href="annonces.php" class="btn btn-warning btn-sm">Traiter</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Événements en attente</p>
                        <h3 id="tache-events" class="mb-0 text-warning">-</h3>
                    </div>
                    <a href="evenements.php" class="btn btn-warning btn-sm">Traiter</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Demandes box en attente</p>
                        <h3 id="tache-box" class="mb-0 text-warning">-</h3>
                    </div>
                    <a href="box.php" class="btn btn-warning btn-sm">Traiter</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
async function charger(url) {
    try {
        var res = await fetch(url);
        var data = await res.json();
        return Array.isArray(data) ? data : [];
    } catch (e) {
        return [];
    }
}

async function chargerStats() {
    var users    = await charger('http://localhost:8080/api/users');
    var events   = await charger('http://localhost:8080/api/evenements');
    var annonces = await charger('http://localhost:8080/api/annonces');
    var box      = await charger('http://localhost:8080/api/box');
    var casiers  = await charger('http://localhost:8080/api/casiers');
    var demandes = await charger('http://localhost:8080/api/demandes_box');

    document.getElementById('heure-maj').innerText = 'Mis à jour à ' + new Date().toLocaleTimeString('fr-FR');

    // --- ALERTES (uniquement les vraies tâches actionnables) ---
    var alertes = '';
    var annAttente = annonces.filter(function(a) { return a.statut_validation === 0; }).length;
    var evtAttente = events.filter(function(e) { return e.statut_validation === 0; }).length;
    var boxAttente = demandes.filter(function(d) { return d.statut === 'en_attente'; }).length;

    if (annAttente > 0)
        alertes += '<div class="alerte-card"><strong>' + annAttente + ' annonce(s)</strong> en attente de validation. <a href="annonces.php">Traiter →</a></div>';
    if (evtAttente > 0)
        alertes += '<div class="alerte-card"><strong>' + evtAttente + ' événement(s)</strong> en attente de validation. <a href="evenements.php">Traiter →</a></div>';
    if (boxAttente > 0)
        alertes += '<div class="alerte-card"><strong>' + boxAttente + ' demande(s) de box</strong> en attente. <a href="box.php">Traiter →</a></div>';
    if (casiers.length > 0 && casiers.filter(function(c) { return c.statut === 'libre'; }).length === 0)
        alertes += '<div class="alerte-card rouge">Tous les casiers sont occupés — pensez à libérer de la place. <a href="box.php">Gérer →</a></div>';

    if (alertes === '')
        alertes = '<div class="alert alert-success mb-0">Aucune tâche en attente. Tout est à jour.</div>';

    document.getElementById('zone-alertes').innerHTML = alertes;

    // --- KPIs ---
    var totalRecycles = annonces.filter(function(a) { return a.statut_validation === 1; }).length
                      + demandes.filter(function(d) { return d.statut === 'valide'; }).length;


    var nbOccupes = casiers.filter(function(c) { return c.statut === 'occupe'; }).length;
    var nbTotal   = casiers.length;

    document.getElementById('kpi-users').innerText      = users.length;
    document.getElementById('kpi-recycles').innerText   = totalRecycles;
    document.getElementById('kpi-box').innerText        = nbOccupes;
    document.getElementById('kpi-box-detail').innerText = 'sur ' + nbTotal + ' casier(s) au total';

    // --- TACHES ---
    document.getElementById('tache-annonces').innerText = annAttente;
    document.getElementById('tache-events').innerText   = evtAttente;
    document.getElementById('tache-box').innerText      = boxAttente;
}

chargerStats();
setInterval(chargerStats, 60000);
</script>

</body>
</html>