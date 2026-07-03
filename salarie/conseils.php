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

<?php include __DIR__ . '/menu.php'; ?>

<div class="container mt-4">
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
        <button id="btn-pub" class="btn btn-primary-upcycle btn-sm" onclick="sauvegarder(1)">Publier</button>
        <button class="btn btn-outline-secondary btn-sm" onclick="sauvegarder(0)">Enregistrer comme brouillon</button>
        <button class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
    </div>

    <!-- Liste de mes articles -->
    <h5 style="color:var(--primary-green);">Mes articles</h5>
    <div id="articles"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// Échappe le HTML pour éviter les injections de code (XSS)
function echapper(t) {
    if (t === null || t === undefined) return '';
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Formate une date SQL en français (ex: 5 mars 2026 à 14h30)
function formaterDate(d) {
    if (!d) return 'Non précisée';
    var partie = d.replace('T', ' ').replace('Z', '');
    var bloc = partie.split(' ');
    var dateP = bloc[0].split('-');
    var heureP = bloc[1] ? bloc[1].split(':') : ['00', '00'];
    var mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    var jour = parseInt(dateP[2], 10);
    var nomMois = mois[parseInt(dateP[1], 10) - 1];
    return jour + ' ' + nomMois + ' ' + dateP[0] + ' à ' + heureP[0] + 'h' + heureP[1];
}


function focusForm() {
    document.getElementById('titre').focus();
    window.scrollTo(0, 0);
}

// Charger mes articles (filtre par auteur)
function charger() {
    fetch('/api/conseils?id_auteur=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('articles');

            if (!data || data.length === 0) {
                div.innerHTML = '<div class="text-center text-muted py-4">' +
                    '<p>Aucun article publié pour le moment.</p>' +
                    '<button class="btn btn-primary-upcycle btn-sm" onclick="focusForm()">Rédiger mon premier article</button>' +
                    '</div>';
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

                var badgeStatut = a.statut === 1
                    ? '<span class="badge bg-success ms-1">Publié</span>'
                    : '<span class="badge bg-secondary ms-1">Brouillon</span>';
                var btnPublier = a.statut === 0
                    ? '<button class="btn btn-success btn-sm me-1" onclick=\'publierArticle(' + JSON.stringify(a) + ')\'>Publier</button>'
                    : '';

                html += '<div class="card mb-2 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<strong>' + echapper(a.titre) + '</strong> ' + badge + badgeStatut + '<br>' +
                    '<span class="text-muted small">' + formaterDate(a.date) + '</span><br>' +
                    '<span class="small">' + echapper(a.contenu.length > 150 ? a.contenu.substring(0, 150) + '...' : a.contenu) + '</span>' +
                    '</div>' +
                    '<div>' +
                    btnPublier +
                    '<button class="btn btn-warning btn-sm me-1" onclick=\'modifier(' + JSON.stringify(a) + ')\'>Modifier</button>' +
                    '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + a.id + ')">Supprimer</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;
        });
}

function sauvegarder(statut) {
    var id = document.getElementById('edit-id').value;
    var titre = document.getElementById('titre').value.trim();
    var type = document.getElementById('type').value;
    var contenu = document.getElementById('contenu').value.trim();

    if (!titre || !contenu) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Le titre et le contenu sont obligatoires.</div>';
        return;
    }

    var url = id ? '/api/conseils/' + id : '/api/conseils';
    var methode = id ? 'PUT' : 'POST';

    var btn = document.getElementById('btn-pub');
    btn.disabled = true;
    btn.innerText = 'Publication...';

    fetch(url, {
        method: methode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titre: titre, type: type, contenu: contenu, id_auteur: userId, statut: statut })
    }).then(function(res) {
        btn.disabled = false;
        btn.innerText = 'Publier';
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">' + (statut === 1 ? 'Article publié !' : 'Brouillon enregistré.') + '</div>';
            resetForm();
            charger();
            setTimeout(function() { document.getElementById('msg').innerHTML = ''; }, 2000);
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Une erreur est survenue, réessayez.</div>';
        }
    }).catch(function() {
        btn.disabled = false;
        btn.innerText = 'Publier';
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Connexion impossible, réessayez.</div>';
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
        fetch('/api/conseils/' + id, { method: 'DELETE' })
            .then(function(res) { if (res.ok) charger(); });
    }
}

// A7 : publier un brouillon en un clic
function publierArticle(a) {
    fetch('/api/conseils/' + a.id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titre: a.titre, type: a.type, contenu: a.contenu, statut: 1 })
    }).then(function(res) { if (res.ok) charger(); }).catch(function() { alert('Connexion impossible, réessayez.'); });
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