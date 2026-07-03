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
    <title>Conseils | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Espace Conseils</h4>
    <p class="text-muted small">Articles et tutoriels publiés par nos salariés.</p>

    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="recherche" class="form-control" placeholder="Rechercher un article..." oninput="filtrer()">
        </div>
        <div class="col-md-3">
            <select id="filtre-type" class="form-select" onchange="filtrer()">
                <option value="">Tous les types</option>
                <option value="tuto">Tuto</option>
                <option value="news">News</option>
                <option value="conseil">Conseil</option>
            </select>
        </div>
    </div>

    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>

    <div id="liste"></div>
</div>

<div class="modal fade" id="modalArticle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    <span id="modal-type"></span> — <span id="modal-date"></span>
                </p>
                <div id="modal-contenu" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var tousLesArticles = [];
var modalArticle = new bootstrap.Modal(document.getElementById('modalArticle'));

fetch('/api/conseils')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loader').style.display = 'none';
        tousLesArticles = data || [];
        afficher(tousLesArticles);
    })
    .catch(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('liste').innerHTML = '<div class="alert alert-danger">Impossible de charger les articles.</div>';
    });

function afficher(liste) {
    if (!liste || liste.length === 0) {
        document.getElementById('liste').innerHTML = '<p class="text-muted">Aucun article disponible.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(a) {
        var badgeType;
        if (a.type === 'tuto') {
            badgeType = '<span class="badge bg-success">Tuto</span>';
        } else if (a.type === 'news') {
            badgeType = '<span class="badge bg-primary">News</span>';
        } else {
            badgeType = '<span class="badge bg-warning text-dark">Conseil</span>';
        }

        var extrait = a.contenu && a.contenu.length > 150
            ? a.contenu.substring(0, 150) + '...'
            : a.contenu;

        var date = a.date ? a.date.substring(0, 10) : '';

        html += '<div class="card mb-3 p-3">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<h5 class="mb-1">' + a.titre + '</h5>' +
            '<p class="text-muted small mb-2">' + date + ' — Par ' + (a.auteur || 'Inconnu') + '</p>' +
            badgeType +
            '<p class="mt-2 mb-0">' + extrait + '</p>' +
            '</div>' +
            '</div>' +
            '<div class="mt-2">' +
            '<button class="btn btn-outline-success btn-sm" onclick=\'ouvrir(' + JSON.stringify(a) + ')\'>Lire la suite</button>' +
            '</div>' +
            '</div>';
    });

    document.getElementById('liste').innerHTML = html;
}

function filtrer() {
    var terme = document.getElementById('recherche').value.toLowerCase();
    var type = document.getElementById('filtre-type').value;

    var resultats = tousLesArticles.filter(function(a) {
        var matchTitre = a.titre.toLowerCase().includes(terme) || a.contenu.toLowerCase().includes(terme);
        var matchType = type === '' || a.type === type;
        return matchTitre && matchType;
    });

    afficher(resultats);
}

function ouvrir(a) {
    document.getElementById('modal-titre').innerText = a.titre;
    document.getElementById('modal-type').innerText = a.type || 'Conseil';
    document.getElementById('modal-date').innerText = (a.date ? a.date.substring(0, 10) : '') + ' — Par ' + (a.auteur || 'Inconnu');
    document.getElementById('modal-contenu').innerText = a.contenu;
    modalArticle.show();
}
</script>

</body>
</html>