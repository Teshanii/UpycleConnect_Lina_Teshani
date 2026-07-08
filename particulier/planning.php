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

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Ateliers disponibles</h4>

    <div class="row g-2 mb-3 mt-2">
        <div class="col-md-4">
            <input type="text" id="recherche" class="form-control" placeholder="Rechercher un atelier..." oninput="filtrer()">
        </div>
        <div class="col-md-3">
            <select id="filtre-prix" class="form-select" onchange="filtrer()">
                <option value="">Tous les prix</option>
                <option value="gratuit">Gratuit</option>
                <option value="payant">Payant</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filtre-place" class="form-select" onchange="filtrer()">
                <option value="">Toutes les places</option>
                <option value="dispo">Avec places disponibles</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" onclick="switcherVue()" id="btn-switch">Calendrier</button>
        </div>
    </div>

    <div id="vue-liste">
        <div id="loader" class="text-center mt-3">
            <div class="spinner-border" style="color:var(--primary-green);"></div>
            <p class="text-muted mt-2">Chargement...</p>
        </div>
        <div id="evenements"></div>
    </div>

    <div id="vue-calendrier" style="display:none;">
        <div id="calendrier" class="mt-3"></div>
    </div>

    <div id="msg-inscription"></div>

    <h4 class="mt-4" style="color:var(--primary-green);">Mes inscriptions</h4>
    <div id="inscriptions"></div>
</div>

<!-- Modal détail événement -->
<div class="modal fade" id="modalEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Date :</strong> <span id="modal-date"></span></p>
                <p><strong>Animateur :</strong> <span id="modal-animateur"></span></p>
                <p><strong>Prix :</strong> <span id="modal-prix"></span></p>
                <p><strong>Places restantes :</strong> <span id="modal-places"></span></p>
                <p><strong>Statut :</strong> <span id="modal-statut"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" id="modal-btn-inscrire" onclick="sinscrireDepuisModal()">S'inscrire</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var tousLesEvenements = [];
var mesInscriptions = [];
var vueCalendrier = false;
var calendar;
var idEventModal = null;
var modalCtrl = new bootstrap.Modal(document.getElementById('modalEvent'));

fetch('/api/evenements')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loader').style.display = 'none';
        var maintenant = new Date();
        tousLesEvenements = data.filter(function(e) {
            return e.statut_validation === 1 && new Date(e.date) > maintenant;
        });
        afficherEvenements(tousLesEvenements);
        initialiserCalendrier();
    })
    .catch(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('evenements').innerHTML = '<div class="alert alert-danger">Impossible de charger les ateliers.</div>';
    });

// Formate une date '2026-07-03T10:00:00' en '3 juillet 2026 à 10h00'
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

function afficherEvenements(liste) {
    if (!liste || liste.length === 0) {
        document.getElementById('evenements').innerHTML = '<p class="text-muted">Aucun atelier disponible.</p>';
        return;
    }

    var html = '';
    liste.forEach(function(e) {
        var dejaInscrit = mesInscriptions.some(function(i) { return i.id_event === e.id; });
        var restantes = e.place - (e.nb_inscrits || 0);

        var badgePrix = e.prix === 0
            ? '<span class="badge bg-success">Gratuit</span>'
            : '<span class="badge bg-warning text-dark">' + e.prix + '€</span>';

        var badgePlaces;
        if (restantes <= 0) {
            badgePlaces = '<span class="badge bg-danger">Complet</span>';
        } else if (restantes <= 3) {
            badgePlaces = '<span class="badge bg-warning text-dark">' + restantes + ' places restantes</span>';
        } else {
            badgePlaces = '<span class="badge bg-light text-dark border">' + restantes + ' places</span>';
        }

        var diffJours = Math.abs((new Date() - new Date(e.date)) / (1000 * 60 * 60 * 24));
        var badgeNouveau = diffJours <= 7 ? '<span class="badge bg-info text-dark ms-1">Nouveau</span>' : '';

        var bouton;
        if (dejaInscrit) {
            bouton = '<button class="btn btn-secondary btn-sm" disabled>Deja inscrit</button>';
        } else if (restantes <= 0) {
            bouton = '<button class="btn btn-danger btn-sm" disabled>Complet</button>';
        } else if (e.prix > 0) {
            // Atelier payant — on redirige vers Stripe
            bouton = '<button class="btn btn-warning btn-sm" onclick="payerAtelier(' + e.id + ', ' + e.prix + ', \'' + e.titre + '\')">Payer ' + e.prix + '€</button>';
        } else {
            bouton = '<button class="btn btn-primary-upcycle btn-sm" onclick="sinscrire(' + e.id + ')">S\'inscrire</button>';
        }

        var badgeType = e.type ? '<span class="badge bg-secondary ms-1">' + e.type + '</span>' : '';
        var ligneLieu = e.lieu ? '<span class="text-muted small">Lieu : ' + e.lieu + '</span><br>' : '';
        var ligneDesc = e.description ? '<p class="small mt-1 mb-0">' + e.description + '</p>' : '';

        html += '<div class="card mb-2 p-3">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<strong>' + e.titre + '</strong>' + badgeNouveau + badgeType + '<br>' +
            '<span class="text-muted small">Date : ' + formaterDate(e.date) + '</span><br>' +
            '<span class="text-muted small">Animateur : ' + (e.anim || 'Non precise') + '</span><br>' +
            ligneLieu +
            '<div class="mt-1">' + badgePrix + ' ' + badgePlaces + '</div>' +
            ligneDesc +
            '</div>' +
            '<div>' + bouton + '</div>' +
            '</div>' +
            '</div>';
    });

    document.getElementById('evenements').innerHTML = html;
}


function payerAtelier(idEvent, prix, titre) {
    fetch('stripe_checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: idEvent, prix: prix * 100, titre: titre })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        
        if (data.url) {
            window.location.href = data.url;
        } else {
            afficherMsg('Erreur lors du paiement.', 'danger');
        }
    });
}

function filtrer() {
    var terme = document.getElementById('recherche').value.toLowerCase();
    var filtrePrix = document.getElementById('filtre-prix').value;
    var filtrePlace = document.getElementById('filtre-place').value;

    var resultats = tousLesEvenements.filter(function(e) {
        var matchTitre = e.titre.toLowerCase().includes(terme);
        var matchPrix = filtrePrix === '' ||
            (filtrePrix === 'gratuit' && e.prix === 0) ||
            (filtrePrix === 'payant' && e.prix > 0);
        var matchPlace = filtrePlace === '' || (filtrePlace === 'dispo' && (e.place - (e.nb_inscrits || 0)) > 0);
        return matchTitre && matchPrix && matchPlace;
    });

    afficherEvenements(resultats);
}

function switcherVue() {
    vueCalendrier = !vueCalendrier;
    document.getElementById('vue-liste').style.display = vueCalendrier ? 'none' : 'block';
    document.getElementById('vue-calendrier').style.display = vueCalendrier ? 'block' : 'none';
    document.getElementById('btn-switch').innerText = vueCalendrier ? 'Liste' : 'Calendrier';
    if (vueCalendrier && calendar) calendar.render();
}

function initialiserCalendrier() {
    var eventsCalendrier = tousLesEvenements.map(function(e) {
        var estInscrit = mesInscriptions.some(function(i) { return i.id_event === e.id; });
        var couleur = estInscrit ? '#0d6efd' : (e.prix === 0 ? '#2d6a4f' : '#f4a261');
        return {
            title: e.titre,
            start: e.date,
            color: couleur,
            extendedProps: { event: e }
        };
    });

    calendar = new FullCalendar.Calendar(document.getElementById('calendrier'), {
        initialView: 'dayGridMonth',
        locale: 'fr',
        buttonText: { today: "Aujourd'hui", month: 'Mois', list: 'Liste' },
        noEventsContent: 'Aucun atelier a afficher',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        events: eventsCalendrier,
        eventClick: function(info) { ouvrirModal(info.event.extendedProps.event); }
    });
}

function ouvrirModal(e) {
    idEventModal = e.id;
    var dejaInscrit = mesInscriptions.some(function(i) { return i.id_event === e.id; });
    var restantes = e.place - (e.nb_inscrits || 0);

    document.getElementById('modal-titre').innerText = e.titre;
    document.getElementById('modal-date').innerText = formaterDate(e.date);
    document.getElementById('modal-animateur').innerText = e.anim || 'Non precise';
    document.getElementById('modal-prix').innerText = e.prix === 0 ? 'Gratuit' : e.prix + '€';
    document.getElementById('modal-places').innerText = restantes <= 0 ? 'Complet' : restantes + ' places restantes';

    var btnInscrire = document.getElementById('modal-btn-inscrire');
    if (dejaInscrit) {
        document.getElementById('modal-statut').innerText = 'Vous etes deja inscrit.';
        btnInscrire.style.display = 'none';
    } else if (restantes <= 0) {
        document.getElementById('modal-statut').innerText = 'Cet atelier est complet.';
        btnInscrire.style.display = 'none';
    } else {
        document.getElementById('modal-statut').innerText = 'Places disponibles.';
        btnInscrire.innerText = e.prix > 0 ? 'Payer ' + e.prix + '€' : "S'inscrire";
        btnInscrire.style.display = 'block';
    }
    modalCtrl.show();
}

function sinscrireDepuisModal() {
    if (!idEventModal) return;
    var event = tousLesEvenements.find(function(e) { return e.id === idEventModal; });
    modalCtrl.hide();
    if (event && event.prix > 0) {
        payerAtelier(event.id, event.prix, event.titre);
    } else {
        sinscrire(idEventModal);
    }
}

function sinscrire(idEvent) {
    var dejaInscrit = mesInscriptions.some(function(i) { return i.id_event === idEvent; });
    if (dejaInscrit) { afficherMsg('Vous etes deja inscrit a cet atelier.', 'warning'); return; }

    fetch('/api/inscriptions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_user: userId, id_event: idEvent })
    }).then(function(res) {
        if (res.ok) {
            afficherMsg('Inscription reussie !', 'success');
            chargerInscriptions();
        } else {
            res.json().then(function(data) { afficherMsg(data.error || 'Erreur.', 'danger'); });
        }
    });
}

function seDesinscrire(idInscription, titreAtelier) {
    if (confirm('Se desinscrire de "' + titreAtelier + '" ?')) {
        fetch('/api/inscriptions/' + idInscription, { method: 'DELETE' })
        .then(function(res) {
            if (res.ok) { afficherMsg('Desinscription effectuee.', 'success'); chargerInscriptions(); }
        });
    }
}

function chargerInscriptions() {
    fetch('/api/inscriptions?id_user=' + userId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            mesInscriptions = data || [];
            var html = '';
            if (mesInscriptions.length > 0) {
                mesInscriptions.forEach(function(i) {
                    var estPasse = new Date(i.date) < new Date();
                    var badgePasse = estPasse ? '<span class="badge bg-secondary ms-1">Passe</span>' : '';
                    var btnDesinscrire = estPasse
                        ? '<button class="btn btn-outline-secondary btn-sm" disabled>Termine</button>'
                        : '<button class="btn btn-outline-danger btn-sm" onclick="seDesinscrire(' + i.id + ', \'' + i.titre + '\')">Se desinscrire</button>';

                    html += '<div class="card mb-2 p-3">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                        '<div><strong>' + i.titre + '</strong>' + badgePasse + '<br>' +
                        '<span class="text-muted small">Date : ' + formaterDate(i.date) + ' — ' + i.prix + '€</span></div>' +
                        '<div>' + btnDesinscrire + '</div>' +
                        '</div></div>';
                });
            } else {
                html = '<p class="text-muted">Vous n\'avez pas encore d\'inscriptions.</p>';
            }
            document.getElementById('inscriptions').innerHTML = html;
            afficherEvenements(tousLesEvenements);
        });
}

function afficherMsg(texte, type) {
    document.getElementById('msg-inscription').innerHTML = '<div class="alert alert-' + type + ' mt-2">' + texte + '</div>';
    setTimeout(function() { document.getElementById('msg-inscription').innerHTML = ''; }, 3000);
}

chargerInscriptions();
</script>
</body>
</html>