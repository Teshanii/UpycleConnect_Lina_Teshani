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
    <title>Mon Score | UpcycleConnect</title>
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
    <div class="card mx-auto mt-3" style="max-width:500px;">
        <div class="card-body text-center">
            <h4 style="color:var(--primary-green);"> Mon Upcycling Score</h4>
            <p class="text-muted small">Plus vous recyclez, plus votre score augmente !</p>

            <div id="score-affichage">
                <div class="display-1 fw-bold" style="color:var(--primary-green);" id="score">...</div>
                <p class="text-muted">points</p>

                <div class="progress mb-2" style="height:20px;">
                    <div class="progress-bar" id="barre" role="progressbar" style="width:0%; background-color:var(--primary-green);"></div>
                </div>
                <p class="text-muted small" id="prochain-palier"></p>

                <div id="badge"></div>
            </div>

            <hr>
            <p class="small text-muted">
                +10 pts pour une annonce publiée<br>
                +20 pts pour un don d'objet<br>
                +5 pts par objet récupéré
            </p>
        </div>
    </div>
</div>
<script>
fetch('http://localhost:8080/api/users')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var userId = <?php echo $_SESSION['user_id']; ?>;
        var user = data.find(function(u) { return u.id === userId; });
        if (user) {
            var score = user.score_upcycling;
            document.getElementById('score').innerText = score;

            // barre de progression vers le prochain palier
            var paliers = [100, 250, 500, 1000];
            var prochainPalier = paliers.find(function(p) { return p > score; });
            if (prochainPalier) {
                var palierPrecedent = paliers[paliers.indexOf(prochainPalier) - 1] || 0;
                var pct = ((score - palierPrecedent) / (prochainPalier - palierPrecedent)) * 100;
                document.getElementById('barre').style.width = pct + '%';
                document.getElementById('prochain-palier').innerText = 'Prochain palier : ' + prochainPalier + ' pts';
            } else {
                document.getElementById('barre').style.width = '100%';
                document.getElementById('prochain-palier').innerText = 'Score maximum atteint !';
            }

            // badges débloqués
            var badges = '';
            if (score >= 100) badges += '<span class="badge me-1" style="background-color:var(--accent-beige);"> Éco-citoyen</span>';
            if (score >= 250) badges += '<span class="badge me-1" style="background-color:var(--accent-beige);"> Recycleur</span>';
            if (score >= 500) badges += '<span class="badge me-1" style="background-color:var(--accent-beige);"> Expert</span>';
            if (score >= 1000) badges += '<span class="badge me-1" style="background-color:var(--accent-beige);"> Légende</span>';
            document.getElementById('badge').innerHTML = badges;
        }
    });
</script>
</body>
</html>