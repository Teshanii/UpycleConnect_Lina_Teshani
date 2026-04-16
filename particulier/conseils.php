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
    <title>Conseils | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);"> Espace Conseils</h4>
    <p class="text-muted small">Articles et tutoriels publiés par nos salariés.</p>

    <div id="liste"></div>
</div>

<script>
fetch('http://localhost:8080/api/conseils')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var html = '';
        if (data && data.length > 0) {
            data.forEach(function(a) {
                html += '<div class="card mb-3 p-3">' +
                    '<h5>' + a.titre + '</h5>' +
                    '<p class="text-muted small">' + a.type + ' — ' + a.date + '</p>' +
                    '<p>' + a.contenu + '</p>' +
                    '</div>';
            });
        } else {
            html = '<p class="text-muted">Aucun article disponible pour le moment.</p>';
        }
        document.getElementById('liste').innerHTML = html;
    });
</script>

</body>
</html>