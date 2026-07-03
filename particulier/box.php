<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 4) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demander une box | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <div class="d-flex align-items-center">
            <?php include 'includes/traductions.php'; ?>
            <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm" data-trad="nav_deconnexion">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);" data-trad="btn_retour">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);" data-trad="box_titre">Déposer un objet dans une box</h4>
    <p class="text-muted small" data-trad="box_sous_titre">Choisissez un objet à déposer et une box disponible près de chez vous.</p>

    <div id="msg"></div>

    <div class="card p-3 mb-4">
        <!-- Choix de l'objet à déposer -->
        <div class="mb-3">
            <label class="form-label" data-trad="box_label_objet">Objet à déposer</label>
            <select id="select-objet" class="form-select">
                <option value="">Chargement...</option>
            </select>
        </div>

        <!-- Filtre par ville -->
        <div class="mb-3">
            <label class="form-label" data-trad="box_label_ville">Filtrer les box par ville</label>
            <select id="filtre-ville" class="form-select" onchange="afficherBox()">
                <option value="" data-trad="box_toutes_villes">Toutes les villes</option>
            </select>
        </div>

        <!-- Liste des box disponibles -->
        <label class="form-label" data-trad="box_label_choix">Choisissez une box</label>
        <div id="liste-box" class="row g-3">
            <p class="text-muted" data-trad="box_chargement">Chargement des box...</p>
        </div>
    </div>

    <!-- Mes demandes en cours -->
    <h4 class="mt-4" style="color:var(--primary-green);" data-trad="box_mes_demandes">Mes demandes</h4>
    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>
    <div id="mes-demandes"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var toutesBox = [];
var boxChoisie = null;

// 1. On charge d'abord les demandes existantes pour savoir quels objets sont déjà engagés
function chargerObjets() {
    fetch('http://localhost:8080/api/demandes_box')
        .then(function(r) { return r.json(); })
        .then(function(demandes) {
            // Les annonces déjà engagées dans une demande active (pas refusée, pas récupérée)
            var annoncesEngagees = [];
            (demandes || []).forEach(function(d) {
                if (d.id_user === userId && d.statut !== 'refuse' && d.statut !== 'recupere' && d.id_annonce) {
                    annoncesEngagees.push(d.id_annonce);
                }
            });

            // Puis on charge les annonces validées du particulier
            fetch('http://localhost:8080/api/annonces?id_user=' + userId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var sel = document.getElementById('select-objet');
                    // On garde les annonces validées ET pas déjà engagées dans une demande
                    var annonces = (data || []).filter(function(a) {
                        return a.statut_validation === 1 && annoncesEngagees.indexOf(a.id) === -1;
                    });
                    if (annonces.length === 0) {
                        sel.innerHTML = '<option value="">Aucun objet disponible (déjà en dépôt ou non validé)</option>';
                        return;
                    }
                    var html = '<option value="">-- Choisir un objet --</option>';
                    annonces.forEach(function(a) {
                        html += '<option value="' + a.id_objet + '" data-annonce="' + a.id + '">' + a.titre + '</option>';
                    });
                    sel.innerHTML = html;
                });
        });
}
chargerObjets();

// 2. On charge les box avec leur nombre de casiers libres
fetch('http://localhost:8080/api/box')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        toutesBox = data || [];
        remplirFiltreVille();
        afficherBox();
    });

// On remplit le menu déroulant des villes (sans doublon)
function remplirFiltreVille() {
    var villes = [];
    toutesBox.forEach(function(b) {
        if (b.ville && villes.indexOf(b.ville) === -1) {
            villes.push(b.ville);
        }
    });
    var sel = document.getElementById('filtre-ville');
    villes.forEach(function(v) {
        sel.innerHTML += '<option value="' + v + '">' + v + '</option>';
    });
}

// On affiche les box (filtrées par ville si besoin)
function afficherBox() {
    var filtreVille = document.getElementById('filtre-ville').value;
    var div = document.getElementById('liste-box');

    var box = toutesBox;
    if (filtreVille) {
        box = box.filter(function(b) { return b.ville === filtreVille; });
    }

    if (box.length === 0) {
        div.innerHTML = '<p class="text-muted">Aucune box dans cette ville.</p>';
        return;
    }

    var html = '';
    box.forEach(function(b) {
        var pleine = b.casiers_libres <= 0;

        // Badge de disponibilité (vert si dispo, rouge si pleine)
        var badge = pleine
            ? '<span class="badge bg-danger">Complet</span>'
            : '<span class="badge bg-success">' + b.casiers_libres + ' casier(s) libre(s)</span>';

        // Carte grisée + bouton désactivé si pleine
        var style = pleine ? 'opacity:0.5;' : '';
        var bouton = pleine
            ? '<button class="btn btn-secondary btn-sm w-100" disabled>Box complète</button>'
            : '<button class="btn btn-primary-upcycle btn-sm w-100" onclick="choisirBox(' + b.id + ')">Choisir cette box</button>';

        html += '<div class="col-md-4">' +
            '  <div class="card h-100 p-3" style="' + style + '" id="box-' + b.id + '">' +
            '    <h6 style="color:var(--primary-green);">' + b.adresse + '</h6>' +
            '    <p class="text-muted small mb-1"> ' + (b.ville || '') + '</p>' +
            '    <div class="mb-2">' + badge + '</div>' +
            bouton +
            '  </div>' +
            '</div>';
    });
    div.innerHTML = html;
}

// Quand on choisit une box
function choisirBox(idBox) {
    boxChoisie = idBox;
    envoyerDemande();
}

// On envoie la demande de dépôt
function envoyerDemande() {
    var sel = document.getElementById('select-objet');
    var idObjet = sel.value;
    var option = sel.options[sel.selectedIndex];
    var idAnnonce = option ? option.dataset.annonce : null;

    if (!idObjet) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Choisissez d\'abord un objet à déposer.</div>';
        window.scrollTo(0, 0);
        return;
    }

    fetch('http://localhost:8080/api/demandes_box', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_user: userId,
            id_objet: parseInt(idObjet),
            id_box: boxChoisie,
            id_annonce: idAnnonce ? parseInt(idAnnonce) : null
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Demande envoyée ! Un administrateur va la valider et vous attribuer un casier avec un code d\'ouverture.</div>';
            window.scrollTo(0, 0);
            chargerDemandes();
            chargerObjets(); // on recharge le select pour retirer l'objet qu'on vient d'engager
        } else if (res.status === 409) {
            res.json().then(function(data) {
                document.getElementById('msg').innerHTML = '<div class="alert alert-warning">' + (data.error || 'Cet objet a déjà une demande en cours.') + '</div>';
                window.scrollTo(0, 0);
            });
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur lors de la demande.</div>';
        }
    });
}

// Marquer la demande comme "déposée"
function marquerDepose(idDemande) {
    if (!confirm("Confirmez-vous avoir déposé votre objet dans le casier ?")) return;
    fetch('http://localhost:8080/api/demandes_box/' + idDemande, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ statut: 'depose' })
    }).then(function(res) {
        if (res.ok) chargerDemandes();
    });
}

// Charger les demandes de l'utilisateur
function chargerDemandes() {
    fetch('http://localhost:8080/api/demandes_box')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('loader').style.display = 'none';
            var html = '';
            // On ne montre que les demandes actives (on masque récupérées et refusées)
            var mesDemandes = data ? data.filter(function(d) {
                return d.id_user === userId && d.statut !== 'recupere' && d.statut !== 'refuse';
            }) : [];

            if (mesDemandes.length === 0) {
                document.getElementById('mes-demandes').innerHTML = '<p class="text-muted">Vous n\'avez pas de demande en cours.</p>';
                return;
            }

            mesDemandes.forEach(function(d) {
                var badge;
                if (d.statut === 'valide') {
                    badge = '<span class="badge bg-success">Validée - à déposer</span>';
                } else if (d.statut === 'depose') {
                    badge = '<span class="badge bg-info text-dark">Objet déposé</span>';
                } else {
                    badge = '<span class="badge bg-warning text-dark">En attente</span>';
                }

                var codeSection = '';
                if ((d.statut === 'valide' || d.statut === 'depose') && d.code_ouverture) {
                    codeSection = '<div class="alert alert-success mt-2">' +
                        '<strong>Casier : ' + (d.numero_casier || 'N/A') + '</strong><br>' +
                        '<strong>Code d\'ouverture : ' + d.code_ouverture + '</strong><br>' +
                        '<strong>Code-barres à coller sur l\'objet :</strong><br>' +
                        '<svg class="code-barre" data-code="' + d.code_barre_scan + '"></svg><br>' +
                        '<small class="text-muted">Présentez le code pour ouvrir le casier et collez le code-barres sur votre objet.</small>' +
                        '</div>';
                }

                var boutonDepose = '';
                if (d.statut === 'valide') {
                    boutonDepose = '<button class="btn btn-sm btn-primary-upcycle mt-2" onclick="marquerDepose(' + d.id + ')">J\'ai déposé l\'objet</button>';
                }

                html += '<div class="card mb-3 p-3">' +
                    '<strong>' + (d.titre_annonce || d.description || 'Objet non précisé') + '</strong><br>' +
                    '<span class="text-muted small">Box : ' + (d.adresse_box || '-') + '</span><br>' +
                    '<span class="text-muted small">Date : ' + (d.date || '-') + '</span><br>' +
                    '<div class="mt-1">' + badge + '</div>' +
                    codeSection + boutonDepose +
                    '</div>';
            });

            document.getElementById('mes-demandes').innerHTML = html;

            // T3 : transformer chaque code en vrai code-barres visuel (l'artisan lit le numéro et le tape)
            document.querySelectorAll('.code-barre').forEach(function(el) {
                var code = el.getAttribute('data-code');
                if (code) { JsBarcode(el, code, { format: 'CODE128', height: 45, fontSize: 14, margin: 4 }); }
            });
        })
        .catch(function() {
            document.getElementById('loader').style.display = 'none';
            document.getElementById('mes-demandes').innerHTML = '<div class="alert alert-danger">Impossible de charger vos demandes.</div>';
        });
}

chargerDemandes();
</script>

</body>
</html>