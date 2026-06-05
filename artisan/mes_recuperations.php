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
    <title>Mes récupérations | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Mes récupérations</h4>
    <p class="text-muted small">Voici les objets que vous avez réservés. Allez à la box, ouvrez le casier avec votre code, puis scannez le code-barres sur l'objet pour finaliser.</p>

    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>

    <div id="msg"></div>

    <!-- Section : récupérations en cours -->
    <h5 class="mt-4" style="color:var(--primary-green);">En cours</h5>
    <div id="en-cours"></div>

    <!-- Section : historique -->
    <h5 class="mt-5" style="color:var(--primary-green);">Historique</h5>
    <div id="historique"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

function charger() {
    fetch('http://localhost:8080/api/demandes_box')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('loader').style.display = 'none';

            // Toutes les demandes de cet artisan
            var mesDemandes = (data || []).filter(function(d) { return d.id_artisan === userId; });

            // En cours : pas encore récupéré
            var enCours = mesDemandes.filter(function(d) { return d.statut !== 'recupere'; });
            // Historique : déjà récupéré
            var historique = mesDemandes.filter(function(d) { return d.statut === 'recupere'; });

            afficherEnCours(enCours);
            afficherHistorique(historique);
        });
}

function afficherEnCours(liste) {
    if (liste.length === 0) {
        document.getElementById('en-cours').innerHTML = '<p class="text-muted">Vous n\'avez aucune récupération en cours.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(d) {
        html += '<div class="card mb-3 p-3">' +
            '<h5>' + (d.titre_annonce || 'Objet') + '</h5>' +
            '<p class="small text-muted mb-1">Déposé par : ' + (d.nom_user || '-') + '</p>' +
            '<p class="small mb-1">Box : ' + (d.adresse_box || '-') + ' — Casier : ' + (d.numero_casier || '-') + '</p>' +
            '<div class="alert alert-info py-2 my-2">' +
            '<strong>Votre code d\'ouverture : ' + d.code_artisan + '</strong><br>' +
            '<small>Utilisez ce code pour ouvrir le casier à la box.</small>' +
            '</div>' +
            '<label class="form-label small">Scannez ou saisissez le code-barres collé sur l\'objet :</label>' +
            '<div class="input-group">' +
            '<input type="text" id="cb-' + d.id + '" class="form-control" placeholder="Code-barres">' +
            '<button class="btn btn-success" onclick="confirmer(' + d.id + ')">Confirmer la récupération</button>' +
            '</div>' +
            '<div id="msg-' + d.id + '" class="mt-2"></div>' +
            '</div>';
    });

    document.getElementById('en-cours').innerHTML = html;
}

function afficherHistorique(liste) {
    if (liste.length === 0) {
        document.getElementById('historique').innerHTML = '<p class="text-muted">Aucun objet récupéré pour l\'instant.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(d) {
        html += '<div class="card mb-2 p-2">' +
            '<div class="d-flex justify-content-between align-items-center">' +
            '<div>' +
            '<strong>' + (d.titre_annonce || 'Objet') + '</strong><br>' +
            '<span class="text-muted small">Déposé par : ' + (d.nom_user || '-') + ' — Box : ' + (d.adresse_box || '-') + '</span>' +
            '</div>' +
            '<span class="badge bg-success">Récupéré</span>' +
            '</div>' +
            '</div>';
    });

    document.getElementById('historique').innerHTML = html;
}

function confirmer(idDemande) {
    var code = document.getElementById('cb-' + idDemande).value.trim();
    if (!code) {
        document.getElementById('msg-' + idDemande).innerHTML = '<div class="alert alert-danger py-1">Saisissez le code-barres.</div>';
        return;
    }

    fetch('http://localhost:8080/api/confirmer-recup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: idDemande,
            code_barre_scan: code,
            id_artisan: userId
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Objet récupéré ! +5 points de score.</div>';
            charger();
        } else {
            document.getElementById('msg-' + idDemande).innerHTML = '<div class="alert alert-danger py-1">Code-barres incorrect.</div>';
        }
    });
}

charger();
</script>

</body>
</html>