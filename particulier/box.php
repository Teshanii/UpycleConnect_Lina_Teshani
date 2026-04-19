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
    <title>Demander une box | UpcycleConnect</title>
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

    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);"> Demander une box</h4>
            <p class="text-muted small">Décrivez votre objet. Un admin validera et vous enverra un code pour ouvrir la box.</p>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Description de l'objet</label>
                <textarea class="form-control" id="description" rows="3" placeholder="Ex: Une vieille chaise en bois..."></textarea>
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="envoyer()">ENVOYER LA DEMANDE</button>
        </div>
    </div>
</div>

<script>
function envoyer() {
    fetch('http://localhost:8080/api/demandes_box', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_user: <?php echo $_SESSION['user_id']; ?>,
            description: document.getElementById('description').value
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Demande envoyée !</div>';
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}
</script>

</body>
</html>