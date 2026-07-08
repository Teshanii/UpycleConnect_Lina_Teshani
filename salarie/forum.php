<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../connexion.php');
    exit;
}
$sujetId = isset($_GET['sujet']) ? intval($_GET['sujet']) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Forum — Animation & Modération</h4>

    <?php if ($sujetId === 0): ?>
        <!-- ===== PAGE LISTE DES SUJETS ===== -->
        <p class="text-muted small">Ouvrez des sujets, répondez et modérez les messages.</p>
        <div class="card mb-4 p-3 shadow-sm border-0">
            <h6>Ouvrir un nouveau sujet</h6>
            <input type="text" id="titre" class="form-control mb-2" placeholder="Titre du sujet (ex: Idées pour recycler des palettes)">
            <select id="categorie" class="form-select form-select-sm mb-2" style="max-width:220px;">
                <option value="Général">Général</option>
                <option value="Annonces">Annonces</option>
                <option value="Questions">Questions</option>
                <option value="Astuces">Astuces</option>
            </select>
            <textarea id="contenu" class="form-control mb-2" rows="3" placeholder="Votre premier message..."></textarea>
            <button class="btn btn-primary-upcycle btn-sm" onclick="creerSujet()">Créer le sujet</button>
            <div id="msg"></div>
        </div>

        <div class="mb-2">
            <button class="btn btn-success btn-sm me-2" onclick="filtrer('visible')">Visibles</button>
            <button class="btn btn-warning btn-sm me-2" onclick="filtrer('modere')">Masqués</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrer('tous')">Tous</button>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-md-7">
                <input type="text" id="recherche" class="form-control form-control-sm" placeholder="Rechercher un sujet ou un auteur..." oninput="afficherListe()">
            </div>
            <div class="col-md-5">
                <select id="filtre-cat" class="form-select form-select-sm" onchange="afficherListe()">
                    <option value="">Toutes les catégories</option>
                    <option value="Général">Général</option>
                    <option value="Annonces">Annonces</option>
                    <option value="Questions">Questions</option>
                    <option value="Astuces">Astuces</option>
                </select>
            </div>
        </div>
        <div id="sujets"></div>

    <?php else: ?>
        
        <a href="forum.php" style="color:var(--primary-green);">← Retour aux sujets</a>
        <div id="detail-sujet" class="mt-3"></div>
    <?php endif; ?>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var sujetId = <?php echo $sujetId; ?>;
var tousMessages = [];
var filtreActuel = 'visible';

function echapper(t) {
    if (t === null || t === undefined) return '';
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function formatDate(d) { return d ? d.replace('T', ' ').replace('Z', '').substring(0, 16) : ''; }

function charger() {
    fetch('/api/messages')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tousMessages = data || [];
            if (sujetId > 0) afficherSujet();
            else afficherListe();
        });
}


function afficherListe() {
    var div = document.getElementById('sujets');
    var recherche = document.getElementById('recherche').value.toLowerCase();
    var cat = document.getElementById('filtre-cat').value;

    var sujets = tousMessages.filter(function(m) { return m.id_message_parent === 0; });
    if (filtreActuel === 'visible') sujets = sujets.filter(function(m) { return m.est_modere === 0; });
    else if (filtreActuel === 'modere') sujets = sujets.filter(function(m) { return m.est_modere === 1; });
    if (cat) sujets = sujets.filter(function(m) { return m.categorie === cat; });
    if (recherche) {
        sujets = sujets.filter(function(m) {
            var dansSujet = ((m.titre || '') + ' ' + m.contenu + ' ' + m.auteur).toLowerCase().indexOf(recherche) !== -1;
            var rep = tousMessages.filter(function(r) { return r.id_message_parent === m.id; });
            var dansRep = rep.some(function(r) { return (r.contenu + ' ' + r.auteur).toLowerCase().indexOf(recherche) !== -1; });
            return dansSujet || dansRep;
        });
    }
    sujets.sort(function(a, b) { return (b.epingle || 0) - (a.epingle || 0); });

    if (sujets.length === 0) { div.innerHTML = '<p class="text-muted">Aucun sujet.</p>'; return; }

    var html = '';
    sujets.forEach(function(m) {
        var nbRep = tousMessages.filter(function(r) { return r.id_message_parent === m.id; }).length;
        var badges = '<span class="badge bg-info text-dark ms-2">' + echapper(m.categorie || 'Général') + '</span>';
        if (m.epingle === 1) badges += '<span class="badge bg-primary ms-1">Épinglé</span>';
        if (m.est_modere === 1) badges += '<span class="badge bg-danger ms-1">Masqué</span>';

        var btnEpingle = m.epingle === 1
            ? '<button class="btn btn-outline-secondary btn-sm me-1" onclick="epingler(' + m.id + ', 0, ' + m.est_modere + ')">Désépingler</button>'
            : '<button class="btn btn-outline-primary btn-sm me-1" onclick="epingler(' + m.id + ', 1, ' + m.est_modere + ')">Épingler</button>';
        var btnModere = m.est_modere === 1
            ? '<button class="btn btn-outline-success btn-sm me-1" onclick="changerStatut(' + m.id + ', 0, ' + m.epingle + ')">Réactiver</button>'
            : '<button class="btn btn-warning btn-sm me-1" onclick="changerStatut(' + m.id + ', 1, ' + m.epingle + ')">Masquer</button>';
        var btnSuppr = '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + m.id + ')">Supprimer</button>';

        html += '<div class="card mb-2 p-3">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div style="flex:1;">' +
            '<a href="forum.php?sujet=' + m.id + '" class="text-decoration-none">' +
            '<strong style="color:var(--primary-green);">' + echapper(m.titre || '(sans titre)') + '</strong></a>' + badges + '<br>' +
            '<span class="text-muted small">par ' + echapper(m.auteur) + ' · ' + formatDate(m.date) + ' · ' + nbRep + ' réponse(s)</span>' +
            '</div>' +
            '<div class="text-end" style="min-width:240px;">' + '<a href="forum.php?sujet=' + m.id + '" class="btn btn-success btn-sm me-1">Voir</a>' + btnEpingle + btnModere + btnSuppr + '</div>' +
            '</div></div>';
    });
    div.innerHTML = html;
}


function afficherSujet() {
    var sujet = tousMessages.filter(function(m) { return m.id === sujetId; })[0];
    var div = document.getElementById('detail-sujet');
    if (!sujet) { div.innerHTML = '<p class="text-muted">Ce sujet n\'existe plus.</p>'; return; }

    var badges = '<span class="badge bg-info text-dark ms-2">' + echapper(sujet.categorie || 'Général') + '</span>';
    if (sujet.epingle === 1) badges += '<span class="badge bg-primary ms-1">Épinglé</span>';

    var html = '<h5 style="color:var(--primary-green);">' + echapper(sujet.titre || '(sans titre)') + badges + '</h5>';
    html += '<div class="card mb-3 p-3"><strong>' + echapper(sujet.auteur) + '</strong>' +
        '<span class="text-muted small ms-2">' + formatDate(sujet.date) + '</span><br>' +
        '<span>' + echapper(sujet.contenu) + '</span></div>';

    var reponses = tousMessages.filter(function(r) { return r.id_message_parent === sujetId; });
    if (reponses.length === 0) html += '<p class="text-muted small">Aucune réponse pour le moment.</p>';
    else reponses.forEach(function(r) {
        var masque = r.est_modere === 1 ? '<span class="badge bg-danger ms-2">Masqué</span>' : '';
        var btnMod = r.est_modere === 1
            ? '<button class="btn btn-outline-success btn-sm me-1" onclick="changerStatut(' + r.id + ', 0, 0)">Réactiver</button>'
            : '<button class="btn btn-warning btn-sm me-1" onclick="changerStatut(' + r.id + ', 1, 0)">Masquer</button>';
        html += '<div class="card mb-2 p-2 ms-4 border-start border-3 border-success">' +
            '<div class="d-flex justify-content-between align-items-start"><div><strong>' + echapper(r.auteur) + '</strong>' + masque +
            '<span class="text-muted small ms-2">' + formatDate(r.date) + '</span><br><span>' + echapper(r.contenu) + '</span></div>' +
            '<div>' + btnMod + '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + r.id + ')">Supprimer</button></div></div></div>';
    });

    html += '<div class="card p-3 mt-3">' +
        '<textarea id="reponse" class="form-control mb-2" rows="2" placeholder="Votre réponse..."></textarea>' +
        '<button class="btn btn-primary-upcycle btn-sm" onclick="repondre()">Répondre</button></div>';
    div.innerHTML = html;
}


function creerSujet() {
    var titre = document.getElementById('titre').value.trim();
    var contenu = document.getElementById('contenu').value.trim();
    var categorie = document.getElementById('categorie').value;
    if (!titre || !contenu) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1 mt-2">Le titre et le message sont obligatoires.</div>';
        return;
    }
    fetch('/api/messages', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contenu: contenu, id_user: userId, id_message_parent: 0, categorie: categorie, titre: titre })
    }).then(function(res) { if (res.ok) window.location.href = 'forum.php'; })
      .catch(function() { alert('Connexion impossible, réessayez.'); });
}

function repondre() {
    var contenu = document.getElementById('reponse').value.trim();
    if (!contenu) { alert('La réponse ne peut pas être vide.'); return; }
    fetch('/api/messages', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contenu: contenu, id_user: userId, id_message_parent: sujetId })
    }).then(function(res) { if (res.ok) window.location.reload(); })
      .catch(function() { alert('Connexion impossible, réessayez.'); });
}

function changerStatut(id, nouvelEtat, epingleActuel) {
    var action = nouvelEtat === 1 ? 'masquer' : 'réactiver';
    if (confirm('Voulez-vous ' + action + ' ce message ?')) {
        fetch('/api/messages/' + id, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ est_modere: nouvelEtat, epingle: epingleActuel })
        }).then(function(res) { if (res.ok) charger(); }).catch(function() { alert('Connexion impossible.'); });
    }
}
function epingler(id, nouvelEtat, modereActuel) {
    fetch('/api/messages/' + id, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ est_modere: modereActuel, epingle: nouvelEtat })
    }).then(function(res) { if (res.ok) charger(); }).catch(function() { alert('Connexion impossible.'); });
}
function supprimer(id) {
    if (confirm('Supprimer définitivement ce message (et ses réponses) ?')) {
        fetch('/api/messages/' + id, { method: 'DELETE' })
            .then(function(res) { if (res.ok) { if (id === sujetId) window.location.href = 'forum.php'; else charger(); } })
            .catch(function() { alert('Connexion impossible.'); });
    }
}
function filtrer(type) { filtreActuel = type; afficherListe(); }

charger();
</script>
</body>
</html>
