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

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Forum — Animation & Modération</h4>
    <p class="text-muted small">Participez aux discussions et modérez les messages inappropriés.</p>

    <!-- Formulaire de post principal -->
    <div class="card mb-4 p-3 shadow-sm border-0">
        <h6>Poster un message</h6>
        <textarea id="contenu" class="form-control mb-2" rows="3" placeholder="Animez la communauté, répondez aux questions..."></textarea>
        <button class="btn btn-primary-upcycle btn-sm" onclick="poster(0)">Envoyer</button>
        <div id="msg"></div>
    </div>

    <!-- Filtres de modération -->
    <div class="mb-3">
        <button class="btn btn-success btn-sm me-2" onclick="filtrer('visible')">Visibles</button>
        <button class="btn btn-warning btn-sm me-2" onclick="filtrer('modere')">Masqués</button>
        <button class="btn btn-secondary btn-sm" onclick="filtrer('tous')">Tous</button>
    </div>

    <!-- Liste des messages -->
    <div id="messages"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var tousMessages = [];
var filtreActuel = 'visible';

// Échappe le HTML pour éviter les injections de code (XSS)
function echapper(t) {
    if (t === null || t === undefined) return '';
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}


// Charger tous les messages (le salarié voit aussi les masqués)
function charger() {
    fetch('http://localhost:8080/api/messages')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tousMessages = data || [];
            afficher();
        });
}

function afficher() {
    var div = document.getElementById('messages');

    // Appliquer le filtre choisi pour les messages PRINCIPAUX
    var principaux = tousMessages.filter(function(m) { return m.id_message_parent === 0; });

    if (filtreActuel === 'visible') {
        principaux = principaux.filter(function(m) { return m.est_modere === 0; });
    } else if (filtreActuel === 'modere') {
        principaux = principaux.filter(function(m) { return m.est_modere === 1; });
    }

    if (principaux.length === 0) {
        div.innerHTML = '<p class="text-muted">Aucun message à afficher.</p>';
        return;
    }

    var html = '';
    principaux.forEach(function(m) {
        html += afficherMessage(m, false);

        // Afficher les réponses sous le message
        var reponses = tousMessages.filter(function(r) { return r.id_message_parent === m.id; });
        reponses.forEach(function(r) {
            html += afficherMessage(r, true);
        });
    });

    div.innerHTML = html;
}

function afficherMessage(m, estReponse) {
    var estMasque = m.est_modere === 1;

    // Boutons de modération (le salarié les a sur TOUS les messages)
    var btnModere = estMasque
        ? '<button class="btn btn-outline-success btn-sm me-1" onclick="changerStatut(' + m.id + ', 0)">Réactiver</button>'
        : '<button class="btn btn-warning btn-sm me-1" onclick="changerStatut(' + m.id + ', 1)">Masquer</button>';

    var btnSupprimer = '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + m.id + ')">Supprimer</button>';

    
    var idThread = estReponse ? m.id_message_parent : m.id;
    var btnRepondre = '<button class="btn btn-link btn-sm p-0 me-2" style="color:var(--primary-green);" onclick="toggleReponse(' + idThread + ')">Répondre</button>';

    // Badge si masqué
    var badgeMasque = estMasque ? '<span class="badge bg-danger ms-2">Masqué</span>' : '';

    // Indentation pour les réponses
    var styleCard = estReponse
        ? 'card mb-2 p-2 ms-5 border-start border-3 border-success'
        : 'card mb-2 p-3';

    var html = '<div class="' + styleCard + '">' +
        '<div class="d-flex justify-content-between align-items-start">' +
        '<div>' +
        '<strong style="color:var(--primary-green);">' + echapper(m.auteur) + '</strong>' + badgeMasque +
        '<span class="text-muted small ms-2">' + (m.date ? m.date.replace('T', ' ').substring(0, 16) : '') + '</span><br>' +
        '<span>' + echapper(m.contenu) + '</span>' +
        '</div>' +
        '<div>' + btnRepondre + btnModere + btnSupprimer + '</div>' +
        '</div>';

    // Zone de réponse cachée (messages principaux seulement)
    if (!estReponse) {
        html += '<div id="zone-reponse-' + m.id + '" class="mt-2" style="display:none;">' +
            '<textarea id="contenu-reponse-' + m.id + '" class="form-control mb-2" rows="2" placeholder="Votre réponse..."></textarea>' +
            '<button class="btn btn-primary-upcycle btn-sm" onclick="poster(' + m.id + ')">Répondre</button>' +
            '<button class="btn btn-link btn-sm text-muted" onclick="toggleReponse(' + m.id + ')">Annuler</button>' +
            '</div>';
    }

    html += '</div>';
    return html;
}

function toggleReponse(idMessage) {
    var zone = document.getElementById('zone-reponse-' + idMessage);
    zone.style.display = zone.style.display === 'none' ? 'block' : 'none';
}

// Poster — idParent = 0 pour message principal, sinon réponse
function poster(idParent) {
    var contenu;
    if (idParent === 0) {
        contenu = document.getElementById('contenu').value.trim();
    } else {
        contenu = document.getElementById('contenu-reponse-' + idParent).value.trim();
    }

    if (!contenu) {
        alert('Le message ne peut pas être vide.');
        return;
    }

    fetch('http://localhost:8080/api/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contenu: contenu, id_user: userId, id_message_parent: idParent })
    }).then(function(res) {
        if (res.ok) {
            if (idParent === 0) document.getElementById('contenu').value = '';
            charger();
        }
    }).catch(function() { alert('Connexion impossible, réessayez.'); });
}

// Modérer : masquer (1) ou réactiver (0)
function changerStatut(id, nouvelEtat) {
    var action = nouvelEtat === 1 ? 'masquer' : 'réactiver';
    if (confirm('Voulez-vous ' + action + ' ce message ?')) {
        fetch('http://localhost:8080/api/messages/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ est_modere: nouvelEtat })
        }).then(function(res) { if (res.ok) charger(); }).catch(function() { alert('Connexion impossible, réessayez.'); });
    }
}

// Supprimer définitivement
function supprimer(id) {
    if (confirm('Supprimer définitivement ce message (et ses réponses) ?')) {
        fetch('http://localhost:8080/api/messages/' + id, { method: 'DELETE' })
            .then(function(res) { if (res.ok) charger(); })
            .catch(function() { alert('Connexion impossible, réessayez.'); });
    }
}

function filtrer(type) {
    filtreActuel = type;
    afficher();
}

charger();
</script>

</body>
</html>