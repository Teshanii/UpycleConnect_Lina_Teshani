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
    <title>Mes prestations | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mes prestations</h4>
    <p class="text-muted small">Proposez vos créations à vendre ou à donner. Chaque prestation doit être validée par un administrateur avant d'être visible.</p>

    <!-- Formulaire nouvelle prestation -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 style="color:var(--primary-green);">Nouvelle prestation</h5>
            <div id="msg-form"></div>

            <div class="mb-2">
                <label class="form-label">Nom de la prestation</label>
                <input type="text" class="form-control" id="nom" placeholder="Ex: Table basse en palette upcyclée">
            </div>
            <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="description" rows="2"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label">Prix (€)</label>
                <input type="number" step="0.01" class="form-control" id="prix" placeholder="0 pour donner gratuitement">
            </div>
            <div class="mb-2">
                <label class="form-label">Photo</label>
                <input type="file" class="form-control" id="photo" accept="image/*">
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="ajouter()">PUBLIER LA PRESTATION</button>
        </div>
    </div>

    <!-- Liste des prestations -->
    <h5 style="color:var(--primary-green);">Mes publications</h5>
    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>
    <div id="liste" class="row g-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

function charger() {
    fetch('/api/prestations?id_createur=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('loader').style.display = 'none';
            var div = document.getElementById('liste');

            if (!data || data.length === 0) {
                div.innerHTML = '<p class="text-muted">Aucune prestation publiée pour l\'instant.</p>';
                return;
            }

            var html = '';
            data.forEach(function(p) {
                
                var badge;
                if (p.statut_validation === 1) {
                    badge = '<span class="badge bg-success">Validée</span>';
                } else if (p.statut_validation === 2) {
                    badge = '<span class="badge bg-danger">Refusée</span>';
                } else {
                    badge = '<span class="badge bg-warning text-dark">En attente</span>';
                }

                
                var badgeVendu = p.vendu === 1
                    ? '<span class="badge bg-dark">Vendue</span>'
                    : '';

                
                var photo = p.photo
                    ? '<img src="/' + p.photo + '" class="card-img-top" style="height:180px; object-fit:cover;">'
                    : '<div class="d-flex align-items-center justify-content-center" style="height:180px; background-color:#f0f7f0;"><span class="text-muted">Pas de photo</span></div>';

                
                var badgePrix = p.prix > 0
                    ? '<span class="badge bg-warning text-dark">' + p.prix.toFixed(2) + ' €</span>'
                    : '<span class="badge bg-success">Gratuit</span>';

                
                var motif = '';
                if (p.statut_validation === 2 && p.motif_refus) {
                    motif = '<p class="text-danger small mt-1">Motif : ' + p.motif_refus + '</p>';
                }

                
                var boutonSupprimer = p.vendu === 1
                    ? '<p class="text-muted small mt-2 mb-0 text-center">Prestation vendue</p>'
                    : '<button class="btn btn-sm btn-outline-danger w-100 mt-2" onclick="supprimer(' + p.id + ')">Supprimer</button>';

                html += '<div class="col-md-4">' +
                    '<div class="card h-100">' +
                    photo +
                    '<div class="card-body">' +
                    '<h6 class="card-title">' + p.nom + '</h6>' +
                    '<div class="mb-2">' + badgePrix + ' ' + badge + ' ' + badgeVendu + '</div>' +
                    '<p class="card-text small text-muted">' + (p.desc || '') + '</p>' +
                    motif +
                    boutonSupprimer +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;
        });
}

function ajouter() {
    var nom = document.getElementById('nom').value.trim();
    var desc = document.getElementById('description').value.trim();
    var prix = parseFloat(document.getElementById('prix').value) || 0;
    var fichier = document.getElementById('photo').files[0];

    if (!nom) {
        document.getElementById('msg-form').innerHTML = '<div class="alert alert-danger py-1">Donnez un nom à la prestation.</div>';
        return;
    }

    
    if (fichier) {
        var formData = new FormData();
        formData.append('photo', fichier);
        fetch('../upload_photo.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                envoyer(nom, desc, prix, data.chemin || '');
            });
    } else {
        envoyer(nom, desc, prix, '');
    }
}

function envoyer(nom, desc, prix, photo) {
    fetch('/api/prestations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: nom,
            desc: desc,
            prix: prix,
            photo: photo,
            id_createur: userId
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg-form').innerHTML = '<div class="alert alert-success py-1">Prestation publiée ! En attente de validation par l\'admin.</div>';
            document.getElementById('nom').value = '';
            document.getElementById('description').value = '';
            document.getElementById('prix').value = '';
            document.getElementById('photo').value = '';
            charger();
            setTimeout(function() { document.getElementById('msg-form').innerHTML = ''; }, 3000);
        }
    });
}

function supprimer(id) {
    if (!confirm("Supprimer cette prestation ?")) return;
    fetch('/api/prestations/' + id, { method: 'DELETE' })
        .then(function(res) { if (res.ok) charger(); });
}

charger();
</script>

</body>
</html>