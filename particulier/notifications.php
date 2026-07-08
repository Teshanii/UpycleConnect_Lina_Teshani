<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes notifications | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mes notifications</h4>
    <p class="text-muted small">Les informations et alertes qui vous concernent.</p>
    <div id="liste"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

function echapper(t) {
    if (t === null || t === undefined) return '';
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function formaterDate(d) {
    if (!d) return '';
    var b = d.replace('T', ' ').replace('Z', '').split(' ');
    var jj = b[0].split('-'); var hh = b[1] ? b[1].substring(0,5) : '';
    return jj[2] + '/' + jj[1] + '/' + jj[0] + ' à ' + hh;
}

fetch('/api/notifications?id_user=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var div = document.getElementById('liste');
        if (!data || data.length === 0) {
            div.innerHTML = '<p class="text-muted">Aucune notification pour le moment.</p>';
            return;
        }
        var html = '';
        data.forEach(function(n) {
            
            var style = n.est_lue === 0 ? 'card mb-2 p-3 border-start border-4 border-success' : 'card mb-2 p-3';
            var badge = n.est_lue === 0 ? '<span class="badge bg-success ms-2">Nouveau</span>' : '';
            html += '<div class="' + style + '">' +
                '<strong>' + echapper(n.titre) + '</strong>' + badge +
                '<span class="text-muted small ms-2">' + formaterDate(n.date) + '</span><br>' +
                '<span>' + echapper(n.message) + '</span>' +
                '</div>';
        });
        div.innerHTML = html;

        
        fetch('/api/notifications?id_user=' + userId, { method: 'PUT' });
    })
    .catch(function() {
        document.getElementById('liste').innerHTML = '<div class="alert alert-danger">Impossible de charger les notifications.</div>';
    });
</script>

</body>
</html>
