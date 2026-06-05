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
var userId = <?php echo $_SESSION['user_id']; ?>;
var projetEnCours = null;
var modalCtrl = new bootstrap.Modal(document.getElementById('modalEtape'));

// Charger mes projets
function chargerProjets() {
    fetch('http://localhost:8080/api/projets?id_createur=' + userId)
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
                html += '<div class="card mb-4">' +
                    '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h5 style="color:var(--primary-green);">' + p.titre + '</h5>' +
                    '<p class="text-muted small">' + p.description + '</p>' +
                    '</div>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="supprimerProjet(' + p.id + ')">Supprimer</button>' +
                    '</div>' +
                    '<button class="btn btn-sm btn-primary-upcycle mb-3" onclick="ouvrirEtape(' + p.id + ')">+ Ajouter une étape</button>' +
                    '<div id="etapes-' + p.id + '"></div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;

            // Charger les étapes de chaque projet
            data.forEach(function(p) { chargerEtapes(p.id); });
        });
}

// Charger les étapes d'un projet
function chargerEtapes(idProjet) {
    fetch('http://localhost:8080/api/etapes?id_projet=' + idProjet)
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
                    ? '<img src="http://localhost/' + e.image + '" class="card-img-top" style="height:140px; object-fit:cover;">'
                    : '<div class="d-flex align-items-center justify-content-center" style="height:140px; background-color:#f0f7f0;"><span class="text-muted small">Pas de photo</span></div>';

                html += '<div class="col-md-4">' +
                    '<div class="card h-100">' +
                    photo +
                    '<div class="card-body p-2">' +
                    '<span class="badge bg-success">Étape ' + e.ordre + '</span> ' +
                    '<strong>' + e.titre + '</strong>' +
                    '<p class="small text-muted mb-1 mt-1">' + e.description + '</p>' +
                    '<button class="btn btn-sm btn-outline-danger w-100" onclick="supprimerEtape(' + e.id + ', ' + idProjet + ')">Supprimer</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });
            html += '</div>';

            div.innerHTML = html;
        });
}

// Créer un projet
function creerProjet() {
    var titre = document.getElementById('titre-projet').value.trim();
    var desc = document.getElementById('desc-projet').value.trim();

    if (!titre) {
        document.getElementById('msg-projet').innerHTML = '<div class="alert alert-danger py-1">Donnez un titre à votre création.</div>';
        return;
    }

    fetch('http://localhost:8080/api/projets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titre: titre, description: desc, id_createur: userId })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('titre-projet').value = '';
            document.getElementById('desc-projet').value = '';
            document.getElementById('msg-projet').innerHTML = '<div class="alert alert-success py-1">Création ajoutée !</div>';
            chargerProjets();
            setTimeout(function() { document.getElementById('msg-projet').innerHTML = ''; }, 2000);
        }
    });
}

// Supprimer un projet
function supprimerProjet(id) {
    if (!confirm("Supprimer cette création et toutes ses étapes ?")) return;
    fetch('http://localhost:8080/api/projets/' + id, { method: 'DELETE' })
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

    // Si une photo est sélectionnée, on l'upload d'abord
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
    fetch('http://localhost:8080/api/etapes', {
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
    fetch('http://localhost:8080/api/etapes/' + id, { method: 'DELETE' })
        .then(function(res) { if (res.ok) chargerEtapes(idProjet); });
}

chargerProjets();
</script>

</body>
</html>