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
    <title>Mes Ateliers | UpcycleConnect</title>
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mes ateliers & événements</h4>
    <p class="text-muted small">Créez des ateliers et formations. Ils seront visibles par les particuliers après validation d'un administrateur.</p>

    <div class="alert alert-light border small">
        <strong>Comment ça marche :</strong>
        1. Vous créez l'atelier → 2. Un administrateur le valide → 3. Il devient visible par les particuliers dans le planning.
    </div>

    <!-- Formulaire création / modification -->
    <div class="card mb-4 p-3 shadow-sm border-0">
        <h6 id="form-title">Créer un nouvel atelier</h6>
        <input type="hidden" id="edit-id">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="small text-muted">Titre</label>
                <input type="text" id="titre" class="form-control" placeholder="Ex: Rénovation de palette">
            </div>
            <div class="col-md-6">
                <label class="small text-muted">Type</label>
                <select id="type" class="form-select">
                    <option value="Atelier">Atelier</option>
                    <option value="Formation">Formation</option>
                    <option value="Conférence">Conférence</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="small text-muted">Date et heure</label>
                <input type="datetime-local" id="date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="small text-muted">Lieu</label>
                <input type="text" id="lieu" class="form-control" placeholder="Ex: Salle B, 11e arrondissement">
            </div>
            <div class="col-md-6">
                <label class="small text-muted">Prix (€) — 0 pour gratuit</label>
                <input type="number" id="prix" class="form-control" value="0" min="0" step="0.01">
            </div>
            <div class="col-md-6">
                <label class="small text-muted">Nombre de places</label>
                <input type="number" id="places" class="form-control" value="10" min="1">
            </div>
            <div class="col-12">
                <label class="small text-muted">Description</label>
                <textarea id="description" class="form-control" rows="2" placeholder="Ce que les participants vont apprendre, matériel à prévoir..."></textarea>
            </div>
        </div>
        <div id="msg" class="mt-2"></div>
        <div class="mt-2">
            <button id="btn-save" class="btn btn-primary-upcycle btn-sm" onclick="sauvegarder()">Créer l'atelier</button>
            <button class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
        </div>
    </div>

    <h5 style="color:var(--primary-green);">Mes ateliers</h5>
    <div id="ateliers"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

function formaterDate(d) {
    if (!d) return 'Non précisée';
    var partie = d.replace('T', ' ').replace('Z', '');
    var bloc = partie.split(' ');
    var dateP = bloc[0].split('-');
    var heureP = bloc[1] ? bloc[1].split(':') : ['00', '00'];
    var mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    var jour = parseInt(dateP[2], 10);
    var nomMois = mois[parseInt(dateP[1], 10) - 1];
    return jour + ' ' + nomMois + ' ' + dateP[0] + ' à ' + heureP[0] + 'h' + heureP[1];
}


function charger() {
    fetch('http://localhost:8080/api/evenements')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // On garde seulement MES ateliers
            var mesAteliers = (data || []).filter(function(e) { return e.id_anim === userId; });

            var div = document.getElementById('ateliers');

            if (mesAteliers.length === 0) {
                div.innerHTML = '<div class="text-center text-muted py-4">' +
                    '<p>Aucun atelier crée pour le moment.</p>' +
                    '<button class="btn btn-primary-upcycle btn-sm" onclick="focusForm()">Créer mon premier atelier</button>' +
                    '</div>';
                return;
            }

            var html = '';
            mesAteliers.forEach(function(e) {
                // Badge de validation
                var badgeStatut;
                if (e.statut_validation === 1) {
                    badgeStatut = '<span class="badge bg-success">Validé</span>';
                } else if (e.statut_validation === 2) {
                    badgeStatut = '<span class="badge bg-danger">Refusé</span>';
                } else {
                    badgeStatut = '<span class="badge bg-warning text-dark">En attente de validation</span>';
                }

                var badgePrix = e.prix === 0
                    ? '<span class="badge bg-light text-dark border">Gratuit</span>'
                    : '<span class="badge bg-light text-dark border">' + e.prix + '€</span>';

                // Motif de refus si refusé
                var motif = (e.statut_validation === 2 && e.motif_refus)
                    ? '<div class="alert alert-danger mt-2 mb-0 py-1 px-2 small">Motif du refus : ' + e.motif_refus + '</div>'
                    : '';

                var badgeType = e.type ? '<span class="badge bg-secondary ms-1">' + e.type + '</span>' : '';
                var ligneLieu = e.lieu ? '<span class="text-muted small">Lieu : ' + e.lieu + '</span><br>' : '';
                var ligneDesc = e.description ? '<p class="small mt-1 mb-0">' + e.description + '</p>' : '';

                html += '<div class="card mb-2 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<strong>' + e.titre + '</strong> ' + badgeStatut + badgeType + '<br>' +
                    '<span class="text-muted small">Date : ' + formaterDate(e.date) + '</span><br>' +
                    ligneLieu +
                    '<div class="mt-1">' + badgePrix + ' <span class="badge bg-light text-dark border">' + e.place + ' places</span> <span class="badge bg-info text-dark">' + e.nb_inscrits + ' inscrit(s)</span></div>' +
                    ligneDesc +
                    motif +
                    '</div>' +
                    '<div>' +
                    '<button class="btn btn-warning btn-sm me-1" onclick=\'modifier(' + JSON.stringify(e) + ')\'>Modifier</button>' +
                    '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + e.id + ')">Supprimer</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            div.innerHTML = html;
        });
}

function sauvegarder() {
    var id = document.getElementById('edit-id').value;
    var titre = document.getElementById('titre').value.trim();
    var type = document.getElementById('type').value;
    var date = document.getElementById('date').value;
    var lieu = document.getElementById('lieu').value.trim();
    var description = document.getElementById('description').value.trim();
    var prix = parseFloat(document.getElementById('prix').value) || 0;
    var places = parseInt(document.getElementById('places').value) || 1;

    if (!titre || !date) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">Le titre et la date sont obligatoires.</div>';
        return;
    }
    if (new Date(date) < new Date()) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">La date ne peut pas être dans le passé.</div>';
        return;
    }

    var dateMysql = date.replace('T', ' ') + ':00';
    var url = id ? 'http://localhost:8080/api/evenements/' + id : 'http://localhost:8080/api/evenements';
    var methode = id ? 'PUT' : 'POST';

    // On désactive le bouton pendant l'envoi (évite le double-clic)
    var btn = document.getElementById('btn-save');
    btn.disabled = true;
    btn.innerText = 'Enregistrement...';

    fetch(url, {
        method: methode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            titre: titre, type: type, lieu: lieu, description: description,
            date: dateMysql, prix: prix, place: places, id_anim: userId
        })
    }).then(function(res) {
        btn.disabled = false;
        btn.innerText = "Créer l'atelier";
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success py-1">Atelier enregistré ! En attente de validation admin.</div>';
            resetForm();
            charger();
            setTimeout(function() { document.getElementById('msg').innerHTML = ''; }, 2500);
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">Une erreur est survenue, réessayez.</div>';
        }
    }).catch(function() {
        btn.disabled = false;
        btn.innerText = "Créer l'atelier";
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">Connexion impossible, réessayez.</div>';
    });
}


// Remplir le formulaire pour modifier
function modifier(e) {
    document.getElementById('edit-id').value = e.id;
    document.getElementById('titre').value = e.titre;
    // Reconvertir la date pour l'input datetime-local
    if (e.date) {
        document.getElementById('date').value = e.date.replace(' ', 'T').substring(0, 16);
    }
    document.getElementById('prix').value = e.prix;
    document.getElementById('places').value = e.place;
    document.getElementById('form-title').innerText = 'Modifier l\'atelier';

    document.getElementById('type').value = e.type ||'Atelier';
    document.getElementById('lieu').value = e.lieu || '';
    document.getElementById('description').value = e.description || '';

    window.scrollTo(0, 0);
}

function supprimer(id) {
    if (confirm('Annuler et supprimer cet atelier ? Les inscrits seront prévenus par email.')) {
        fetch('annuler_atelier.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        }).then(function(res) {
            if (res.ok) charger();
            else alert("Une erreur est survenue, réessayez.");
        });
    }
}

function resetForm() {
    document.getElementById('edit-id').value = '';
    document.getElementById('titre').value = '';
    document.getElementById('date').value = '';
    document.getElementById('prix').value = '0';
    document.getElementById('places').value = '10';
    document.getElementById('form-title').innerText = 'Créer un nouvel atelier';

    document.getElementById('type').value = 'Atelier';
    document.getElementById('lieu').value = '';
    document.getElementById('description').value = '';
}

function focusForm() {
    document.getElementById('titre').focus();
    window.scrollTo(0, 0);
}

charger();
</script>

</body>
</html>