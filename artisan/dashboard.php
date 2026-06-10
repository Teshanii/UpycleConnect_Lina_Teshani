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
        <span class="text-white me-3">Artisan : <?php echo $_SESSION['user_prenom']; ?></span>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);">Mon espace artisan</h4>
    <p class="text-muted">Trouvez de la matière première et valorisez vos créations.</p>

    <!-- KPIs rapides -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                <small class="text-muted">Mon score upcycling</small>
                <h3 id="kpi-score" class="fw-bold" style="color:var(--primary-green);">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <small class="text-muted">Objets disponibles</small>
                <h3 id="kpi-objets" class="fw-bold text-primary">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #e76f51 !important;">
                <small class="text-muted">Mes réservations en cours</small>
                <h3 id="kpi-recups" class="fw-bold" style="color:#e76f51;">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <small class="text-muted">Mon abonnement</small>
                <h3 id="kpi-abo" class="fw-bold" style="color:#6f42c1;">Gratuit</h3>
            </div>
        </div>
    </div>

    <!-- Cartes navigation -->
    <div class="row g-3">
        <div class="col-md-4" id="btn-catalogue">
            <a href="catalogue.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Catalogue</h5>
                    <p class="text-muted small">Trouver des objets à récupérer</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-recups">
            <a href="mes_recuperations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes récupérations</h5>
                    <p class="text-muted small">Mes objets réservés à aller chercher</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-creations">
            <a href="mes_creations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes créations</h5>
                    <p class="text-muted small">Documenter mes transformations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-prestations">
            <a href="mes_prestations.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mes prestations</h5>
                    <p class="text-muted small">Proposer mes services à vendre/donner</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-ateliers">
            <a href="ateliers.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Ateliers</h5>
                    <p class="text-muted small">S'inscrire aux ateliers et formations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-conseils">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Conseils</h5>
                    <p class="text-muted small">Articles et tutoriels de la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-abonnement">
            <a href="abonnement.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Abonnement</h5>
                    <p class="text-muted small">Passer Premium</p>
                </div>
            </a>
        </div>

        <!-- Carte Mon Score : score + récompense Premium à 100 pts -->
        <div class="col-md-4" id="btn-score">
            <a href="score.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mon Score</h5>
                    <p class="text-muted small">100 pts = 1 mois Premium offert</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-portefeuille">
            <a href="../particulier/portefeuille.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mon portefeuille</h5>
                    <p class="text-muted small">Mon argent et mes retraits</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-forum">
            <a href="forum.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Forum</h5>
                    <p class="text-muted small">Échanger avec la communauté</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-profil">
            <a href="profil.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5>Mon Profil</h5>
                    <p class="text-muted small">Modifier mes informations</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Section Statistiques avancées (Premium) -->
    <h4 class="mt-5" id="btn-stats" style="color:var(--primary-green);">Statistiques avancées</h4>
    <div id="zone-stats" class="position-relative">
        <!-- Overlay cadenas si gratuit -->
        <div id="overlay-premium" class="d-none position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background:rgba(255,255,255,0.85); z-index:10; top:0; left:0; border-radius:8px;">
            <div class="text-center">
                <div style="font-size:3rem;">🔒</div>
                <h5 class="mt-2">Réservé aux membres Premium</h5>
                <p class="text-muted">Passez Premium pour débloquer vos statistiques détaillées.</p>
                <a href="abonnement.php" class="btn btn-primary-upcycle">Passer Premium</a>
            </div>
        </div>

        <!-- KPIs stats -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted">Objets récupérés</small>
                    <h3 class="fw-bold" style="color:var(--primary-green);" id="stat-recups">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted">Prestations vendues</small>
                    <h3 class="fw-bold text-primary" id="stat-ventes">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow-sm">
                    <small class="text-muted">Chiffre d'affaires</small>
                    <h3 class="fw-bold" style="color:#e76f51;" id="stat-ca">-</h3>
                </div>
            </div>
          
        </div>

        <!-- Graphiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <div class="card p-3 shadow-sm">
                    <h6 class="text-muted">Mes récupérations par mois</h6>
                    <canvas id="graph-mois"></canvas>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-3 shadow-sm">
                    <h6 class="text-muted">Répartition par catégorie</h6>
                    <canvas id="graph-cat"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11/dist/js/shepherd.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// Mon score upcycling
fetch('http://localhost:8080/api/users')
    .then(r => r.json())
    .then(data => {
        var moi = (data || []).find(u => u.id === userId);
        if (moi) document.getElementById('kpi-score').innerText = moi.score_upcycling;
    });

// Objets disponibles dans le catalogue
fetch('http://localhost:8080/api/catalogue-artisan')
    .then(r => r.json())
    .then(data => {
        document.getElementById('kpi-objets').innerText = (data || []).length;
    });

// Mes réservations en cours (objets réservés, pas encore récupérés)
fetch('http://localhost:8080/api/demandes_box')
    .then(r => r.json())
    .then(data => {
        var mesRecups = (data || []).filter(function(d) {
            return d.id_artisan === userId && d.statut !== 'recupere';
        });
        document.getElementById('kpi-recups').innerText = mesRecups.length;
    });

// Mon abonnement (gratuit ou premium)
fetch('http://localhost:8080/api/abonnement?id_user=' + userId)
    .then(r => r.json())
    .then(data => {
        var abo = data.abonnement === 'premium' ? 'Premium' : 'Gratuit';
        document.getElementById('kpi-abo').innerText = abo;
    });

// ===== STATISTIQUES AVANCÉES (Premium) =====
// On vérifie d'abord l'abonnement
fetch('http://localhost:8080/api/abonnement?id_user=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(abo) {
        if (abo.abonnement !== 'premium') {
            // Gratuit : on affiche l'overlay cadenas par-dessus les stats
            document.getElementById('overlay-premium').classList.remove('d-none');
            return; // on ne charge pas les vraies données
        }
        // Premium : on charge les vraies stats
        chargerStats();
    });

function chargerStats() {
    fetch('http://localhost:8080/api/stats-artisan?id_artisan=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // KPIs
            document.getElementById('stat-recups').innerText = data.nb_recups || 0;
            document.getElementById('stat-ventes').innerText = data.nb_ventes || 0;
            document.getElementById('stat-ca').innerText = (data.chiffre_affaires || 0).toFixed(2) + ' €';
            

            // Graphique courbe : récups par mois
            var mois = (data.par_mois || []).map(function(m) { return m.mois; });
            var nbMois = (data.par_mois || []).map(function(m) { return m.nb; });
            new Chart(document.getElementById('graph-mois'), {
                type: 'line',
                data: {
                    labels: mois,
                    datasets: [{
                        label: 'Récupérations',
                        data: nbMois,
                        borderColor: '#2d6a4f',
                        backgroundColor: 'rgba(45,106,79,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { plugins: { legend: { display: false } } }
            });

            // Camembert : répartition par catégorie
            var cats = (data.categories || []).map(function(c) { return c.nom; });
            var nbCats = (data.categories || []).map(function(c) { return c.nb; });
            new Chart(document.getElementById('graph-cat'), {
                type: 'doughnut',
                data: {
                    labels: cats,
                    datasets: [{
                        data: nbCats,
                        backgroundColor: ['#2d6a4f', '#0d6efd', '#e76f51', '#6f42c1', '#f4a261', '#e9c46a', '#999999']
                    }]
                }
            });
        });
}

// ===== TUTORIEL ARTISAN =====
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

// On enregistre le cookie avec une expiration d'1 an pour que le tuto ne réapparaisse pas tout seul
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