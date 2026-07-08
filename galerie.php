<?php
session_start();

$estConnecte = isset($_SESSION['user_id']);
$userId = $estConnecte ? $_SESSION['user_id'] : 0;


$monEspace = 'index.php';
if ($estConnecte) {
    switch ($_SESSION['user_role']) {
        case 2: $monEspace = 'salarie/dashboard.php'; break;
        case 3: $monEspace = 'artisan/dashboard.php'; break;
        case 4: $monEspace = 'particulier/dashboard.php'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Galerie des créations | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">UpcycleConnect</a>
        <div>
            <?php if ($estConnecte) { ?>
                <a href="<?= $monEspace ?>" class="btn btn-light btn-sm me-2" style="color:var(--primary-green); font-weight:600;">← Mon espace</a>
                <a href="connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
            <?php } else { ?>
                <a href="connexion.php" class="btn btn-outline-light btn-sm">Connexion</a>
                <a href="inscription.php" class="btn btn-light btn-sm ms-2">Inscription</a>
            <?php } ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);">Galerie des créations de la communauté</h4>
    <p class="text-muted">Découvrez les projets d'upcycling réalisés par nos artisans.</p>

    <!-- Filtre par ville -->
    <div class="mb-4">
        <input type="text" id="filtre-ville" class="form-control" style="max-width:300px;"
               placeholder="Filtrer par ville..." onkeyup="afficherProjets()">
    </div>

    <!-- Zone d'affichage des projets -->
    <div class="row g-4" id="zone-projets">
        <p class="text-muted">Chargement...</p>
    </div>
</div>

<script>
function echapper(t) {
    if (t === null || t === undefined) return "";
    return String(t).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");
}
var estConnecte = <?php echo $estConnecte ? 'true' : 'false'; ?>;
var tousLesProjets = [];


fetch('/api/projets')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tousLesProjets = data || [];
        afficherProjets();
    });

function afficherProjets() {
    var zone = document.getElementById('zone-projets');
    var filtreVille = document.getElementById('filtre-ville').value.toLowerCase();

    // On filtre par ville (si le champ est vide, on garde tout)
    var projets = tousLesProjets.filter(function(p) {
        return (p.ville || '').toLowerCase().indexOf(filtreVille) !== -1;
    });

    if (projets.length === 0) {
        zone.innerHTML = '<p class="text-muted">Aucun projet à afficher.</p>';
        return;
    }

    var html = '';
    projets.forEach(function(p) {
        
        var photo = p.photo_couverture ? '/' + p.photo_couverture : 'https://via.placeholder.com/400x250?text=Projet';

        // Badges
        var badgeSponsor = '';
        if (p.est_sponsorise == 1) {
            badgeSponsor = '<span class="badge bg-warning text-dark">⭐ Sponsorisé</span> ';
        }
        var badgeStatut = '';
        if (p.statut === 'termine') {
            badgeStatut = '<span class="badge bg-success">Terminé</span>';
        } else {
            badgeStatut = '<span class="badge bg-secondary">En cours</span>';
        }

        html += '<div class="col-md-4">';
        html += '  <div class="card h-100 shadow-sm">';
        html += '    <img src="' + photo + '" class="card-img-top" style="height:200px; object-fit:cover;">';
        html += '    <div class="card-body">';
        html += '      <div class="mb-2">' + badgeSponsor + badgeStatut + '</div>';
        html += '      <h5 class="card-title">' + p.titre + '</h5>';
        html += '      <p class="text-muted small mb-1">Par ' + p.createur + '</p>';
        if (p.ville) {
            html += '      <p class="text-muted small mb-2"> ' + echapper(p.ville) + '</p>';
        }
        html += '      <a href="projet_detail.php?id=' + p.id + '" class="btn btn-primary-upcycle btn-sm w-100">Voir le projet</a>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';
    });
    zone.innerHTML = html;
}
</script>

</body>
</html>