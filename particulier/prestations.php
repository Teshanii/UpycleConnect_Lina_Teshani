<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prestations | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Catalogue des prestations</h4>

    <!-- Filtres -->
    <div class="row g-2 mb-3 mt-2">
        <div class="col-md-6">
            <input type="text" id="recherche" class="form-control" placeholder="Rechercher une prestation..." oninput="filtrer()">
        </div>
        <div class="col-md-3">
            <select id="filtre-prix" class="form-select" onchange="filtrer()">
                <option value="">Tous les prix</option>
                <option value="0-50">0 - 50€</option>
                <option value="50-100">50 - 100€</option>
                <option value="100+">100€ et +</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="tri" class="form-select" onchange="filtrer()">
                <option value="recent">Plus récent</option>
                <option value="prix-asc">Prix croissant</option>
                <option value="prix-desc">Prix décroissant</option>
            </select>
        </div>
    </div>

    <div id="msg"></div>
    <div id="prestations" class="row g-3"></div>
</div>

<!-- Modal détail prestation -->
<div class="modal fade" id="modalPrestation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-nom"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-photo" class="mb-3"></div>
                <p><strong>Créateur :</strong> <span id="modal-createur"></span></p>
                <p><strong>Description :</strong> <span id="modal-desc"></span></p>
                <p><strong>Prix :</strong> <span id="modal-prix" class="fw-bold" style="color:var(--primary-green);"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary-upcycle" onclick="acheter()">Commander cette prestation</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var toutesPrestations = [];
var prestationActuelle = null;
var modalCtrl = new bootstrap.Modal(document.getElementById('modalPrestation'));

// Charger les prestations validées
fetch('http://localhost:8080/api/prestations')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        // On garde seulement les prestations validées
        toutesPrestations = (data || []).filter(function(p) { return p.statut_validation === 1; });
        afficher(toutesPrestations);
    })
    .catch(function() {
        document.getElementById('prestations').innerHTML = '<div class="alert alert-danger">Impossible de charger les prestations.</div>';
    });

function afficher(liste) {
    var div = document.getElementById('prestations');

    if (!liste || liste.length === 0) {
        div.innerHTML = '<p class="text-muted mt-3">Aucune prestation disponible.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(p) {
        var photo = p.photo
            ? '<img src="http://localhost/' + p.photo + '" class="card-img-top" style="height:180px; object-fit:cover;">'
            : '<div class="d-flex align-items-center justify-content-center" style="height:180px; background-color:#f0f7f0;"><span class="text-muted">Pas de photo</span></div>';

        html += '<div class="col-md-4">' +
            '<div class="card h-100 shadow-sm">' +
            photo +
            '<div class="card-body">' +
            '<h5 class="card-title">' + p.nom + '</h5>' +
            '<p class="text-muted small mb-1">Par ' + (p.createur || 'Inconnu') + '</p>' +
            '<p class="card-text small">' + (p.desc ? p.desc.substring(0, 100) + (p.desc.length > 100 ? '...' : '') : '') + '</p>' +
            '<div class="d-flex justify-content-between align-items-center">' +
            '<span class="fw-bold" style="color:var(--primary-green);">' + p.prix.toFixed(2) + ' €</span>' +
            '<button class="btn btn-primary-upcycle btn-sm" onclick=\'ouvrir(' + JSON.stringify(p) + ')\'>Voir</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    });

    div.innerHTML = html;
}

function filtrer() {
    var terme = document.getElementById('recherche').value.toLowerCase();
    var prix = document.getElementById('filtre-prix').value;
    var tri = document.getElementById('tri').value;

    var res = toutesPrestations.filter(function(p) {
        var matchNom = p.nom.toLowerCase().includes(terme) || (p.desc && p.desc.toLowerCase().includes(terme));
        var matchPrix = prix === '' ||
            (prix === '0-50' && p.prix <= 50) ||
            (prix === '50-100' && p.prix > 50 && p.prix <= 100) ||
            (prix === '100+' && p.prix > 100);
        return matchNom && matchPrix;
    });

    // Tri
    if (tri === 'prix-asc') res.sort(function(a, b) { return a.prix - b.prix; });
    else if (tri === 'prix-desc') res.sort(function(a, b) { return b.prix - a.prix; });
    else res.sort(function(a, b) { return b.id - a.id; }); // récent

    afficher(res);
}

function ouvrir(p) {
    prestationActuelle = p;
    document.getElementById('modal-nom').innerText = p.nom;
    document.getElementById('modal-createur').innerText = p.createur || 'Inconnu';
    document.getElementById('modal-desc').innerText = p.desc || '-';
    document.getElementById('modal-prix').innerText = p.prix.toFixed(2) + ' €';

    if (p.photo) {
        document.getElementById('modal-photo').innerHTML = '<img src="http://localhost/' + p.photo + '" style="max-width:100%; border-radius:8px;">';
    } else {
        document.getElementById('modal-photo').innerHTML = '';
    }

    modalCtrl.show();
}

// Acheter — paiement Stripe
function acheter() {
    if (!prestationActuelle) return;

    fetch('stripe_presta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_prestation: prestationActuelle.id,
            nom: prestationActuelle.nom,
            prix: prestationActuelle.prix * 100 // en centimes pour Stripe
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.url) {
            window.location.href = data.url;
        } else {
            alert('Erreur lors du paiement.');
        }
    });
}
</script>

</body>
</html>