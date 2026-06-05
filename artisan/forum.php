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
    <h4 class="mt-3" style="color:var(--primary-green);">Forum de la communauté</h4>
    <p class="text-muted small">Échangez avec les autres membres autour de l'upcycling.</p>

    <div class="card mb-4 p-3 shadow-sm border-0">
        <h6>Poster un message</h6>
        <textarea id="contenu" class="form-control mb-2" rows="3" placeholder="Partagez vos astuces, posez vos questions..."></textarea>
        <button class="btn btn-primary-upcycle btn-sm" onclick="poster(0)">Envoyer</button>
        <div id="msg"></div>
    </div>

    <div id="messages"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var tousMessages = [];

function charger() {
    fetch('http://localhost:8080/api/messages')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tousMessages = (data || []).filter(function(m) { return m.est_modere === 0; });
            afficher();
        });
}

function afficher() {
    var div = document.getElementById('messages');
    var principaux = tousMessages.filter(function(m) { return m.id_message_parent === 0; });

    if (principaux.length === 0) {
        div.innerHTML = '<p class="text-muted">Aucun message pour le moment. Soyez le premier !</p>';
        return;
    }

    var html = '';
    principaux.forEach(function(m) {
        html += afficherMessage(m, false);
        var reponses = tousMessages.filter(function(r) { return r.id_message_parent === m.id; });
        reponses.forEach(function(r) {
            html += afficherMessage(r, true);
        });
    });

    div.innerHTML = html;
}

function afficherMessage(m, estReponse) {
    var btnSupprimer = m.id_user === userId
        ? '<button class="btn btn-link btn-sm text-danger p-0" onclick="supprimer(' + m.id + ')">Supprimer</button>'
        : '';

    var btnRepondre = !estReponse
        ? '<button class="btn btn-link btn-sm p-0 me-2" style="color:var(--primary-green);" onclick="toggleReponse(' + m.id + ')">Répondre</button>'
        : '';

    var styleCard = estReponse
        ? 'card mb-2 p-2 ms-5 border-start border-3 border-success'
        : 'card mb-2 p-3';

    var html = '<div class="' + styleCard + '">' +
        '<div class="d-flex justify-content-between align-items-start">' +
        '<div>' +
        '<strong style="color:var(--primary-green);">' + m.auteur + '</strong>' +
        '<span class="text-muted small ms-2">' + (m.date ? m.date.replace('T', ' ').substring(0, 16) : '') + '</span><br>' +
        '<span>' + m.contenu + '</span>' +
        '</div>' +
        '<div>' + btnRepondre + btnSupprimer + '</div>' +
        '</div>';

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
            if (idParent === 0) {
                document.getElementById('contenu').value = '';
            }
            charger();
        }
    });
}

function supprimer(id) {
    if (confirm('Supprimer ce message ?')) {
        fetch('http://localhost:8080/api/messages/' + id, { method: 'DELETE' })
            .then(function(res) { if (res.ok) charger(); });
    }
}

charger();
</script>

</body>
</html>