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
    <title>Mon Planning | UpcycleConnect</title>
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

    <h4 class="mt-3" style="color:var(--primary-green);"> Ateliers disponibles</h4>
    <div id="evenements"></div>

    <h4 class="mt-4" style="color:var(--primary-green);"> Mes inscriptions</h4>
    <div id="inscriptions"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

fetch('http://localhost:8080/api/evenements')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var html = '';
        data.forEach(function(e) {
            if (e.statut_validation === 1) {
                html += '<div class="card mb-2 p-3">' +
                    '<strong>' + e.titre + '</strong>' +
                    '<p class="text-muted small mb-1">' + e.date + ' — ' + e.prix + '€ — ' + e.place + ' places</p>' +
                    '<button class="btn btn-primary-upcycle btn-sm" onclick="sinscrire(' + e.id + ')">S\'inscrire</button>' +
                    '</div>';
            }
        });
        if (!html) html = '<p class="text-muted">Aucun événement disponible.</p>';
        document.getElementById('evenements').innerHTML = html;
    });

function chargerInscriptions() {
    fetch('http://localhost:8080/api/inscriptions?id_user=' + userId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var html = '';
            if (data && data.length > 0) {
                data.forEach(function(i) {
                    html += '<div class="card mb-2 p-3">' +
                        '<strong>' + i.titre + '</strong>' +
                        '<p class="text-muted small mb-1">' + i.date + ' — ' + i.prix + '€</p>' +
                        '<button class="btn btn-danger btn-sm" onclick="seDesinscrire(' + i.id + ')">Se désinscrire</button>' +
                        '</div>';
                });
            } else {
                html = '<p class="text-muted">Vous n\'avez pas encore d\'inscriptions.</p>';
            }
            document.getElementById('inscriptions').innerHTML = html;
        });
}

function sinscrire(idEvent) {
    fetch('http://localhost:8080/api/inscriptions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_user: userId, id_event: idEvent })
    }).then(function(res) {
        if (res.ok) {
            alert('Inscription réussie !');
            chargerInscriptions();
        }
    });
}

function seDesinscrire(idInscription) {
    fetch('http://localhost:8080/api/inscriptions/' + idInscription, {
        method: 'DELETE'
    }).then(function(res) {
        if (res.ok) {
            alert('Désinscription effectuée !');
            chargerInscriptions();
        }
    });
}

chargerInscriptions();
</script>

</body>
</html>