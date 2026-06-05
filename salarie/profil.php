<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
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
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>

    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);">Mon Profil</h4>
            <p class="text-muted small">Espace salarié</p>

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

            <hr class="mt-4">
            <h5 style="color:var(--primary-green);">Changer le mot de passe</h5>
            <div id="msg-mdp"></div>

            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="nouveau-mdp" placeholder="Minimum 6 caractères">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm-mdp">
            </div>
            <button class="btn btn-outline-secondary w-100" onclick="changerMdp()">CHANGER LE MOT DE PASSE</button>
        </div>
    </div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var userRoleId = <?php echo $_SESSION['user_role']; ?>;
var userData = null;

// Charger les infos du profil
fetch('http://localhost:8080/api/users')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        userData = data.find(function(u) { return u.id === userId; });
        if (userData) {
            document.getElementById('nom').value = userData.nom;
            document.getElementById('prenom').value = userData.pre;
            document.getElementById('email').value = userData.mail;
        }
    });

function sauvegarder() {
    fetch('http://localhost:8080/api/users/' + userId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: document.getElementById('nom').value,
            pre: document.getElementById('prenom').value,
            mail: document.getElementById('email').value,
            id_role: userRoleId,
            est_actif: 1,
            score_upcycling: userData ? userData.score_upcycling : 0,
            est_verifie: userData ? userData.est_verifie : 1
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Profil mis à jour !</div>';
            setTimeout(function() { document.getElementById('msg').innerHTML = ''; }, 3000);
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur lors de la mise à jour.</div>';
        }
    });
}

function changerMdp() {
    var nouveau = document.getElementById('nouveau-mdp').value;
    var confirme = document.getElementById('confirm-mdp').value;

    if (!nouveau) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Entrez un nouveau mot de passe.</div>';
        return;
    }
    if (nouveau.length < 6) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Le mot de passe doit faire au moins 6 caractères.</div>';
        return;
    }
    if (nouveau !== confirme) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
        return;
    }

    fetch('http://localhost:8080/api/users/' + userId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: document.getElementById('nom').value,
            pre: document.getElementById('prenom').value,
            mail: document.getElementById('email').value,
            id_role: userRoleId,
            est_actif: 1,
            score_upcycling: userData ? userData.score_upcycling : 0,
            est_verifie: userData ? userData.est_verifie : 1,
            mdp: nouveau
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-success">Mot de passe changé !</div>';
            document.getElementById('nouveau-mdp').value = '';
            document.getElementById('confirm-mdp').value = '';
            setTimeout(function() { document.getElementById('msg-mdp').innerHTML = ''; }, 3000);
        } else {
            document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}
</script>

</body>
</html>