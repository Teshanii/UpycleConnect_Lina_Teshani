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
        <a href="../connexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);"> Mes annonces</h4>
    <div id="liste"></div>
</div>

<script>
fetch('http://localhost:8080/api/annonces?id_user=<?php echo $_SESSION['user_id']; ?>')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var html = '';
        if (data && data.length > 0) {
            data.forEach(function(a) {
                var validation = a.statut_validation === 1
                    ? '<span class="badge bg-success">Publiée</span>'
                    : '<span class="badge bg-warning text-dark">En attente</span>';

                var type = a.type_offre === 'don'
                    ? '<span class="badge bg-info text-dark">Don gratuit</span>'
                    : '<span class="badge bg-secondary">Vente — ' + a.prix + '€</span>';

                var statutAnnonce = a.statut_annonce === 'vendu'
                    ? '<span class="badge bg-danger">Vendu</span>'
                    : a.statut_annonce === 'recupere'
                    ? '<span class="badge bg-primary">Récupéré</span>'
                    : '<span class="badge bg-light text-dark border">Disponible</span>';

                html += '<div class="card mb-3 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h5 class="mb-1">' + a.titre + '</h5>' +
                    '<p class="text-muted small mb-1">Catégorie : ' + (a.categorie || 'Non renseignée') + '</p>' +
                    '<p class="mb-2">' + (a.description || '') + '</p>' +
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
            html = '<p class="text-muted">Vous n\'avez pas encore d\'annonces.</p>';
        }
        document.getElementById('liste').innerHTML = html;
    });

function supprimer(id) {
    if (confirm('Supprimer cette annonce ?')) {
        fetch('http://localhost:8080/api/annonces/' + id, { method: 'DELETE' })
            .then(function() { location.reload(); });
    }
}
</script>

</body>
</html>