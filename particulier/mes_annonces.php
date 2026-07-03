<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes annonces | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php"> UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);"> Mes annonces</h4>

    <!-- Loader — visible pendant que les données chargent -->
    <div id="loader" class="text-center mt-4">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
        <p class="text-muted mt-2">Chargement de vos annonces...</p>
    </div>

    <div id="liste"></div>
</div>

<script>
fetch('/api/annonces?id_user=<?php echo $_SESSION['user_id']; ?>')
    .then(function(res) { return res.json(); })
    .then(function(data) {

        // Les données sont arrivées — on cache le loader
        document.getElementById('loader').style.display = 'none';

        var html = '';

        if (data && data.length > 0) {
            data.forEach(function(a) {

                // Badge statut validation
                var validation;
                if (a.statut_validation === 1) {
                    validation = '<span class="badge bg-primary"> En ligne</span>';
                } else if (a.statut_validation === 2) {
                validation = '<span class="badge bg-danger"> Refusée</span>';
                if (a.motif_refus) {
                    validation += '<p class="text-danger small mt-1">Motif : ' + a.motif_refus + '</p>';
                }
                } else {
                    validation = '<span class="badge bg-warning text-dark"> En attente de validation</span>';
                }

                // Badge type don ou vente
                var type = a.type_offre === 'don'
                    ? '<span class="badge bg-info text-dark">Don gratuit</span>'
                    : '<span class="badge bg-secondary">Vente — ' + a.prix + '€</span>';

                // Badge statut de l'annonce
                var statutAnnonce;
                if (a.statut_annonce === 'vendu') {
                    statutAnnonce = '<span class="badge bg-danger">Vendu</span>';
                } else if (a.statut_annonce === 'recupere') {
                    statutAnnonce = '<span class="badge bg-secondary">Récupéré</span>';
                } else {
                    statutAnnonce = '<span class="badge bg-success">Disponible</span>';
                }

                html += '<div class="card mb-3 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h5 class="mb-1">' + a.titre + '</h5>' +
                    '<p class="text-muted small mb-1">Catégorie : ' + (a.categorie || 'Non renseignée') + '</p>' +
                    '<p class="mb-2">' + (a.description || '') + '</p>' +
                    (a.photo ? '<img src="/' + a.photo + '" style="max-width:150px; border-radius:8px;" class="mb-2"><br>' : '') +
                    type + ' ' + validation + ' ' + statutAnnonce +
                    '</div>' +
                    '<div class="ms-3">' +
                    '<button class="btn btn-outline-danger btn-sm d-block mb-1" onclick="supprimer(' + a.id + ')">Supprimer</button>' +
                    '<a href="modifier_annonce.php?id=' + a.id + '" class="btn btn-outline-secondary btn-sm d-block">Modifier</a>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });
        } else {
            html = '<p class="text-muted mt-3">Vous n\'avez pas encore d\'annonces.</p>';
        }

        document.getElementById('liste').innerHTML = html;
    })
    .catch(function() {
        // Si le fetch échoue — on cache le loader et on affiche une erreur
        document.getElementById('loader').style.display = 'none';
        document.getElementById('liste').innerHTML = '<div class="alert alert-danger">Impossible de charger vos annonces. Réessayez plus tard.</div>';
    });

function supprimer(id) {
    if (confirm('Supprimer cette annonce ?')) {
        fetch('/api/annonces/' + id, { method: 'DELETE' })
            .then(function() { location.reload(); });
    }
}
</script>

</body>
</html>