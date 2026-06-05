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
        .kpi-vert { background: linear-gradient(135deg, #2d6a4f, #40916c); }
        .kpi-bleu { background: linear-gradient(135deg, #1565c0, #1976d2); }
        .kpi-orange { background: linear-gradient(135deg, #e65100, #f57c00); }
        .kpi-rouge { background: linear-gradient(135deg, #b71c1c, #c62828); }

        .stat-mini {
            background: white;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #eee;
        }
        .stat-mini h3 { font-size: 1.8rem; font-weight: bold; margin: 4px 0 0; }
        .stat-mini p { font-size: 0.8rem; color: #888; margin: 0; }

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
        <div>
            <h2 class="mb-0">Tableau de bord</h2>
        </div>
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
        <div class="col-md-3">
            <div class="kpi-card kpi-bleu">
                <p>Utilisateurs inscrits</p>
                <h2 id="kpi-users">-</h2>
                <a href="utilisateurs.php">Gérer les comptes →</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card kpi-vert">
                <p>Objets recyclés</p>
                <h2 id="kpi-recycles">-</h2>
                <small>annonces validées + dépôts</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card kpi-orange">
                <p>Score moyen communauté</p>
                <h2 id="kpi-score">-</h2>
                <small>points upcycling / utilisateur</small>
            </div>
        </div>
        <div class="col-md-3">
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

    <!-- UTILISATEURS -->
    <h5 class="mb-3">Utilisateurs</h5>
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="stat-mini">
                <p>Particuliers</p>
                <h3 id="u-particuliers" class="text-primary">-</h3>
            </div>
        </div>
        <div class="col">
            <div class="stat-mini">
                <p>Artisans</p>
                <h3 id="u-artisans" class="text-success">-</h3>
            </div>
        </div>
        <div class="col">
            <div class="stat-mini">
                <p>Salariés</p>
                <h3 id="u-salaries" class="text-warning">-</h3>
            </div>
        </div>
        <div class="col">
            <div class="stat-mini">
                <p>Comptes actifs</p>
                <h3 id="u-actifs" class="text-success">-</h3>
            </div>
        </div>
        <div class="col">
            <div class="stat-mini">
                <p>Comptes bannis</p>
                <h3 id="u-bannis" class="text-danger">-</h3>
            </div>
        </div>
        <div class="col">
            <div class="stat-mini">
                <p>Meilleur score</p>
                <h3 id="u-meilleur" class="text-info">-</h3>
                <p id="u-meilleur-nom" style="font-size:10px;"></p>
            </div>
        </div>
    </div>

    <!-- ANNONCES -->
    <h5 class="mb-3">Annonces</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Total</p>
                <h3 id="a-total">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Validées</p>
                <h3 id="a-validees" class="text-success">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Refusées</p>
                <h3 id="a-refusees" class="text-danger">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Dons gratuits</p>
                <h3 id="a-dons" class="text-info">-</h3>
            </div>
        </div>
    </div>

    <!-- BOX -->
    <h5 class="mb-3">Box & Dépôts</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Box disponibles</p>
                <h3 id="b-total">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Casiers libres</p>
                <h3 id="b-libres" class="text-success">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Casiers occupés</p>
                <h3 id="b-occupes" class="text-danger">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Dépôts validés</p>
                <h3 id="b-valides" class="text-success">-</h3>
            </div>
        </div>
    </div>

    <!-- EVENEMENTS -->
    <h5 class="mb-3">Ateliers & Événements</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Total événements</p>
                <h3 id="e-total">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Validés</p>
                <h3 id="e-valides" class="text-success">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Total inscrits</p>
                <h3 id="e-inscrits" class="text-primary">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini">
                <p>Articles conseils</p>
                <h3 id="e-conseils">-</h3>
            </div>
        </div>
    </div>

    <!-- FORUM -->
    <h5 class="mb-3">Forum & Modération</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-mini">
                <p>Total messages</p>
                <h3 id="f-total">-</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini">
                <p>Modérés</p>
                <h3 id="f-moderes" class="text-success">-</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini" style="border: 2px solid #ef4444;">
                <p>Non modérés</p>
                <h3 id="f-nonmoderes" class="text-danger">-</h3>
                <a href="messages.php" style="font-size:11px; color:#ef4444;">Modérer →</a>
            </div>
        </div>
    </div>

    <!-- DERNIÈRES ANNONCES -->
    <h5 class="mb-3">Dernières annonces déposées</h5>
    <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="tbody-annonces">
                    <tr><td colspan="5" class="text-center text-muted">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DERNIERS UTILISATEURS -->
    <h5 class="mb-3">Derniers utilisateurs inscrits</h5>
    <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Score</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="tbody-users">
                    <tr><td colspan="5" class="text-center text-muted">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Correspondance id_role → label lisible
var roles = { 1: 'Admin', 2: 'Salarié', 3: 'Artisan', 4: 'Particulier' };

// Fetch simple : retourne un tableau, jamais d'erreur
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
    // Chaque API est chargée séparément — si une échoue, les autres continuent
    var users    = await charger('http://localhost:8080/api/users');
    var events   = await charger('http://localhost:8080/api/evenements');
    var annonces = await charger('http://localhost:8080/api/annonces');
    var box      = await charger('http://localhost:8080/api/box');
    var casiers  = await charger('http://localhost:8080/api/casiers');
    var demandes = await charger('http://localhost:8080/api/demandes_box');
    var conseils = await charger('http://localhost:8080/api/conseils');
    var messages = await charger('http://localhost:8080/api/messages');

    // Heure de mise à jour
    document.getElementById('heure-maj').innerText = 'Mis à jour à ' + new Date().toLocaleTimeString('fr-FR');

    // --- ALERTES ---
    var alertes = '';
    var annAttente = annonces.filter(function(a) { return a.statut_validation === 0; }).length;
    var evtAttente = events.filter(function(e) { return e.statut_validation === 0; }).length;
    var boxAttente = demandes.filter(function(d) { return d.statut === 'en_attente'; }).length;
    var msgNonMod  = messages.filter(function(m) { return m.est_modere === 0; }).length;

    if (annAttente > 0)
        alertes += '<div class="alerte-card"> <strong>' + annAttente + ' annonce(s)</strong> en attente de validation. <a href="annonces.php">Traiter →</a></div>';
    if (evtAttente > 0)
        alertes += '<div class="alerte-card"> <strong>' + evtAttente + ' événement(s)</strong> en attente de validation. <a href="evenements.php">Traiter →</a></div>';
    if (boxAttente > 0)
        alertes += '<div class="alerte-card">⚠️ <strong>' + boxAttente + ' demande(s) de box</strong> en attente. <a href="box.php">Traiter →</a></div>';
    if (msgNonMod > 0)
        alertes += '<div class="alerte-card rouge"> <strong>' + msgNonMod + ' message(s)</strong> du forum non modérés. <a href="messages.php">Modérer →</a></div>';
    if (casiers.length > 0 && casiers.filter(function(c) { return c.statut === 'libre'; }).length === 0)
        alertes += '<div class="alerte-card rouge"> Tous les casiers sont occupés — pensez à libérer de la place. <a href="box.php">Gérer →</a></div>';

    document.getElementById('zone-alertes').innerHTML = alertes;

    // --- KPIs ---
    var totalRecycles = annonces.filter(function(a) { return a.statut_validation === 1; }).length
                      + demandes.filter(function(d) { return d.statut === 'valide'; }).length;

    var scoreTotal = users.reduce(function(s, u) { return s + (u.score_upcycling || 0); }, 0);
    var scoreMoyen = users.length > 0 ? Math.round(scoreTotal / users.length) : 0;

    // Casiers occupés en CHIFFRE (plus de pourcentage)
    var nbOccupes = casiers.filter(function(c) { return c.statut === 'occupe'; }).length;
    var nbTotal   = casiers.length;

    document.getElementById('kpi-users').innerText    = users.length;
    document.getElementById('kpi-recycles').innerText = totalRecycles;
    document.getElementById('kpi-score').innerText    = scoreMoyen + ' pts';
    document.getElementById('kpi-box').innerText      = nbOccupes;
    document.getElementById('kpi-box-detail').innerText = 'sur ' + nbTotal + ' casier(s) au total';

    // --- TACHES ---
    document.getElementById('tache-annonces').innerText = annAttente;
    document.getElementById('tache-events').innerText   = evtAttente;
    document.getElementById('tache-box').innerText      = boxAttente;

    // --- UTILISATEURS ---
    document.getElementById('u-particuliers').innerText = users.filter(function(u) { return u.id_role === 4; }).length;
    document.getElementById('u-artisans').innerText     = users.filter(function(u) { return u.id_role === 3; }).length;
    document.getElementById('u-salaries').innerText     = users.filter(function(u) { return u.id_role === 2; }).length;
    document.getElementById('u-actifs').innerText       = users.filter(function(u) { return u.est_actif === 1; }).length;
    document.getElementById('u-bannis').innerText       = users.filter(function(u) { return u.est_actif === 0; }).length;

    // Meilleur score — anti-crash si users vide
    if (users.length > 0) {
        var meilleur = users.reduce(function(best, u) {
            return (u.score_upcycling || 0) > (best.score_upcycling || 0) ? u : best;
        }, users[0]);
        document.getElementById('u-meilleur').innerText     = (meilleur.score_upcycling || 0) + ' pts';
        document.getElementById('u-meilleur-nom').innerText = meilleur.nom ? meilleur.nom + ' ' + meilleur.pre : '-';
    } else {
        document.getElementById('u-meilleur').innerText     = '0 pts';
        document.getElementById('u-meilleur-nom').innerText = '-';
    }

    // --- ANNONCES ---
    document.getElementById('a-total').innerText    = annonces.length;
    document.getElementById('a-validees').innerText = annonces.filter(function(a) { return a.statut_validation === 1; }).length;
    document.getElementById('a-refusees').innerText = annonces.filter(function(a) { return a.statut_validation === 2; }).length;
    document.getElementById('a-dons').innerText     = annonces.filter(function(a) { return a.type_offre === 'don'; }).length;

    // --- BOX ---
    document.getElementById('b-total').innerText   = box.length;
    document.getElementById('b-libres').innerText  = casiers.filter(function(c) { return c.statut === 'libre'; }).length;
    document.getElementById('b-occupes').innerText = nbOccupes;
    document.getElementById('b-valides').innerText = demandes.filter(function(d) { return d.statut === 'valide'; }).length;

    // --- EVENEMENTS ---
    document.getElementById('e-total').innerText   = events.length;
    document.getElementById('e-valides').innerText = events.filter(function(e) { return e.statut_validation === 1; }).length;
    document.getElementById('e-inscrits').innerText = events.reduce(function(s, e) { return s + (e.nb_inscrits || 0); }, 0);
    document.getElementById('e-conseils').innerText = conseils.length;

    // --- FORUM ---
    document.getElementById('f-total').innerText      = messages.length;
    document.getElementById('f-moderes').innerText    = messages.filter(function(m) { return m.est_modere === 1; }).length;
    document.getElementById('f-nonmoderes').innerText = msgNonMod;

    // --- TABLEAU ANNONCES (5 dernières) ---
    var tbodyA = document.getElementById('tbody-annonces');
    tbodyA.innerHTML = '';
    if (annonces.length === 0) {
        tbodyA.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucune annonce.</td></tr>';
    } else {
        annonces.slice(-5).reverse().forEach(function(a) {
            var badge = a.statut_validation === 1
                ? '<span class="badge bg-success">Validée</span>'
                : a.statut_validation === 2
                ? '<span class="badge bg-danger">Refusée</span>'
                : '<span class="badge bg-warning text-dark">En attente</span>';
            tbodyA.innerHTML += '<tr>'
                + '<td><strong>' + (a.titre || '-') + '</strong></td>'
                + '<td>' + (a.auteur || '-') + '</td>'
                + '<td>' + (a.categorie || '-') + '</td>'
                + '<td>' + (a.type_offre === 'don' ? 'Don gratuit' : 'Vente') + '</td>'
                + '<td>' + badge + '</td>'
                + '</tr>';
        });
    }

    // --- TABLEAU UTILISATEURS (5 derniers) ---
    var tbodyU = document.getElementById('tbody-users');
    tbodyU.innerHTML = '';
    if (users.length === 0) {
        tbodyU.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun utilisateur.</td></tr>';
    } else {
        users.slice(-5).reverse().forEach(function(u) {
            var badge = u.est_actif === 1
                ? '<span class="badge bg-success">Actif</span>'
                : '<span class="badge bg-danger">Banni</span>';
            // On utilise roles[u.id_role] au lieu de u.role qui n'existe pas dans l'API
            tbodyU.innerHTML += '<tr>'
                + '<td><strong>' + (u.nom || '') + ' ' + (u.pre || '') + '</strong></td>'
                + '<td>' + (u.mail || '-') + '</td>'
                + '<td>' + (roles[u.id_role] || '-') + '</td>'
                + '<td>' + (u.score_upcycling || 0) + ' pts</td>'
                + '<td>' + badge + '</td>'
                + '</tr>';
        });
    }
}

// Chargement initial + auto-refresh toutes les 60 secondes
chargerStats();
setInterval(chargerStats, 60000);
</script>

</body>
</html>