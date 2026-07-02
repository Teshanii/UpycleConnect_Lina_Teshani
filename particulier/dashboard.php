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
    <title>Mon Espace | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11/dist/css/shepherd.css"/>
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Particulier : <?php echo $_SESSION['user_prenom']; ?></span>
            <?php include 'includes/traductions.php'; ?>
            <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h4 style="color:var(--primary-green);" data-trad="dashboard_titre">Mon tableau de bord</h4>
    <p class="text-muted" data-trad="dashboard_action">Que voulez-vous faire ?</p>

    <div class="row g-3">
        <div class="col-md-4" id="btn-annonce">
            <a href="annonce.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_annonce">Déposer une annonce</h5>
                    <p class="text-muted small">Donnez ou vendez un objet</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-mes-annonces">
            <a href="mes_annonces.php" class="text-decoration-none text-dark">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_mes_annonces">Mes annonces</h5>
                    <p class="text-muted small">Suivez vos annonces publiées</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-box">
            <a href="box.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_box">Demander une box</h5>
                    <p class="text-muted small">Déposez dans un conteneur</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-score">
            <a href="score.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_score">Mon Score</h5>
                    <p class="text-muted small">Votre impact écologique</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-planning">
            <a href="planning.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_planning">Mon Planning</h5>
                    <p class="text-muted small">Vos ateliers et formations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-conseils">
            <a href="conseils.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_conseils">Conseils</h5>
                    <p class="text-muted small">Tutos recyclage</p>
                </div>
            </a>
        </div>
        <div class="col-md-4" id="btn-prestations">
            <a href="prestations.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_prestations">Prestations</h5>
                    <p class="text-muted small">Services proposés par nos artisans</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-galerie">
            <a href="../galerie.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_galerie">Galerie communauté</h5>
                    <p class="text-muted small">Découvrir les créations et participer aux projets</p>
                </div>
            </a>
        </div>  
        <div class="col-md-4" id="btn-forum">
            <a href="forum.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_forum">Forum</h5>
                    <p class="text-muted small">Échanger avec la communauté</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-portefeuille">
            <a href="portefeuille.php" class="text-decoration-none">
                <div class="card p-3 text-center h-100">
                    <h5 data-trad="btn_portefeuille">Mon portefeuille</h5>
                    <p class="text-muted small">Mon argent et mes retraits</p>
                </div>
            </a>
        </div>

        <div class="col-md-4" id="btn-profil">
            <a href="profil.php" class="text-decoration-none">
                <div class="card p-3 text-center">
                    <h5 data-trad="btn_profil">Mon Profil</h5>
                    <p class="text-muted small">Modifier mes informations</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11/dist/js/shepherd.min.js"></script>
<script>
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
    text: '<strong>Bienvenue sur UpcycleConnect !</strong><br><br>On va vous faire une visite rapide du tableau de bord.',
    buttons: [
        { text: 'Passer', action: tour.cancel, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Commencer →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'annonce',
    text: '<strong>Déposer une annonce</strong><br>Donnez ou vendez un objet dont vous n\'avez plus besoin. Votre annonce sera validée par un admin avant publication.',
    attachTo: { element: '#btn-annonce', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'box',
    text: '<strong>Demander une box</strong><br>Déposez votre objet dans un casier physique près de chez vous. On vous envoie un code et un code-barres.',
    attachTo: { element: '#btn-box', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'score',
    text: '<strong>Mon Upcycling Score</strong><br>Chaque action compte ! Déposez des objets, gagnez des points et débloquez des badges éco-citoyens.',
    attachTo: { element: '#btn-score', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'planning',
    text: '<strong>Mon Planning</strong><br>Inscrivez-vous à des ateliers et formations autour du recyclage. Apprenez à donner une seconde vie aux objets !',
    attachTo: { element: '#btn-planning', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'prestations',
    text: '<strong>Prestations</strong><br>Découvrez les services proposés par nos artisans et salariés : restauration de meubles, formations privées, conseils personnalisés...',
    attachTo: { element: '#btn-prestations', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'forum',
    text: '<strong>Forum</strong><br>Échangez avec les autres membres de la communauté UpcycleConnect ! Posez vos questions, partagez vos astuces et trouvez de l\'inspiration.',
    attachTo: { element: '#btn-forum', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'portefeuille',
    text: '<strong>Mon portefeuille</strong><br>Quand vous vendez un objet, l\'argent arrive ici (moins 7% de commission UpcycleConnect). Vous pouvez suivre vos gains et retirer votre argent quand vous voulez.',
    attachTo: { element: '#btn-portefeuille', on: 'bottom' },
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'Suivant →', action: tour.next, classes: 'btn btn-sm btn-success' }
    ]
});

tour.addStep({
    id: 'fin',
    text: '<strong>Vous êtes prêt !</strong><br>Vous pouvez relancer ce tutoriel depuis votre profil à tout moment. Bonne expérience sur UpcycleConnect !',
    buttons: [
        { text: '← Retour', action: tour.back, classes: 'btn btn-sm btn-outline-secondary' },
        { text: 'C\'est parti !', action: tour.complete, classes: 'btn btn-sm btn-success' }
    ]
});

// On enregistre le cookie avec une expiration d'1 an pour que le tuto ne réapparaisse pas tout seul
function marquerTutoVu() {
    var dans1an = new Date();
    dans1an.setFullYear(dans1an.getFullYear() + 1);
    document.cookie = "tuto_vu=1; path=/; expires=" + dans1an.toUTCString();
}

tour.on('complete', marquerTutoVu);
tour.on('cancel', marquerTutoVu);

if (!getCookie('tuto_vu')) {
    tour.start();
}
</script>

</body>
</html>