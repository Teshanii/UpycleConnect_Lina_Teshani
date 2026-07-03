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
    <title>Mes créations | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mes créations</h4>
    <p class="text-muted small">Documentez vos transformations avec des photos avant/après pour valoriser votre travail.</p>

    <!-- Formulaire nouveau projet -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 style="color:var(--primary-green);">Nouvelle création</h5>
            <div id="msg-projet"></div>
            <div class="mb-2">
                <input type="text" class="form-control" id="titre-projet" placeholder="Titre (ex: Palette transformée en table basse)">
            </div>
            <div class="mb-2">
                <textarea class="form-control" id="desc-projet" rows="2" placeholder="Description générale du projet"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control" id="adresse-projet" placeholder="Adresse (optionnel)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control" id="ville-projet" placeholder="Ville (ex: Paris 11)">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label small text-muted">Date de début</label>
                    <input type="datetime-local" class="form-control" id="debut-projet" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small text-muted">Date de fin (estimée)</label>
                    <input type="datetime-local" class="form-control" id="fin-projet" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small text-muted">Photo de couverture (optionnel)</label>
                <input type="file" class="form-control" id="couverture-projet" accept="image/*">
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="ouvert-projet" checked>
                <label class="form-check-label small" for="ouvert-projet">Ouvrir le projet aux participants de la communauté</label>
            </div>
            <button class="btn btn-primary-upcycle w-100" onclick="creerProjet()">CRÉER LA CRÉATION</button>
        </div>
    </div>

    <!-- Liste des projets -->
    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>
    <div id="projets"></div>
</div>

<!-- Modal pour ajouter une étape -->
<div class="modal fade" id="modalEtape" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une étape</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="msg-etape"></div>
                <div class="mb-2">
                    <label class="form-label">Titre de l'étape (ex: Avant, Démontage, Après...)</label>
                    <input type="text" class="form-control" id="titre-etape">
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="desc-etape" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Ordre (1, 2, 3...)</label>
                    <input type="number" class="form-control" id="ordre-etape" value="1">
                </div>
                <div class="mb-2">
                    <label class="form-label">Photo</label>
                    <input type="file" class="form-control" id="photo-etape" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="enregistrerEtape()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function echapper(t) {
    if (t === null || t === undefined) return "";
    return String(t).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");
}
var userId = <?php echo $_SESSION['user_id']; ?>;
var projetEnCours = null;
var modalCtrl = new bootstrap.Modal(document.getElementById('modalEtape'));



// Charger mes projets
function chargerProjets() {
    fetch('/api/projets?id_createur=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('loader').style.display = 'none';
            var div = document.getElementById('projets');

            if (!data || data.length === 0) {
                div.innerHTML = '<p class="text-muted">Aucune création pour l\'instant. Créez-en une au-dessus !</p>';
                return;
            }

            var html = '';
            data.forEach(function(p) {
                // Badge de statut
                var badgeStatut = p.statut === 'termine'
                    ? '<span class="badge bg-success">Terminé</span>'
                    : '<span class="badge bg-secondary">En cours</span>';

                // Bouton terminer (seulement si pas déjà terminé)
                var btnTerminer = p.statut === 'termine'
                    ? ''
                    : '<button class="btn btn-sm btn-success mb-3 ms-1" onclick="terminerProjet(' + p.id + ')">Marquer terminé</button>';

                var imgCouverture = p.photo_couverture
                    ? '<img src="/' + p.photo_couverture + '" class="card-img-top" style="width:100%; height:180px; object-fit:cover;">'
                    : '';

                html += '<div class="card mb-4">' +
                    imgCouverture +
                    '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h5 style="color:var(--primary-green);">' + echapper(p.titre) + ' ' + badgeStatut + '</h5>' +
                    '<p class="text-muted small">' + echapper(p.description) + '</p>' +
                    (p.ville ? '<p class="text-muted small"> ' + echapper(p.ville) + '</p>' : '') +
                    ((p.date_debut && p.date_fin) ? '<p class="text-muted small">Du ' + p.date_debut.substring(0,10) + ' au ' + p.date_fin.substring(0,10) + '</p>' : '') +
                    '</div>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="supprimerProjet(' + p.id + ')">Supprimer</button>' +
                    '</div>' +
                    '<button class="btn btn-sm btn-primary-upcycle mb-3" onclick="ouvrirEtape(' + p.id + ')">+ Ajouter une étape</button>' +
                    btnTerminer +
                    '<a href="../projet_detail.php?id=' + p.id + '" target="_blank" class="btn btn-sm btn-outline-secondary mb-3 ms-1">Voir la page publique</a>' +
                    (p.est_sponsorise == 1
                        ? '<span class="badge bg-warning text-dark mb-3 ms-1">⭐ Déjà mis en avant</span>'
                        : '<button class="btn btn-sm btn-warning mb-3 ms-1" onclick="sponsoriser(' + p.id + ')">⭐ Mettre en avant (100€)</button>') +
                    '<div id="etapes-' + p.id + '"></div>' +
                    '<div id="participants-' + p.id + '" class="mt-3"></div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;

            // Charger les étapes et les demandes de participation de chaque projet
            data.forEach(function(p) {
                chargerEtapes(p.id);
                chargerParticipants(p.id);
            });
        });
}
// Mettre en avant un projet (paiement Stripe)
function sponsoriser(idProjet) {
    if (!confirm("Mettre ce projet en avant pendant 30 jours pour 100€ ?")) return;
    fetch('stripe_sponsoring.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prix: 10000, id_projet: idProjet }) // 10000 centimes = 100€
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.url) {
            window.location.href = data.url; // redirection vers Stripe
        } else {
            alert("Erreur lors de la création du paiement.");
        }
    });
}

// Charger les étapes d'un projet
function chargerEtapes(idProjet) {
    fetch('/api/etapes?id_projet=' + idProjet)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('etapes-' + idProjet);
            if (!data || data.length === 0) {
                div.innerHTML = '<p class="text-muted small">Aucune étape ajoutée.</p>';
                return;
            }

            var html = '<div class="row g-2">';
            data.forEach(function(e) {
                var photo = e.image
                    ? '<img src="/' + e.image + '" class="card-img-top" style="height:140px; object-fit:cover;">'
                    : '<div class="d-flex align-items-center justify-content-center" style="height:140px; background-color:#f0f7f0;"><span class="text-muted small">Pas de photo</span></div>';

                html += '<div class="col-md-4">' +
                    '<div class="card h-100">' +
                    photo +
                    '<div class="card-body p-2">' +
                    '<span class="badge bg-success">Étape ' + e.ordre + '</span> ' +
                    '<strong>' + echapper(e.titre) + '</strong>' +
                    '<p class="small text-muted mb-1 mt-1">' + echapper(e.description) + '</p>' +
                    '<button class="btn btn-sm btn-outline-danger w-100" onclick="supprimerEtape(' + e.id + ', ' + idProjet + ')">Supprimer</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });
            html += '</div>';

            div.innerHTML = html;
        });
}

// Charger les demandes de participation d'un projet
function chargerParticipants(idProjet) {
    fetch('/api/participants?id_projet=' + idProjet)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('participants-' + idProjet);
            if (!data || data.length === 0) {
                div.innerHTML = '';
                return;
            }

            var html = '<h6 class="text-muted small mt-2">Demandes de participation :</h6><ul class="list-group">';
            data.forEach(function(p) {
                if (p.statut === 'en_attente') {
                    // Demande en attente → boutons accepter/refuser
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><strong>' + echapper(p.nom_user) + '</strong>' + (p.tache ? ' — ' + echapper(p.tache) : '') + '</span>' +
                        '<span>' +
                        '<button class="btn btn-sm btn-success me-1" onclick="repondreParticipant(' + p.id + ', \'accepte\', \'' + (p.tache || '') + '\', ' + idProjet + ')">Accepter</button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="repondreParticipant(' + p.id + ', \'refuse\', \'\', ' + idProjet + ')">Refuser</button>' +
                        '</span>' +
                        '</li>';
                } else if (p.statut === 'accepte') {
                    html += '<li class="list-group-item"><span class="badge bg-success">Accepté</span> <strong>' + echapper(p.nom_user) + '</strong>' + (p.tache ? ' — ' + echapper(p.tache) : '') + '</li>';
                }
            });
            html += '</ul>';
            div.innerHTML = html;
        });
}

// Accepter ou refuser une demande de participation
function repondreParticipant(idParticipation, statut, tache, idProjet) {
    fetch('/api/participants/' + idParticipation, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ statut: statut, tache: tache })
    }).then(function(res) {
        if (res.ok) chargerParticipants(idProjet);
    });
}

// Créer un projet (avec upload de la photo de couverture)
function creerProjet() {
    var titre = document.getElementById('titre-projet').value.trim();
    var desc = document.getElementById('desc-projet').value.trim();
    var adresse = document.getElementById('adresse-projet').value.trim();
    var ville = document.getElementById('ville-projet').value.trim();
    var ouvert = document.getElementById('ouvert-projet').checked ? 1 : 0;
    var debut = document.getElementById('debut-projet').value;
    var fin = document.getElementById('fin-projet').value;
    var fichier = document.getElementById('couverture-projet').files[0];

    if (!titre) {
        document.getElementById('msg-projet').innerHTML = '<div class="alert alert-danger py-1">Donnez un titre à votre création.</div>';
        return;
    }
    if (!debut || !fin) {
        document.getElementById('msg-projet').innerHTML = '<div class="alert alert-danger py-1">Indiquez une date de début et de fin.</div>';
        return;
    }
    if (fin < debut) {
        document.getElementById('msg-projet').innerHTML = '<div class="alert alert-danger py-1">La date de fin doit être après la date de début.</div>';
        return;
    }

    // Si une photo de couverture est choisie, on l'upload d'abord
    if (fichier) {
        var formData = new FormData();
        formData.append('photo', fichier);
        fetch('../upload_photo.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                envoyerProjet(titre, desc, adresse, ville, ouvert, data.chemin || '', debut, fin);
            });
    } else {
        envoyerProjet(titre, desc, adresse, ville, ouvert, '', debut, fin);
    }
}

function envoyerProjet(titre, desc, adresse, ville, ouvert, photoCouverture, debut, fin) {
    fetch('/api/projets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            titre: titre,
            description: desc,
            adresse: adresse,
            ville: ville,
            ouvert_participation: ouvert,
            photo_couverture: photoCouverture,
            id_createur: userId,
            date_debut: debut.replace('T', ' '), // format MySQL (2026-07-05 14:00)
            date_fin: fin.replace('T', ' ')
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('titre-projet').value = '';
            document.getElementById('desc-projet').value = '';
            document.getElementById('adresse-projet').value = '';
            document.getElementById('ville-projet').value = '';
            document.getElementById('debut-projet').value = '';
            document.getElementById('fin-projet').value = '';
            document.getElementById('couverture-projet').value = '';
            document.getElementById('msg-projet').innerHTML = '<div class="alert alert-success py-1">Création ajoutée !</div>';
            chargerProjets();
            setTimeout(function() { document.getElementById('msg-projet').innerHTML = ''; }, 2000);
        }
    });
}

// Marquer un projet comme terminé
function terminerProjet(id) {
    if (!confirm("Marquer ce projet comme terminé ?")) return;
    // On récupère d'abord le projet pour garder ses infos, puis on change juste le statut
    fetch('/api/projets?id_createur=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var p = data.find(function(x) { return x.id === id; });
            if (!p) return;
            fetch('/api/projets/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    titre: p.titre,
                    description: p.description,
                    adresse: p.adresse,
                    ville: p.ville,
                    statut: 'termine',
                    photo_couverture: p.photo_couverture,
                    ouvert_participation: p.ouvert_participation
                })
            }).then(function(res) {
                if (res.ok) chargerProjets();
            });
        });
}

// Supprimer un projet
function supprimerProjet(id) {
    if (!confirm("Supprimer cette création et toutes ses étapes ?")) return;
    fetch('/api/projets/' + id, { method: 'DELETE' })
        .then(function(res) { if (res.ok) chargerProjets(); });
}

// Ouvrir le modal pour ajouter une étape
function ouvrirEtape(idProjet) {
    projetEnCours = idProjet;
    document.getElementById('titre-etape').value = '';
    document.getElementById('desc-etape').value = '';
    document.getElementById('ordre-etape').value = '1';
    document.getElementById('photo-etape').value = '';
    document.getElementById('msg-etape').innerHTML = '';
    modalCtrl.show();
}

// Enregistrer une étape (upload photo + appel API)
function enregistrerEtape() {
    var titre = document.getElementById('titre-etape').value.trim();
    var desc = document.getElementById('desc-etape').value.trim();
    var ordre = parseInt(document.getElementById('ordre-etape').value) || 1;
    var fichier = document.getElementById('photo-etape').files[0];

    if (!titre) {
        document.getElementById('msg-etape').innerHTML = '<div class="alert alert-danger py-1">Donnez un titre à l\'étape.</div>';
        return;
    }

    if (fichier) {
        var formData = new FormData();
        formData.append('photo', fichier);
        fetch('../upload_photo.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                envoyerEtape(titre, desc, ordre, data.chemin || '');
            });
    } else {
        envoyerEtape(titre, desc, ordre, '');
    }
}

function envoyerEtape(titre, desc, ordre, image) {
    fetch('/api/etapes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            titre: titre,
            description: desc,
            ordre: ordre,
            image: image,
            id_projet: projetEnCours
        })
    }).then(function(res) {
        if (res.ok) {
            modalCtrl.hide();
            chargerEtapes(projetEnCours);
        }
    });
}

// Supprimer une étape
function supprimerEtape(id, idProjet) {
    if (!confirm("Supprimer cette étape ?")) return;
    fetch('/api/etapes/' + id, { method: 'DELETE' })
        .then(function(res) { if (res.ok) chargerEtapes(idProjet); });
}

chargerProjets();
</script>

</body>
</html>