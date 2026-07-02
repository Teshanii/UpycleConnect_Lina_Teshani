<?php
session_start();

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 1: header('Location: admin_backoffice/index.php'); exit;
        case 2: header('Location: salarie/dashboard.php'); exit;
        case 3: header('Location: artisan/dashboard.php'); exit;
        case 4: header('Location: particulier/dashboard.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect — Donnez une seconde vie à vos objets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #fff; }
        .hero {
            background: linear-gradient(135deg, var(--primary-green) 0%, #1f4d2c 100%);
            color: #fff; padding: 90px 0 100px 0; text-align: center;
        }
        .hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 18px; }
        .hero p { font-size: 1.2rem; opacity: 0.92; max-width: 600px; margin: 0 auto 30px auto; }
        .hero .btn { padding: 12px 30px; font-weight: 600; margin: 5px; }
        .btn-blanc { background:#fff; color:var(--primary-green); border:none; }
        .btn-blanc:hover { background:var(--accent-beige); color:#fff; }
        .btn-contour { background:transparent; color:#fff; border:2px solid #fff; }
        .btn-contour:hover { background:#fff; color:var(--primary-green); }
        .section { padding: 65px 0; }
        .section-titre { text-align:center; font-weight:800; color:var(--primary-green); margin-bottom:40px; }
        .etape-num {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--primary-green); color:#fff;
            font-size: 1.5rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
            margin: 0 auto 14px auto;
        }
        .carte-espace {
            border:none; border-radius:14px; padding:28px; height:100%; text-align:center;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06); transition: transform 0.2s;
        }
        .carte-espace:hover { transform: translateY(-6px); }
        .carte-espace .icone { font-size: 2.6rem; color: var(--primary-green); }
        .stats { background: var(--bg-light); }
        .stat-num { font-size: 2.8rem; font-weight:800; color:var(--primary-green); }
        .carte-event {
            border:none; border-radius:12px; overflow:hidden;
            box-shadow:0 3px 14px rgba(0,0,0,0.07); height:100%;
        }
        .carte-event .haut { background:var(--primary-green); color:#fff; padding:14px 18px; }
        .carte-event .corps { padding:18px; }
        footer { background:#1f2d24; color:#cfd8d2; padding:35px 0; text-align:center; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold fs-4" href="index.php"><i class="bi bi-recycle"></i> UpcycleConnect</a>
        <div class="d-flex align-items-center">
            <a href="galerie.php" class="btn btn-outline-light btn-sm me-2" data-trad="nav_galerie">Galerie</a>
            <?php include 'includes/traductions.php'; ?>
            <a href="connexion.php" class="btn btn-outline-light btn-sm mx-2" data-trad="nav_connexion">Connexion</a>
            <a href="inscription.php" class="btn btn-light btn-sm" style="color:var(--primary-green); font-weight:600;" data-trad="nav_inscription">Inscription</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <h1 data-trad="hero_titre">Donnez une seconde vie à vos objets</h1>
        <p data-trad="hero_sous_titre">La plateforme qui connecte les particuliers et les artisans pour transformer les objets oubliés en créations uniques.</p>
        <a href="inscription.php" class="btn btn-blanc" data-trad="hero_btn_inscription">Je m'inscris gratuitement</a>
        <a href="#comment" class="btn btn-contour" data-trad="hero_btn_comment">Comment ça marche</a>
    </div>
</section>


<section class="section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-6">
                <h2 style="color:var(--primary-green); font-weight:800;" data-trad="presentation_titre">L'upcycling, simplement</h2>
                <p class="text-muted" data-trad="presentation_texte">Transformez les objets destinés à la poubelle en créations uniques. Une palette devient une table, un tissu devient un sac.</p>
                <a href="inscription.php" class="btn btn-primary-upcycle" data-trad="presentation_btn">Rejoindre</a>
            </div>
            <div class="col-md-6">
                <div style="height:300px; border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.08);">
                    <img src="img/presentation.png" alt="Atelier d'upcycling" style="width:100%; height:100%; object-fit:cover;"
                         onerror="this.parentNode.style.background='var(--bg-light)'; this.style.display='none';">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="comment">
    <div class="container">
        <h2 class="section-titre" data-trad="comment_titre">Comment ça marche ?</h2>
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="etape-num">1</div>
                <h5 data-trad="etape1_titre">Vous déposez</h5>
                <p class="text-muted small" data-trad="etape1_texte">Une annonce ou un dépôt en box, avec un code unique.</p>
            </div>
            <div class="col-md-4">
                <div class="etape-num">2</div>
                <h5 data-trad="etape2_titre">Un artisan récupère</h5>
                <p class="text-muted small" data-trad="etape2_texte">Il repère votre objet dans le catalogue et le récupère.</p>
            </div>
            <div class="col-md-4">
                <div class="etape-num">3</div>
                <h5 data-trad="etape3_titre">L'objet renaît</h5>
                <p class="text-muted small" data-trad="etape3_texte">Transformé en création unique. Vous gagnez des points !</p>
            </div>
        </div>
    </div>
</section>

<section class="section stats">
    <div class="container">
        <h2 class="section-titre" data-trad="pourqui_titre">Pour qui ?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="carte-espace bg-white">
                    <div class="icone"><i class="bi bi-house-heart"></i></div>
                    <h5 class="mt-2" style="color:var(--primary-green);" data-trad="pourqui_particuliers_titre">Particuliers</h5>
                    <p class="text-muted small" data-trad="pourqui_particuliers_texte">Donnez ou vendez vos objets, suivez votre Upcycling Score et inscrivez-vous aux ateliers.</p>
                    <a href="inscription.php" class="btn btn-primary-upcycle btn-sm" data-trad="pourqui_btn">Je m'inscris</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte-espace bg-white">
                    <div class="icone"><i class="bi bi-hammer"></i></div>
                    <h5 class="mt-2" style="color:var(--primary-green);" data-trad="pourqui_artisans_titre">Artisans & Pros</h5>
                    <p class="text-muted small" data-trad="pourqui_artisans_texte">Trouvez votre matière première, valorisez vos créations et suivez votre impact écolo.</p>
                    <a href="inscription.php" class="btn btn-primary-upcycle btn-sm" data-trad="pourqui_btn2">Je m'inscris</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte-espace bg-white">
                    <div class="icone"><i class="bi bi-people"></i></div>
                    <h5 class="mt-2" style="color:var(--primary-green);" data-trad="pourqui_animateurs_titre">Nos animateurs</h5>
                    <p class="text-muted small" data-trad="pourqui_animateurs_texte">Notre équipe organise les ateliers, publie des conseils et anime la communauté.</p>
                    <span class="badge bg-light text-dark border" data-trad="pourqui_badge">Équipe UpcycleConnect</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-titre" data-trad="ateliers_titre">Nos prochains ateliers</h2>
        <div class="row g-4" id="events">
            <p class="text-center text-muted">Chargement...</p>
        </div>
    </div>
</section>

<section class="section stats">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="stat-num">+10</div>
                <p class="text-muted small" data-trad="stat_points">points par annonce</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-num">2</div>
                <p class="text-muted small" data-trad="stat_espaces">espaces ouverts</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-num">100%</div>
                <p class="text-muted small" data-trad="stat_revalorises">objets revalorisés</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-num">0€</div>
                <p class="text-muted small" data-trad="stat_gratuit">pour s'inscrire</p>
            </div>
        </div>
    </div>
</section>

<section class="hero" style="padding:55px 0;">
    <div class="container">
        <h1 style="font-size:2rem;" data-trad="cta_titre">Prêt à commencer ?</h1>
        <a href="inscription.php" class="btn btn-blanc mt-2" data-trad="cta_btn">Créer mon compte</a>
    </div>
</section>

<footer>
    <div class="container">
        <h5 class="text-white"><i class="bi bi-recycle"></i> UpcycleConnect</h5>
        <p class="small mb-2" data-trad="footer_slogan">L'upcycling intelligent — Paris, depuis 2026</p>
        <p class="small mb-0">
            <a href="galerie.php" class="text-white text-decoration-none me-3" data-trad="nav_galerie">Galerie</a>
            <a href="connexion.php" class="text-white text-decoration-none me-3" data-trad="nav_connexion">Connexion</a>
            <a href="inscription.php" class="text-white text-decoration-none" data-trad="nav_inscription">Inscription</a>
        </p>
    </div>
</footer>

<script>
function formaterDate(d) {
    if (!d) return 'Date à venir';
    var partie = d.replace('T', ' ').replace('Z', '');
    var bloc = partie.split(' ');
    var dateP = bloc[0].split('-');
    var heureP = bloc[1] ? bloc[1].split(':') : ['00', '00'];
    var mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    var jour = parseInt(dateP[2], 10);
    var nomMois = mois[parseInt(dateP[1], 10) - 1];
    return jour + ' ' + nomMois + ' ' + dateP[0] + ' à ' + heureP[0] + 'h' + heureP[1];
}

fetch('http://localhost:8080/api/evenements')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var maintenant = new Date();
        var aVenir = (data || []).filter(function(e) {
            return e.statut_validation === 1 && new Date(e.date) > maintenant;
        });
        aVenir.sort(function(a, b) { return new Date(a.date) - new Date(b.date); });
        aVenir = aVenir.slice(0, 3);

        var div = document.getElementById('events');
        if (aVenir.length === 0) {
            div.innerHTML = '<p class="text-center text-muted">Aucun atelier programmé pour le moment.</p>';
            return;
        }

        var html = '';
        aVenir.forEach(function(e) {
            var placesRestantes = e.place - e.nb_inscrits;
            var prix = e.prix === 0 ? 'Gratuit' : e.prix + '€';
            html += '<div class="col-md-4">' +
                '<div class="carte-event">' +
                '<div class="haut"><strong>' + e.titre + '</strong></div>' +
                '<div class="corps">' +
                '<p class="small text-muted mb-1"><i class="bi bi-calendar-event"></i> ' + formaterDate(e.date) + '</p>' +
                '<p class="small text-muted mb-1"><i class="bi bi-person"></i> ' + (e.anim || 'UpcycleConnect') + '</p>' +
                '<p class="small mb-2"><span class="badge bg-light text-dark border">' + prix + '</span> ' +
                '<span class="badge bg-light text-dark border">' + placesRestantes + ' places</span></p>' +
                '<a href="connexion.php" class="btn btn-primary-upcycle btn-sm w-100">M\'inscrire</a>' +
                '</div></div></div>';
        });
        div.innerHTML = html;
    })
    .catch(function() {
        document.getElementById('events').innerHTML = '<p class="text-center text-muted">Impossible de charger les événements.</p>';
    });
</script>

</body>
</html>