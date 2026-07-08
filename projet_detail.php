<?php
session_start();

$estConnecte = isset($_SESSION['user_id']);
$userId = $estConnecte ? $_SESSION['user_id'] : 0;


$idProjet = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idProjet === 0) {
    header('Location: galerie.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du projet | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">UpcycleConnect</a>
        <div>
            <a href="galerie.php" class="btn btn-outline-light btn-sm me-2">← Galerie</a>
            <?php if ($estConnecte) { ?>
                <a href="connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
            <?php } else { ?>
                <a href="connexion.php" class="btn btn-outline-light btn-sm">Connexion</a>
            <?php } ?>
        </div>
    </div>
</nav>

<div class="container mt-4" style="max-width:800px;">

    <!-- En-tête du projet (rempli en JS) -->
    <div id="entete-projet">
        <p class="text-muted">Chargement...</p>
    </div>

    <!-- Timeline des étapes -->
    <h5 class="mt-4" style="color:var(--primary-green);">Les étapes de la transformation</h5>
    <div id="zone-etapes">
        <p class="text-muted">Chargement des étapes...</p>
    </div>

    <h5 class="mt-4" style="color:var(--primary-green);">Les participants</h5>
    <div id="zone-participants">
        <p class="text-muted">Chargement...</p>
    </div>

   
    <div class="my-4" id="zone-bouton-participer"></div>

    
    <div id="msg"></div>

</div>

<script>
function echapper(t) {
    if (t === null || t === undefined) return "";
    return String(t).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");
}
var idProjet = <?php echo $idProjet; ?>;
var estConnecte = <?php echo $estConnecte ? 'true' : 'false'; ?>;
var userId = <?php echo $userId; ?>;
var projetCourant = null; 


fetch('/api/projets')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var projet = (data || []).find(function(p) { return p.id === idProjet; });
        if (!projet) {
            document.getElementById('entete-projet').innerHTML = '<div class="alert alert-warning">Projet introuvable.</div>';
            return;
        }
        projetCourant = projet;
        afficherEntete(projet);
        afficherBoutonParticiper(projet);
    });

function afficherEntete(p) {
    var photo = p.photo_couverture ? '/' + p.photo_couverture : 'https://via.placeholder.com/800x350?text=Projet';

    var badgeStatut = p.statut === 'termine'
        ? '<span class="badge bg-success">Terminé</span>'
        : '<span class="badge bg-secondary">En cours</span>';

    var badgeSponsor = p.est_sponsorise == 1
        ? '<span class="badge bg-warning text-dark"> Sponsorisé</span> '
        : '';

    var html = '';
    html += '<img src="' + photo + '" class="w-100 mb-3" style="height:300px; object-fit:cover; border-radius:10px;">';
    html += '<div class="mb-2">' + badgeSponsor + badgeStatut + '</div>';
    html += '<h3 style="color:var(--primary-green);">' + echapper(p.titre) + '</h3>';
    html += '<p class="text-muted mb-1">Créé par <strong>' + echapper(p.createur) + '</strong></p>';
    if (p.ville || p.adresse) {
        html += '<p class="text-muted mb-2"> ' + (p.adresse ? echapper(p.adresse) + ', ' : '') + echapper(p.ville || '') + '</p>';
    }
    if (p.date_debut && p.date_fin) {
        html += '<p class="text-muted mb-2">Période : du ' + p.date_debut.substring(0,10) + ' au ' + p.date_fin.substring(0,10) + '</p>';
    }
    html += '<p>' + echapper(p.description || '') + '</p>';
    document.getElementById('entete-projet').innerHTML = html;
}

fetch('/api/etapes?id_projet=' + idProjet)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var zone = document.getElementById('zone-etapes');
        if (!data || data.length === 0) {
            zone.innerHTML = '<p class="text-muted">Aucune étape pour ce projet.</p>';
            return;
        }
        var html = '';
        data.forEach(function(e, index) {
            var img = e.image ? '/' + e.image : '';
            html += '<div class="card mb-3 shadow-sm">';
            html += '  <div class="card-body">';
            html += '    <h6 style="color:var(--primary-green);">Étape ' + (index + 1) + ' : ' + echapper(e.titre || '') + '</h6>';
            if (img) {
                html += '    <img src="' + img + '" class="w-100 mb-2" style="max-height:350px; object-fit:cover; border-radius:8px;">';
            }
            html += '    <p class="mb-0">' + echapper(e.description || '') + '</p>';
            html += '  </div>';
            html += '</div>';
        });
        zone.innerHTML = html;
    });


fetch('/api/participants?id_projet=' + idProjet)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var zone = document.getElementById('zone-participants');
        var acceptes = (data || []).filter(function(p) { return p.statut === 'accepte'; });
        if (acceptes.length === 0) {
            zone.innerHTML = '<p class="text-muted">Aucun participant pour le moment.</p>';
            return;
        }
        var html = '<ul class="list-group">';
        acceptes.forEach(function(p) {
            html += '<li class="list-group-item">';
            html += '<strong>' + echapper(p.nom_user) + '</strong>';
            if (p.tache) {
                html += '— <span class="text-muted">' + echapper(p.tache) + '</span>';
            }
            html += '</li>';
        });
        html += '</ul>';
        zone.innerHTML = html;
    });

function afficherBoutonParticiper(p) {
    var zone = document.getElementById('zone-bouton-participer');

    // Si le projet n'accepte pas les participations ou est terminé
    if (p.ouvert_participation != 1 || p.statut === 'termine') {
        zone.innerHTML = '<div class="alert alert-secondary">Ce projet n\'accepte pas de nouveaux participants.</div>';
        return;
    }

    
    if (!estConnecte) {
        zone.innerHTML =
            '<div class="card p-3 text-center" style="background:var(--bg-light);">' +
            '<p class="mb-2">Vous voulez participer à ce projet ?</p>' +
            '<div>' +
            '<a href="connexion.php" class="btn btn-primary-upcycle me-2">Se connecter</a>' +
            '<a href="inscription.php" class="btn btn-outline-secondary">S\'inscrire</a>' +
            '</div></div>';
        return;
    }

    

    if (p.id_createur === userId) {
        zone.innerHTML = '<div class="alert alert-info">Vous êtes le créateur de ce projet.</div>';
        return;
    }

    // Sinon : utilisateur connecté qui peut demander à participer
    zone.innerHTML =
        '<div class="card p-3">' +
        '<label class="form-label">Comment voulez-vous aider ? (optionnel)</label>' +
        '<input type="text" id="tache" class="form-control mb-2" placeholder="Ex: je peux poncer le bois, peindre...">' +
        '<button class="btn btn-primary-upcycle" onclick="participer()">Demander à participer</button>' +
        '</div>';
}

function participer() {
    var tache = document.getElementById('tache').value;
    fetch('/api/participants', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_projet: idProjet,
            id_user: userId,
            tache: tache
        })
   }).then(function(res) {
        if (res.ok) {
            document.getElementById('zone-bouton-participer').innerHTML =
                '<div class="alert alert-success">Votre demande de participation a été envoyée ! Le créateur du projet doit la valider.</div>';
        } else if (res.status === 409) {
            document.getElementById('zone-bouton-participer').innerHTML =
                '<div class="alert alert-warning">Vous avez déjà demandé à participer à ce projet.</div>';
        } else {
            document.getElementById('msg').innerHTML =
                '<div class="alert alert-danger">Une erreur est survenue.</div>';
        }
    });
}
</script>

</body>
</html>