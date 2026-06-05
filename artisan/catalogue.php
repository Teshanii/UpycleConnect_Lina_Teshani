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
    <title>Catalogue | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Catalogue des matériaux</h4>
    <p class="text-muted small">Trouvez des objets déposés en box. Réservez-les et allez les récupérer.</p>

    <!-- Filtres -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="recherche" class="form-control" placeholder="Rechercher un objet..." oninput="filtrer()">
        </div>
        <div class="col-md-4">
            <select id="filtre-categorie" class="form-select" onchange="filtrer()">
                <option value="">Toutes les catégories</option>
            </select>
        </div>
        <div class="col-md-4">
            <select id="filtre-type" class="form-select" onchange="filtrer()">
                <option value="">Don et vente</option>
                <option value="don">Don (gratuit)</option>
                <option value="vente">Vente</option>
            </select>
        </div>
    </div>

    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>

    <div id="msg"></div>
    <div id="catalogue" class="row g-3"></div>
</div>

<!-- Modal de confirmation de réservation -->
<div class="modal fade" id="modalRecup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Réserver cet objet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Vous allez réserver cet objet. Un code d'ouverture vous sera fourni pour aller le récupérer à la box.</p>
                <p class="text-muted small"><strong>Objet :</strong> <span id="modal-titre"></span></p>
                <p class="text-muted small"><strong>Box :</strong> <span id="modal-box"></span> — Casier <span id="modal-casier"></span></p>
                <div id="modal-msg"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmerReservation()">Confirmer la réservation</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal qui affiche le code après réservation -->
<div class="modal fade" id="modalCode" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Réservation confirmée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <strong>Votre code d'ouverture :</strong>
                    <h2 class="text-center my-3" id="code-recu"></h2>
                    <p class="small mb-0">Présentez ce code à la box pour ouvrir le casier. Une fois l'objet récupéré, allez dans <strong>"Mes récupérations"</strong> pour scanner le code-barres et finaliser.</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="mes_recuperations.php" class="btn btn-primary-upcycle">Voir mes récupérations</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var tousObjets = [];
var objetEnCours = null;
var modalRecup = new bootstrap.Modal(document.getElementById('modalRecup'));
var modalCode = new bootstrap.Modal(document.getElementById('modalCode'));

// Charger le catalogue
fetch('http://localhost:8080/api/catalogue-artisan')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('loader').style.display = 'none';
        tousObjets = data || [];
        remplirCategories();
        afficher(tousObjets);
    });

function remplirCategories() {
    var categories = [];
    tousObjets.forEach(function(o) {
        if (o.categorie && categories.indexOf(o.categorie) === -1) {
            categories.push(o.categorie);
        }
    });
    var select = document.getElementById('filtre-categorie');
    categories.forEach(function(c) {
        select.innerHTML += '<option value="' + c + '">' + c + '</option>';
    });
}

function afficher(liste) {
    var div = document.getElementById('catalogue');

    if (!liste || liste.length === 0) {
        div.innerHTML = '<p class="text-muted mt-3">Aucun objet disponible dans les box pour le moment.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(o) {
        var badgePrix = o.type_offre === 'don'
            ? '<span class="badge bg-success">Gratuit</span>'
            : '<span class="badge bg-warning text-dark">' + o.prix.toFixed(2) + ' €</span>';

        var badgeCat = o.categorie
            ? '<span class="badge bg-light text-dark border">' + o.categorie + '</span>'
            : '';

        var photo = o.photo
            ? '<img src="http://localhost/' + o.photo + '" class="card-img-top" style="height:160px; object-fit:cover;">'
            : '<div class="d-flex align-items-center justify-content-center" style="height:160px; background-color:#f0f7f0;"><span class="text-muted">Pas de photo</span></div>';

        html += '<div class="col-md-4">' +
            '<div class="card h-100 shadow-sm">' +
            photo +
            '<div class="card-body">' +
            '<h6 class="card-title">' + o.titre + '</h6>' +
            '<div class="mb-2">' + badgePrix + ' ' + badgeCat + '</div>' +
            '<p class="card-text small">' + (o.description || '') + '</p>' +
            '<p class="card-text small text-muted">Déposé par ' + o.nom_particulier + '</p>' +
            '<p class="card-text small">Box : ' + o.adresse_box + ' — Casier ' + o.numero_casier + '</p>' +
            '<button class="btn btn-primary-upcycle btn-sm w-100" onclick=\'ouvrirRecup(' + JSON.stringify(o) + ')\'>Réserver cet objet</button>' +
            '</div>' +
            '</div>' +
            '</div>';
    });

    div.innerHTML = html;
}

function filtrer() {
    var terme = document.getElementById('recherche').value.toLowerCase();
    var cat = document.getElementById('filtre-categorie').value;
    var type = document.getElementById('filtre-type').value;

    var resultats = tousObjets.filter(function(o) {
        var nom = (o.titre || '').toLowerCase();
        var matchNom = nom.includes(terme);
        var matchCat = cat === '' || o.categorie === cat;
        var matchType = type === '' || o.type_offre === type;
        return matchNom && matchCat && matchType;
    });

    afficher(resultats);
}

function ouvrirRecup(o) {
    objetEnCours = o;
    document.getElementById('modal-titre').innerText = o.titre;
    document.getElementById('modal-box').innerText = o.adresse_box;
    document.getElementById('modal-casier').innerText = o.numero_casier;
    document.getElementById('modal-msg').innerHTML = '';
    modalRecup.show();
}

function confirmerReservation() {
    fetch('http://localhost:8080/api/recuperation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: objetEnCours.id_demande, id_artisan: userId })
    }).then(function(res) {
        if (res.ok) {
            return res.json();
        } else if (res.status === 409) {
            document.getElementById('modal-msg').innerHTML = '<div class="alert alert-danger py-1">Cet objet a déjà été réservé par un autre artisan.</div>';
            return null;
        } else {
            document.getElementById('modal-msg').innerHTML = '<div class="alert alert-danger py-1">Erreur lors de la réservation.</div>';
            return null;
        }
    }).then(function(data) {
        if (data && data.code_artisan) {
            modalRecup.hide();
            document.getElementById('code-recu').innerText = data.code_artisan;
            modalCode.show();
            // Quand l'artisan ferme le modal du code, on recharge le catalogue
            document.getElementById('modalCode').addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, { once: true });
        }
    });
}
</script>

</body>
</html>