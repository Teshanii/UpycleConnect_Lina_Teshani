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
    <title>Validation Prestations | UpcycleConnect</title>
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

<?php include __DIR__ . '/menu.php'; ?>

<div class="container mt-4">
    <h4 class="mt-3" style="color:var(--primary-green);">Validation des prestations</h4>
    <p class="text-muted small">Validez ou refusez les prestations proposées par les artisans.</p>

    <!-- Filtres -->
    <div class="mb-3">
        <button class="btn btn-warning btn-sm me-2" onclick="filtrer('attente')">En attente</button>
        <button class="btn btn-success btn-sm me-2" onclick="filtrer('valide')">Validées</button>
        <button class="btn btn-danger btn-sm me-2" onclick="filtrer('refuse')">Refusées</button>
        <button class="btn btn-secondary btn-sm" onclick="filtrer('tous')">Toutes</button>
    </div>

    <div id="prestations" class="row g-3"></div>
</div>

<!-- Modal détail -->
<div class="modal fade" id="modalPrestation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-nom"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-photo" class="mb-3"></div>
                <p><strong>Créateur (artisan) :</strong> <span id="modal-createur"></span></p>
                <p><strong>Description :</strong> <span id="modal-desc"></span></p>
                <p><strong>Prix :</strong> <span id="modal-prix" class="fw-bold" style="color:var(--primary-green);"></span></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var toutesPrestations = [];
var modalCtrl = new bootstrap.Modal(document.getElementById('modalPrestation'));

// Charger toutes les prestations
function charger() {
    fetch('/api/prestations')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            toutesPrestations = data || [];
            afficher(toutesPrestations);
        });
}

function afficher(liste) {
    var div = document.getElementById('prestations');

    if (!liste || liste.length === 0) {
        div.innerHTML = '<p class="text-muted mt-3">Aucune prestation.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(p) {
        // Badge de statut
        var badge;
        if (p.statut_validation === 1) {
            badge = '<span class="badge bg-success">Validée</span>';
        } else if (p.statut_validation === 2) {
            badge = '<span class="badge bg-danger">Refusée</span>';
        } else {
            badge = '<span class="badge bg-warning text-dark">En attente</span>';
        }

        var photo = p.photo
            ? '<img src="/' + p.photo + '" class="card-img-top" style="height:160px; object-fit:cover;">'
            : '<div class="d-flex align-items-center justify-content-center" style="height:160px; background-color:#f0f7f0;"><span class="text-muted">Pas de photo</span></div>';

        // Boutons selon le statut
        var boutons = '';
        if (p.statut_validation === 0) {
            boutons = '<button class="btn btn-success btn-sm me-1" onclick="valider(' + p.id + ')">Valider</button>' +
                '<button class="btn btn-outline-danger btn-sm" onclick="refuser(' + p.id + ')">Refuser</button>';
        } else if (p.statut_validation === 2 && p.motif_refus) {
            boutons = '<div class="alert alert-danger mt-2 mb-0 py-1 px-2 small">Motif : ' + p.motif_refus + '</div>';
        }

        html += '<div class="col-md-4">' +
            '<div class="card h-100 shadow-sm">' +
            photo +
            '<div class="card-body">' +
            '<h6 class="card-title">' + p.nom + ' ' + badge + '</h6>' +
            '<p class="text-muted small mb-1">Par ' + (p.createur || 'Inconnu') + '</p>' +
            '<p class="card-text small">' + (p.desc ? p.desc.substring(0, 80) + (p.desc.length > 80 ? '...' : '') : '') + '</p>' +
            '<p class="fw-bold" style="color:var(--primary-green);">' + p.prix.toFixed(2) + ' €</p>' +
            '<button class="btn btn-outline-secondary btn-sm mb-2" onclick=\'voir(' + JSON.stringify(p) + ')\'>Détails</button><br>' +
            boutons +
            '</div>' +
            '</div>' +
            '</div>';
    });

    div.innerHTML = html;
}

function filtrer(type) {
    if (type === 'tous') return afficher(toutesPrestations);
    if (type === 'attente') return afficher(toutesPrestations.filter(function(p) { return p.statut_validation === 0; }));
    if (type === 'valide') return afficher(toutesPrestations.filter(function(p) { return p.statut_validation === 1; }));
    if (type === 'refuse') return afficher(toutesPrestations.filter(function(p) { return p.statut_validation === 2; }));
}

function voir(p) {
    document.getElementById('modal-nom').innerText = p.nom;
    document.getElementById('modal-createur').innerText = p.createur || 'Inconnu';
    document.getElementById('modal-desc').innerText = p.desc || '-';
    document.getElementById('modal-prix').innerText = p.prix.toFixed(2) + ' €';

    if (p.photo) {
        document.getElementById('modal-photo').innerHTML = '<img src="/' + p.photo + '" style="max-width:100%; border-radius:8px;">';
    } else {
        document.getElementById('modal-photo').innerHTML = '';
    }

    modalCtrl.show();
}

// Valider une prestation (PUT sans rien = validation dans le handler Go)
function valider(id) {
    if (!confirm('Valider cette prestation ? Elle sera visible par les particuliers.')) return;
    fetch('/api/prestations/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    }).then(function(res) { if (res.ok) charger(); });
}

// Refuser avec motif
function refuser(id) {
    var motif = prompt('Motif du refus :');
    if (!motif) return;
    fetch('/api/prestations/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ motif_refus: motif })
    }).then(function(res) { if (res.ok) charger(); });
}

charger();
</script>

</body>
</html>