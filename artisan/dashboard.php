<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Artisan | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11/dist/css/shepherd.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Artisan : <?php echo $_SESSION['user_prenom']; ?></span>
            <?php include '../includes/traductions.php'; ?>
            <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm" data-trad="nav_deconnexion">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);" data-trad="artisan_titre">Mon espace artisan</h4>
    <p class="text-muted" data-trad="artisan_sous_titre">Trouvez de la matière première et valorisez vos créations.</p>

    <!-- KPIs rapides -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                <small class="text-muted" data-trad="kpi_score">Mon score upcycling</small>
                <h3 id="kpi-score" class="fw-bold" style="color:var(--primary-green);">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <small class="text-muted" data-trad="kpi_objets">Objets disponibles</small>
                <h3 id="kpi-objets" class="fw-bold text-primary">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #e76f51 !important;">
                <small class="text-muted" data-trad="kpi_recups">Mes réservations en cours</small>
                <h3 id="kpi-recups" class="fw-bold" style="color:#e76f51;">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <small class="text-muted" data-trad="kpi_abo">Mon abonnement</small>
                <h3 id="kpi-abo" class="fw-bold" style="color:#6f42c1;">Gratuit</h3>
            </div>
        </div>
    </div>

    <!-- Cartes navigation -->
    <div class="row g-3">
        <div class="col-md-4" id="btn-catalogue">
            <a href="catalogue.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="artisan_catalogue">Catalogue</h5>
                    <p class="text-muted small" data-trad="artisan_catalogue_desc">Trouver des objets à récupérer</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-recups">
            <a href="mes_recuperations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="artisan_recups">Mes récupérations</h5>
                    <p class="text-muted small" data-trad="artisan_recups_desc">Mes objets réservés à aller chercher</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-creations">
            <a href="mes_creations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="artisan_creations">Mes créations</h5>
                    <p class="text-muted small" data-trad="artisan_creations_desc">Documenter mes transformations</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-galerie">
            <a href="../galerie.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_galerie">Galerie communauté</h5>
                    <p class="text-muted small" data-trad="artisan_galerie_desc">Voir les créations et participer aux projets</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-prestations">
            <a href="mes_prestations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="artisan_prestations">Mes prestations</h5>
                    <p class="text-muted small" data-trad="artisan_prestations_desc">Proposer mes services à vendre/donner</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-ateliers">
            <a href="ateliers.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_planning">Ateliers</h5>
                    <p class="text-muted small" data-trad="artisan_ateliers_desc">S'inscrire aux ateliers et formations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-conseils">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_conseils">Conseils</h5>
                    <p class="text-muted small" data-trad="artisan_conseils_desc">Articles et tutoriels de la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-abonnement">
            <a href="abonnement.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="artisan_abonnement">Abonnement</h5>
                    <p class="text-muted small" data-trad="artisan_abonnement_desc">Passer Premium</p>
                </div>
            </a>
        </div>

        <!-- Carte Mon Score : score + récompense Premium à 100 pts -->
        <div class="col-md-4" id="btn-score">
            <a href="score.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_score">Mon Score</h5>
                    <p class="text-muted small" data-trad="artisan_score_desc">100 pts = 1 mois Premium offert</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-portefeuille">
            <a href="../particulier/portefeuille.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_portefeuille">Mon portefeuille</h5>
                    <p class="text-muted small" data-trad="artisan_portefeuille_desc">Mon argent et mes retraits</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-forum">
            <a href="forum.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_forum">Forum</h5>
                    <p class="text-muted small" data-trad="artisan_forum_desc">Échanger avec la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-profil">
            <a href="profil.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_profil">Mon Profil</h5>
                    <p class="text-muted small" data-trad="artisan_profil_desc">Modifier mes informations</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Section Statistiques avancées (Premium) -->
    <h4 class="mt-5" id="btn-stats" style="color:var(--primary-green);" data-trad="artisan_stats_titre">Statistiques avancées</h4>
    <div id="zone-stats" class="position-relative">
        <!-- Overlay cadenas si gratuit -->
        <div id="overlay-premium" class="d-none position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background:rgba(255,255,255,0.85); z-index:10; top:0; left:0; border-radius:8px;">
            <div class="text-center">
                <div style="font-size:3rem;">🔒</div>
                <h5 class="mt-2" data-trad="artisan_premium_titre">Réservé aux membres Premium</h5>
                <p class="text-muted" data-trad="artisan_premium_desc">Passez Premium pour débloquer vos statistiques détaillées.</p>
                <a href="abonnement.php" class="btn btn-primary-upcycle" data-trad="artisan_premium_btn">Passer Premium</a>
            </div>
        </div>

        <!-- KPIs stats -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted" data-trad="stat_recups_label">Objets récupérés</small>
                    <h3 class="fw-bold" style="color:var(--primary-green);" id="stat-recups">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted" data-trad="stat_ventes_label">Prestations vendues</small>
                    <h3 class="fw-bold text-primary" id="stat-ventes">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted" data-trad="stat_ca_label">Chiffre d'affaires</small>
                    <h3 class="fw-bold" style="color:#e76f51;" id="stat-ca">-</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11/dist/js/shepherd.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;


fetch('/api/users')
    .then(r => r.json())
    .then(data => {
        var moi = (data || []).find(u => u.id === userId);
        if (moi) document.getElementById('kpi-score').innerText = moi.score_upcycling;
    });


fetch('/api/catalogue-artisan')
    .then(r => r.json())
    .then(data => {
        document.getElementById('kpi-objets').innerText = (data || []).length;
    });


fetch('/api/demandes_box')
    .then(r => r.json())
    .then(data => {
        var mesRecups = (data || []).filter(function(d) {
            return d.id_artisan === userId && d.statut !== 'recupere';
        });
        document.getElementById('kpi-recups').innerText = mesRecups.length;
    });

fetch('/api/abonnement?id_user=' + userId)
    .then(r => r.json())
    .then(data => {
        var abo = data.abonnement === 'premium' ? 'Premium' : 'Gratuit';
        document.getElementById('kpi-abo').innerText = abo;
    });

fetch('/api/abonnement?id_user=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(abo) {
        if (abo.abonnement !== 'premium') {
            document.getElementById('overlay-premium').classList.remove('d-none');
            return;
        }
        chargerStats();
    });

function chargerStats() {
    fetch('/api/stats-artisan?id_artisan=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('stat-recups').innerText = data.nb_recups || 0;
            document.getElementById('stat-ventes').innerText = data.nb_ventes || 0;
            document.getElementById('stat-ca').innerText = (data.chiffre_affaires || 0).toFixed(2) + ' €';
        });
}


function getCookie(name) {
    return document.cookie.split(';').some(c => c.trim().startsWith(name + '='));
}

const tour = new Shepherd.Tour({
    useModalOverlay: true,
    defaultStepOptions: {
        cancelIcon: { enabled: true },
        scrollTo: true,
        modalOverlayOpeningRadius: 8
    }
});

tour.addStep({
    id: 'bienvenue',
    text: '<strong>Bienvenue sur votre espace artisan !</strong><br><br>On va vous montrer rapidement comment trouver de la matière première et vendre vos créations.',
    buttons: [
        { text: 'Passer', action: tour.cancel, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Commencer →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'catalogue',
    text: '<strong>Catalogue</strong><br>Trouvez des objets déposés par les particuliers dans les box. Réservez-les gratuitement (dons) ou achetez-les.',
    attachTo: { element: '#btn-catalogue', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'recups',
    text: '<strong>Mes récupérations</strong><br>Retrouvez les objets que vous avez réservés. Scannez le code-barres à la box pour finaliser la récupération.',
    attachTo: { element: '#btn-recups', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'creations',
    text: '<strong>Mes créations</strong><br>Documentez vos transformations avec des photos avant/après pour valoriser votre savoir-faire.',
    attachTo: { element: '#btn-creations', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'prestations',
    text: '<strong>Mes prestations</strong><br>Mettez en vente vos créations et services. Une fois validés par un admin, les particuliers peuvent les acheter.',
    attachTo: { element: '#btn-prestations', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'ateliers',
    text: '<strong>Ateliers</strong><br>Inscrivez-vous aux ateliers et formations de la communauté. Les ateliers gratuits sont en un clic, les payants se règlent en ligne.',
    attachTo: { element: '#btn-ateliers', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'abonnement',
    text: '<strong>Abonnement Premium</strong><br>Passez Premium pour accéder à des statistiques avancées et des alertes prioritaires sur les nouveaux objets.',
    attachTo: { element: '#btn-abonnement', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'score',
    text: '<strong>Mon Score</strong><br>Chaque objet récupéré vous rapporte +5 points. À 100 points, réclamez <strong>1 mois de Premium gratuit</strong> !',
    attachTo: { element: '#btn-score', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'portefeuille',
    text: '<strong>Mon portefeuille</strong><br>Quand vous vendez une prestation, l\'argent arrive ici (moins 7% de commission). Vous pouvez retirer votre argent quand vous voulez.',
    attachTo: { element: '#btn-portefeuille', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'conseils',
    text: '<strong>Conseils</strong><br>Retrouvez des articles et tutoriels postés par la communauté pour vous inspirer dans vos créations.',
    attachTo: { element: '#btn-conseils', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'forum',
    text: '<strong>Forum</strong><br>Échangez avec les autres membres de la communauté UpcycleConnect : partagez vos astuces, posez vos questions et trouvez de l\'inspiration.',
    attachTo: { element: '#btn-forum', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'profil',
    text: '<strong>Mon Profil</strong><br>Modifiez vos informations, gérez votre abonnement, consultez vos paiements et relancez ce tutoriel quand vous voulez.',
    attachTo: { element: '#btn-profil', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'stats',
    text: '<strong>Statistiques avancées</strong><br>En tant que membre Premium, suivez votre activité en détail : objets récupérés, ventes, chiffre d\'affaires, impact écologique et graphiques. Les membres gratuits voient cette section verrouillée.',
    attachTo: { element: '#btn-stats', on: 'top' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'fin',
    text: '<strong>Vous êtes prêt !</strong><br>Vous pouvez relancer ce tutoriel depuis votre profil à tout moment. Bon upcycling !',
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'C\'est parti !', action: tour.complete, classes: 'btn btn-sm btn-success' }
    ]
});

function marquerTutoVu() {
    var dans1an = new Date();
    dans1an.setFullYear(dans1an.getFullYear() + 1);
    document.cookie = "tuto_vu_artisan=1; path=/; expires=" + dans1an.toUTCString();
}

tour.on('complete', marquerTutoVu);
tour.on('cancel', marquerTutoVu);

if (!getCookie('tuto_vu_artisan')) {
    tour.start();
}
</script>

</body>
</html>