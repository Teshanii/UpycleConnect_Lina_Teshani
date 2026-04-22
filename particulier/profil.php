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
    <title>Mon Profil | UpcycleConnect</title>
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
            <h4 style="color:var(--primary-green);">Mon Profil</h4>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom">
            </div>
            <div class="mb-3">
                <label class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="email">
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="sauvegarder()">SAUVEGARDER</button>
            <button class="btn btn-outline-secondary w-100 mt-2" onclick="relancerTuto()">Revoir le tutoriel</button>
        </div>
    </div>
</div>

<script>
fetch('http://localhost:8080/api/users')
    .then(r => r.json())
    .then(data => {
        const user = data.find(u => u.id === <?php echo $_SESSION['user_id']; ?>);
        if (user) {
            document.getElementById('nom').value = user.nom;
            document.getElementById('prenom').value = user.pre;
            document.getElementById('email').value = user.mail;
        }
    });

function sauvegarder() {
    fetch('http://localhost:8080/api/users/<?php echo $_SESSION['user_id']; ?>', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: document.getElementById('nom').value,
            pre: document.getElementById('prenom').value,
            mail: document.getElementById('email').value,
            id_role: <?php echo $_SESSION['user_role_id'] ?? 4; ?>
        })
    }).then(res => {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Profil mis à jour !</div>';
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}

function relancerTuto() {
    document.cookie = "tuto_vu=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
    window.location.href = 'dashboard.php';
}
</script>

</body>
</html>