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
    <title>Mes Conseils | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mes articles & conseils</h4>
    <p class="text-muted small">Rédigez des tutos et des news pour sensibiliser la communauté à l'upcycling.</p>

    <!-- Formulaire création / modification -->
    <div class="card mb-4 p-3 shadow-sm border-0">
        <h6 id="form-title">Rédiger un nouvel article</h6>
        <input type="hidden" id="edit-id">
        <div class="mb-2">
            <label class="small text-muted">Titre</label>
            <input type="text" id="titre" class="form-control" placeholder="Ex: Comment repeindre un vieux meuble">
        </div>
        <div class="mb-2">
            <label class="small text-muted">Type</label>
            <select id="type" class="form-select">
                <option value="tuto">Tutoriel</option>
                <option value="news">News</option>
                <option value="conseil">Conseil</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="small text-muted">Contenu</label>
            <textarea id="contenu" class="form-control" rows="5" placeholder="Écrivez votre article ici..."></textarea>
        </div>
        <div id="msg"></div>
        <button class="btn btn-primary-upcycle btn-sm" onclick="sauvegarder()">Publier</button>
        <button class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
    </div>

    <!-- Liste de mes articles -->
    <h5 style="color:var(--primary-green);">Mes articles publiés</h5>
    <div id="articles"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// Charger mes articles (filtre par auteur)
function charger() {
    fetch('http://localhost:8080/api/conseils?id_auteur=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('articles');

            if (!data || data.length === 0) {
                div.innerHTML = '<p class="text-muted">Vous n\'avez pas encore publié d\'article.</p>';
                return;
            }

            var html = '';
            data.forEach(function(a) {
                // Badge selon le type
                var badge = a.type === 'tuto'
                    ? '<span class="badge bg-primary">Tutoriel</span>'
                    : a.type === 'news'
                    ? '<span class="badge bg-info text-dark">News</span>'
                    : '<span class="badge bg-success">Conseil</span>';

                html += '<div class="card mb-2 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<strong>' + a.titre + '</strong> ' + badge + '<br>' +
                    '<span class="text-muted small">' + (a.date ? a.date.split('T')[0] : '') + '</span><br>' +
                    '<span class="small">' + (a.contenu.length > 150 ? a.contenu.substring(0, 150) + '...' : a.contenu) + '</span>' +
                    '</div>' +
                    '<div>' +
                    '<button class="btn btn-warning btn-sm me-1" onclick=\'modifier(' + JSON.stringify(a) + ')\'>Modifier</button>' +
                    '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + a.id + ')">Supprimer</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;
        });
}

// Publier ou modifier
function sauvegarder() {
    var id = document.getElementById('edit-id').value;
    var titre = document.getElementById('titre').value.trim();
    var type = document.getElementById('type').value;
    var contenu = document.getElementById('contenu').value.trim();

    if (!titre || !contenu) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Le titre et le contenu sont obligatoires.</div>';
        return;
    }

    var url = id ? 'http://localhost:8080/api/conseils/' + id : 'http://localhost:8080/api/conseils';
    var methode = id ? 'PUT' : 'POST';

    fetch(url, {
        method: methode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titre: titre, type: type, contenu: contenu, id_auteur: userId })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Article publié !</div>';
            resetForm();
            charger();
            setTimeout(function() { document.getElementById('msg').innerHTML = ''; }, 2000);
        }
    });
}

// Remplir le formulaire pour modifier
function modifier(a) {
    document.getElementById('edit-id').value = a.id;
    document.getElementById('titre').value = a.titre;
    document.getElementById('type').value = a.type;
    document.getElementById('contenu').value = a.contenu;
    document.getElementById('form-title').innerText = 'Modifier l\'article';
    window.scrollTo(0, 0);
}

function supprimer(id) {
    if (confirm('Supprimer cet article ?')) {
        fetch('http://localhost:8080/api/conseils/' + id, { method: 'DELETE' })
            .then(function(res) { if (res.ok) charger(); });
    }
}

function resetForm() {
    document.getElementById('edit-id').value = '';
    document.getElementById('titre').value = '';
    document.getElementById('contenu').value = '';
    document.getElementById('type').value = 'tuto';
    document.getElementById('form-title').innerText = 'Rédiger un nouvel article';
}

charger();
</script>

</body>
</html>