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
    <title>Mon Espace | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php"> UpcycleConnect</a>
        <span class="text-white me-3">Bonjour, <?php echo $_SESSION['user_prenom']; ?> !</span>
        <a href="../connexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<!-- Overlay tuto première connexion -->
<?php if (!isset($_COOKIE['tuto_vu'])): ?>
<div id="overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999; display:flex; align-items:center; justify-content:center;">
    <div class="card p-4" style="max-width:450px;">
        <h5 style="color:var(--primary-green);">Bienvenue sur UpcycleConnect !</h5>
        <p>Voici comment ça marche :</p>
        <ul>
            <li>Déposez vos objets via une annonce ou une box</li>
            <li>Gagnez des points avec votre Upcycling Score</li>
            <li>Consultez votre planning d'ateliers</li>
        </ul>
        <button class="btn btn-primary-upcycle w-100" onclick="fermerTuto()">C'est parti !</button>
    </div>
</div>
<?php endif; ?>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);">Mon tableau de bord</h4>
    <p class="text-muted">Que voulez-vous faire ?</p>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="annonce.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5> Déposer une annonce</h5>
                    <p class="text-muted small">Donnez ou vendez un objet</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="mes_annonces.php" class="text-decoration-none text-dark">
            <div class="card p-3 text-center">
            <h5> Mes annonces</h5>
            <p class="text-muted small">Suivez vos annonces publiées</p>
        </div>
    </a>
</div>
        <div class="col-md-4">
            <a href="box.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5> Demander une box</h5>
                    <p class="text-muted small">Déposez dans un conteneur</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="score.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5> Mon Score</h5>
                    <p class="text-muted small">Votre impact écologique</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="planning.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5> Mon Planning</h5>
                    <p class="text-muted small">Vos ateliers et formations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5> Conseils</h5>
                    <p class="text-muted small">Tutos recyclage</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
function fermerTuto() {
    document.cookie = "tuto_vu=1; path=/";
    document.getElementById('overlay').style.display = 'none';
}
</script>

</body>
</html>