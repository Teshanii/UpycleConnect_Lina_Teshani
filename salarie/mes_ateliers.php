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

<?php include __DIR__ . '/menu.php'; ?>

<div class="container mt-4">
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
                <label class="small text-muted">Date et heure de fin</label>
                <input type="datetime-local" id="date_fin" class="form-control">
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

    <!-- B3 : onglets de filtrage -->
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><button id="onglet-avenir" class="nav-link onglet-btn active" onclick="changerOnglet('avenir')">À venir</button></li>
        <li class="nav-item"><button id="onglet-passes" class="nav-link onglet-btn" onclick="changerOnglet('passes')">Passés</button></li>
        <li class="nav-item"><button id="onglet-attente" class="nav-link onglet-btn" onclick="changerOnglet('attente')">En attente</button></li>
        <li class="nav-item"><button id="onglet-complets" class="nav-link onglet-btn" onclick="changerOnglet('complets')">Complets</button></li>
    </ul>

    <div id="ateliers"></div>
</div>

<!-- Modal liste des inscrits + presence -->
<div class="modal fade" id="modalInscrits" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inscrits &mdash; <span id="modal-titre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-inscrits"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var tousMesAteliers = [];   // B3 : on garde tous mes ateliers en mémoire
var ongletActif = 'avenir'; // B3 : onglet affiché par défaut

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

// Récupère mes ateliers puis affiche l'onglet actif
function charger() {
    fetch('/api/evenements')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tousMesAteliers = (data || []).filter(function(e) { return e.id_anim === userId; });
            afficher();
        });
}

// B3 : change d'onglet
function changerOnglet(nom) {
    ongletActif = nom;
    var boutons = document.querySelectorAll('.onglet-btn');
    boutons.forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('onglet-' + nom).classList.add('active');
    afficher();
}

// Affiche la liste filtrée selon l'onglet
function afficher() {
    var div = document.getElementById('ateliers');

    if (tousMesAteliers.length === 0) {
        div.innerHTML = '<div class="text-center text-muted py-4">' +
            '<p>Aucun atelier crée pour le moment.</p>' +
            '<button class="btn btn-primary-upcycle btn-sm" onclick="focusForm()">Créer mon premier atelier</button>' +
            '</div>';
        return;
    }

    var maintenant = new Date();
    var liste = tousMesAteliers.filter(function(e) {
        var estPasse = new Date(e.date) < maintenant;
        if (ongletActif === 'avenir')   return !estPasse;
        if (ongletActif === 'passes')   return estPasse;
        if (ongletActif === 'attente')  return e.statut_validation === 0;
        if (ongletActif === 'complets') return e.nb_inscrits >= e.place;
        return true;
    });

    if (liste.length === 0) {
        div.innerHTML = '<p class="text-muted py-3">Aucun atelier dans cet onglet.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(e) {
        var estPasse = new Date(e.date) < maintenant;

        
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

        var motif = (e.statut_validation === 2 && e.motif_refus)
            ? '<div class="alert alert-danger mt-2 mb-0 py-1 px-2 small">Motif du refus : ' + e.motif_refus + '</div>'
            : '';

        var badgeType = e.type ? '<span class="badge bg-secondary ms-1">' + e.type + '</span>' : '';
        var badgePasse = estPasse ? '<span class="badge bg-dark ms-1">Passé</span>' : '';
        var ligneLieu = e.lieu ? '<span class="text-muted small">Lieu : ' + e.lieu + '</span><br>' : '';
        var ligneDesc = e.description ? '<p class="small mt-1 mb-0">' + e.description + '</p>' : '';

        
        var btnVoir = '<button class="btn btn-outline-success btn-sm me-1 mb-1" onclick="voirInscrits(' + e.id + ', \'' + e.titre.replace(/'/g, "\\'") + '\')">Voir les inscrits</button>';
        var btnDupliquer = '<button class="btn btn-outline-primary btn-sm me-1 mb-1" onclick=\'dupliquer(' + JSON.stringify(e) + ')\'>Dupliquer</button>';
        var btnModifier = '<button class="btn btn-warning btn-sm me-1 mb-1" onclick=\'modifier(' + JSON.stringify(e) + ')\'>Modifier</button>';
        var btnSupprimer = '<button class="btn btn-outline-danger btn-sm mb-1" onclick="supprimer(' + e.id + ')">Supprimer</button>';
        var boutons = estPasse
            ? (btnVoir + btnDupliquer)
            : (btnVoir + btnModifier + btnSupprimer + btnDupliquer);

        html += '<div class="card mb-2 p-3">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<strong>' + e.titre + '</strong> ' + badgeStatut + badgeType + badgePasse + '<br>' +
            '<span class="text-muted small">Date : ' + formaterDate(e.date) + '</span><br>' +
            ligneLieu +
            '<div class="mt-1">' + badgePrix + ' <span class="badge bg-light text-dark border">' + e.nb_inscrits + '/' + e.place + ' places</span>' +
            (e.nb_inscrits >= e.place ? ' <span class="badge bg-danger">Complet</span>' : '') + '</div>' +
            ligneDesc +
            motif +
            '</div>' +
            '<div class="text-end" style="min-width:150px;">' +
            boutons +
            '</div>' +
            '</div>' +
            '</div>';
    });

    div.innerHTML = html;
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
    var dateFin = document.getElementById('date_fin').value;

    if (!titre || !date) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">Le titre et la date sont obligatoires.</div>';
        return;
    }
    if (new Date(date) < new Date()) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">La date ne peut pas être dans le passé.</div>';
        return;
    }
    if (dateFin && new Date(dateFin) <= new Date(date)) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger py-1">L\'heure de fin doit être après le début.</div>';
        return;
    }

    var dateMysql = date.replace('T', ' ') + ':00';
    var url = id ? '/api/evenements/' + id : '/api/evenements';
    var methode = id ? 'PUT' : 'POST';

    
    var btn = document.getElementById('btn-save');
    btn.disabled = true;
    btn.innerText = 'Enregistrement...';

    fetch(url, {
        method: methode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            titre: titre, type: type, lieu: lieu, description: description,
            date: dateMysql, date_fin: dateFin ? dateFin.replace('T', ' ') + ':00' : '',
            prix: prix, place: places, id_anim: userId
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


function modifier(e) {
    document.getElementById('edit-id').value = e.id;
    document.getElementById('titre').value = e.titre;
    
    if (e.date) {
        document.getElementById('date').value = e.date.replace(' ', 'T').substring(0, 16);
    }
    document.getElementById('date_fin').value = e.date_fin ? e.date_fin.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('prix').value = e.prix;
    document.getElementById('places').value = e.place;
    document.getElementById('form-title').innerText = 'Modifier l\'atelier';

    document.getElementById('type').value = e.type || 'Atelier';
    document.getElementById('lieu').value = e.lieu || '';
    document.getElementById('description').value = e.description || '';

    window.scrollTo(0, 0);
}


function dupliquer(e) {
    document.getElementById('edit-id').value = '';   
    document.getElementById('titre').value = e.titre;
    document.getElementById('date').value = '';       
    document.getElementById('date_fin').value = '';
    document.getElementById('prix').value = e.prix;
    document.getElementById('places').value = e.place;
    document.getElementById('type').value = e.type || 'Atelier';
    document.getElementById('lieu').value = e.lieu || '';
    document.getElementById('description').value = e.description || '';
    document.getElementById('form-title').innerText = 'Dupliquer — choisissez une nouvelle date';
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
    document.getElementById('date_fin').value = '';
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


var modalInscritsCtrl = new bootstrap.Modal(document.getElementById('modalInscrits'));
var derniersInscrits = [];
var dernierTitre = '';

function voirInscrits(idEvent, titre) {
    document.getElementById('modal-titre').innerText = titre;
    document.getElementById('modal-inscrits').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" style="color:var(--primary-green);"></div></div>';
    modalInscritsCtrl.show();

    fetch('/api/inscrits-evenement/' + idEvent)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data || data.length === 0) {
                document.getElementById('modal-inscrits').innerHTML = '<p class="text-muted">Aucun inscrit pour le moment.</p>';
                return;
            }
            derniersInscrits = data; dernierTitre = titre;
            var atelier = tousMesAteliers.filter(function(x) { return x.id === idEvent; })[0];
            var estPasse = atelier && new Date(atelier.date) < new Date();
            var html = '<ul class="list-group">';
            data.forEach(function(u) {
                var checked = u.present === 1 ? 'checked' : '';
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<div><strong>' + u.pre + ' ' + u.nom + '</strong><br>' +
                    '<span class="text-muted small">' + u.mail + '</span></div>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" ' + checked +
                    ' onchange="marquerPresence(' + idEvent + ', ' + u.id + ', this.checked)">' +
                    '<label class="form-check-label small">Present</label>' +
                    '</div>' +
                    (estPasse ? '<a class="btn btn-outline-dark btn-sm ms-2" target="_blank" href="attestation.php?id_event=' + idEvent + '&id_user=' + u.id + '">Attestation</a>' : '') +
                    '</li>';
            });
            html += '</ul>';
            html += '<button class="btn btn-primary-upcycle btn-sm mt-3 w-100" onclick="envoyerRappel(' + idEvent + ')">Envoyer un rappel par email aux inscrits</button>';
            html += '<button class="btn btn-outline-secondary btn-sm mt-2 w-100" onclick="exporterCSV()">Exporter la liste (CSV)</button>';
            document.getElementById('modal-inscrits').innerHTML = html;
        });
}


function exporterCSV() {
    if (derniersInscrits.length === 0) return;
    var lignes = ['Prenom,Nom,Email,Present'];
    derniersInscrits.forEach(function(u) {
        lignes.push(u.pre + ',' + u.nom + ',' + u.mail + ',' + (u.present === 1 ? 'oui' : 'non'));
    });
    var blob = new Blob([lignes.join('\n')], { type: 'text/csv' });
    var lien = document.createElement('a');
    lien.href = URL.createObjectURL(blob);
    lien.download = 'inscrits_' + dernierTitre.replace(/[^a-z0-9]/gi, '_') + '.csv';
    lien.click();
}


function marquerPresence(idEvent, idUser, present) {
    fetch('/api/presence', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: idEvent, id_user: idUser, present: present ? 1 : 0 })
    });
}

function envoyerRappel(idEvent) {
    var message = prompt("Message a envoyer aux inscrits :", "Rappel : votre atelier approche, pensez a venir !");
    if (!message) return;
    fetch('rappel_inscrits.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idEvent, message: message })
    }).then(function(res) {
        if (res.ok) alert("Rappel envoye aux inscrits !");
        else alert("Une erreur est survenue, reessayez.");
    }).catch(function() {
        alert("Connexion impossible, reessayez.");
    });
}

charger();
</script>

</body>
</html>