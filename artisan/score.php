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
            <p class="text-muted small">Récupérez des objets pour faire grimper votre score !</p>

            <div id="score-affichage">
                <div class="display-1 fw-bold" style="color:var(--primary-green);" id="score">...</div>
                <p class="text-muted">points</p>

                <div class="progress mb-2" style="height:20px;">
                    <div class="progress-bar" id="barre" role="progressbar" style="width:0%; background-color:var(--primary-green);"></div>
                </div>
                <p class="text-muted small" id="prochain-palier"></p>
            </div>

            <hr>

            <!-- Bloc récompense : tous les 100 pts = 1 mois Premium -->
            <div id="bloc-recompense" class="mt-3"></div>

            <hr>
            <p class="small text-muted">
                +5 pts par objet récupéré dans une box
            </p>
        </div>
    </div>
</div>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var scoreActuel = 0;

// On charge le score puis l'état des récompenses
function chargerScore() {
    fetch('http://localhost:8080/api/users')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var user = data.find(function(u) { return u.id === userId; });
            if (!user) return;
            scoreActuel = user.score_upcycling;
            document.getElementById('score').innerText = scoreActuel;

            // barre de progression vers le prochain palier de 100
            var dansLePalier = scoreActuel % 100;
            var pct = (dansLePalier / 100) * 100;
            document.getElementById('barre').style.width = pct + '%';
            document.getElementById('prochain-palier').innerText = 'Encore ' + (100 - dansLePalier) + ' pts pour la prochaine récompense';

            chargerRecompense();
        });
}

function chargerRecompense() {
    fetch('http://localhost:8080/api/abonnement?id_user=' + userId)
        .then(function(res) { return res.json(); })
        .then(function(abo) {
            var bloc = document.getElementById('bloc-recompense');

            // Récompenses méritées (1 par tranche de 100) vs déjà réclamées
            var meritees = Math.floor(scoreActuel / 100);
            var dejaPrises = abo.recompense_reclamee || 0;
            var dispo = meritees - dejaPrises;

            if (dispo > 0) {
                bloc.innerHTML =
                    '<p class="fw-bold" style="color:var(--primary-green);"> Vous avez ' + dispo + ' récompense(s) disponible(s) !</p>' +
                    '<p class="text-muted small">Réclamez 1 mois de Premium gratuit (ajouté à votre abonnement actuel).</p>' +
                    '<button class="btn btn-primary-upcycle" id="btn-recompense" onclick="reclamer()">Réclamer 1 mois de Premium</button>';
            } else {
                bloc.innerHTML = '<p class="text-muted"> Atteignez le prochain palier de 100 points pour gagner 1 mois de Premium gratuit.</p>';
            }
        });
}

function reclamer() {
    var btn = document.getElementById('btn-recompense');
    btn.disabled = true; // anti double-clic
    btn.innerText = 'Traitement...';

    fetch('http://localhost:8080/api/recompense', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_user: userId })
    })
    .then(function(res) { return res.json().then(function(j) { return { ok: res.ok, body: j }; }); })
    .then(function(r) {
        var bloc = document.getElementById('bloc-recompense');
        if (r.ok) {
            // On affiche le succès puis on recharge l'état (il peut rester des récompenses dispo)
            bloc.innerHTML = '<div class="alert alert-success mb-2"> ' + r.body.message + '</div>';
            chargerRecompense();
        } else {
            bloc.innerHTML = '<div class="alert alert-danger mb-0">' + r.body.error + '</div>';
        }
    });
}

chargerScore();
</script>
</body>
</html>