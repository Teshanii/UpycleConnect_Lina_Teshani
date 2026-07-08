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
    <title>Mon Planning | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
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
    <h4 class="mt-3" style="color:var(--primary-green);">Mon planning</h4>
    <p class="text-muted small">Vos ateliers et la liste des inscrits.</p>

    <!-- Stats globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                <small class="text-muted">Ateliers à venir</small>
                <h3 id="kpi-avenir" class="fw-bold" style="color:var(--primary-green);">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <small class="text-muted">Total inscrits</small>
                <h3 id="kpi-inscrits" class="fw-bold text-primary">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                <small class="text-muted">Taux de remplissage moyen</small>
                <h3 id="kpi-remplissage" class="fw-bold" style="color:#f4a261;">0%</h3>
            </div>
        </div>
    </div>

    <!-- Bouton bascule vue -->
    <div class="mb-3 text-end">
        <button class="btn btn-outline-secondary btn-sm" onclick="switcherVue()" id="btn-switch">Vue calendrier</button>
    </div>

    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>

    <!-- Vue liste -->
    <div id="vue-liste">
        <div id="ateliers"></div>
    </div>

    <!-- Vue calendrier -->
    <div id="vue-calendrier" style="display:none;">
        <div id="calendrier" class="mt-2"></div>
    </div>
</div>

<!-- Modal liste des inscrits -->
<div class="modal fade" id="modalInscrits" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inscrits — <span id="modal-titre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-inscrits"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var mesAteliers = [];
var vueCalendrier = false;
var calendar;
var modalCtrl = new bootstrap.Modal(document.getElementById('modalInscrits'));


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


fetch('/api/evenements')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('loader').style.display = 'none';

        mesAteliers = (data || []).filter(function(e) {
            return e.id_anim === userId && e.statut_validation === 1;
        });
        

        afficher(mesAteliers);
        calculerStats(mesAteliers);
        initialiserCalendrier();
    })
    .catch(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('ateliers').innerHTML =
            '<div class="alert alert-danger">Impossible de charger vos ateliers. Vérifiez que le serveur est démarré, puis réessayez.</div>';
    });

function afficher(liste) {
    var div = document.getElementById('ateliers');

    if (!liste || liste.length === 0) {
        div.innerHTML = '<div class="text-center text-muted py-4">' +
            '<p>Aucun atelier validé pour le moment.<br>' +
            '<span class="small">Vos ateliers apparaissent ici une fois validés par un administrateur.</span></p>' +
            '<a href="mes_ateliers.php" class="btn btn-primary-upcycle btn-sm">Créer un atelier</a>' +
            '</div>';
        return;
    }

    
    liste.sort(function(a, b) { return new Date(a.date) - new Date(b.date); });

    var html = '';
    liste.forEach(function(e) {
        var estPasse = new Date(e.date) < new Date();
        var badgePasse = estPasse
            ? '<span class="badge bg-secondary ms-1">Passé</span>'
            : '<span class="badge bg-success ms-1">À venir</span>';

        
        var capaciteTotale = e.place;
        var restantes = e.place - e.nb_inscrits;
        var tauxRemplissage = capaciteTotale > 0 ? Math.round((e.nb_inscrits / capaciteTotale) * 100) : 0;

        html += '<div class="card mb-2 p-3">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<strong>' + e.titre + '</strong> ' + badgePasse + '<br>' +
            '<span class="text-muted small">Date : ' + formaterDate(e.date) + '</span><br>' +
            '<div class="mt-1">' +
            '<span class="badge bg-info text-dark">' + e.nb_inscrits + ' inscrit(s)</span> ' +
            '<span class="badge bg-light text-dark border">' + restantes + ' places restantes</span> ' +
            '<span class="badge bg-light text-dark border">' + tauxRemplissage + '% rempli</span>' +
            '</div>' +
            '<div class="progress mt-2" style="height:6px; width:250px;">' +
            '<div class="progress-bar" style="width:' + tauxRemplissage + '%; background-color:var(--primary-green);"></div>' +
            '</div>' +
            '</div>' +
            '<div>' +
            '<button class="btn btn-outline-success btn-sm" onclick="voirInscrits(' + e.id + ', \'' + e.titre.replace(/'/g, "\\'") + '\')">Voir les inscrits</button>' +
            '</div>' +
            '</div>' +
            '</div>';
    });

    div.innerHTML = html;
}

function calculerStats(liste) {
    var avenir = liste.filter(function(e) { return new Date(e.date) >= new Date(); }).length;
    document.getElementById('kpi-avenir').innerText = avenir;

    var totalInscrits = 0;
    var totalTaux = 0;
    var nbAteliers = liste.length;

    liste.forEach(function(e) {
        totalInscrits += e.nb_inscrits;
        var capacite = e.place;
        if (capacite > 0) {
            totalTaux += (e.nb_inscrits / capacite) * 100;
        }
    });

    document.getElementById('kpi-inscrits').innerText = totalInscrits;
    var moyenne = nbAteliers > 0 ? Math.round(totalTaux / nbAteliers) : 0;
    document.getElementById('kpi-remplissage').innerText = moyenne + '%';
}


function switcherVue() {
    vueCalendrier = !vueCalendrier;
    document.getElementById('vue-liste').style.display = vueCalendrier ? 'none' : 'block';
    document.getElementById('vue-calendrier').style.display = vueCalendrier ? 'block' : 'none';
    document.getElementById('btn-switch').innerText = vueCalendrier ? 'Vue liste' : 'Vue calendrier';
    if (vueCalendrier && calendar) calendar.render();
}

function initialiserCalendrier() {
    var eventsCalendrier = mesAteliers.map(function(e) {
        var estPasse = new Date(e.date) < new Date();
        return {
            title: e.titre,
            start: e.date,
            end: e.date_fin || null,                    // A4 : heure de fin si renseignee
            color: estPasse ? '#6c757d' : '#2d6a4f',    // B7 : gris si passe, vert si a venir
            extendedProps: { event: e }
        };
    });

    calendar = new FullCalendar.Calendar(document.getElementById('calendrier'), {
        initialView: 'dayGridMonth',
        locale: 'fr',
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', day: 'Jour', list: 'Liste' },
        noEventsContent: 'Aucun atelier à afficher',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: eventsCalendrier,
        
        eventClick: function(info) {
            var e = info.event.extendedProps.event;
            voirInscrits(e.id, e.titre);
        }
    });
}


function voirInscrits(idEvent, titre) {
    document.getElementById('modal-titre').innerText = titre;
    document.getElementById('modal-inscrits').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" style="color:var(--primary-green);"></div></div>';
    modalCtrl.show();

    fetch('/api/inscrits-evenement/' + idEvent)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data || data.length === 0) {
                document.getElementById('modal-inscrits').innerHTML = '<p class="text-muted">Aucun inscrit pour le moment.</p>';
                return;
            }

            var html = '<ul class="list-group">';
            data.forEach(function(u) {
                html += '<li class="list-group-item">' +
                    '<strong>' + u.pre + ' ' + u.nom + '</strong><br>' +
                    '<span class="text-muted small">' + u.mail + '</span>' +
                    '</li>';
            });
            
            html += '</ul>';
            html += '<button class="btn btn-primary-upcycle btn-sm mt-3 w-100" onclick="envoyerRappel(' + idEvent + ')">Envoyer un rappel par email aux inscrits</button>';
            document.getElementById('modal-inscrits').innerHTML = html;

        });
}


function envoyerRappel(idEvent) {
    var message = prompt("Message à envoyer aux inscrits :", "Rappel : votre atelier approche, pensez à venir !");
    if (!message) return;

    fetch('rappel_inscrits.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idEvent, message: message })
    }).then(function(res) {
        if (res.ok) alert("Rappel envoyé aux inscrits !");
        else alert("Une erreur est survenue, réessayez.");
    }).catch(function() {
        alert("Connexion impossible, réessayez.");
    });
}


</script>

</body>
</html>