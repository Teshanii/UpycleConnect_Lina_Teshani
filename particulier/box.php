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
    <title>Demander une box | UpcycleConnect</title>
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

    <!-- Formulaire de demande -->
    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);">Demander une box</h4>
            <p class="text-muted small">Choisissez une de vos annonces validées et une box. Un admin validera et vous enverra un code pour ouvrir le casier.</p>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Choisir une annonce</label>
                <select class="form-select" id="id_annonce">
                    <option value="">Chargement...</option>
                </select>
                <small class="text-muted">Seules vos annonces validées et pas encore déposées apparaissent ici.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Choisir une box</label>
                <select class="form-select" id="id_box">
                    <option value="">Chargement...</option>
                </select>
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="envoyer()">ENVOYER LA DEMANDE</button>
        </div>
    </div>

    <!-- Mes demandes en cours -->
    <h4 class="mt-4" style="color:var(--primary-green);">Mes demandes</h4>
    <div id="loader" class="text-center mt-3">
        <div class="spinner-border" style="color:var(--primary-green);"></div>
    </div>
    <div id="mes-demandes"></div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;

// Charger les annonces validées de l'utilisateur, en excluant celles déjà en cours de dépôt
function chargerAnnonces() {
    // On récupère d'abord les demandes du user pour savoir quelles annonces sont déjà utilisées
    fetch('http://localhost:8080/api/demandes_box')
        .then(function(r) { return r.json(); })
        .then(function(demandes) {
            // On liste les id_annonce déjà engagées dans une demande active (pas refusée)
            var annoncesUtilisees = [];
            (demandes || []).forEach(function(d) {
                if (d.id_user === userId && d.statut !== 'refuse' && d.id_annonce) {
                    annoncesUtilisees.push(d.id_annonce);
                }
            });

            // Puis on charge les annonces validées du user
            fetch('http://localhost:8080/api/annonces?id_user=' + userId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var sel = document.getElementById('id_annonce');
                    sel.innerHTML = '';
                    // On garde les annonces validées ET pas déjà utilisées dans une demande active
                    var dispo = data ? data.filter(function(a) {
                        return a.statut_validation === 1 && annoncesUtilisees.indexOf(a.id) === -1;
                    }) : [];

                    if (dispo.length === 0) {
                        sel.innerHTML = '<option value="">Aucune annonce disponible. Publiez-en une ou attendez la validation.</option>';
                        return;
                    }
                    sel.innerHTML = '<option value="">-- Sélectionner une annonce --</option>';
                    dispo.forEach(function(a) {
                        sel.innerHTML += '<option value="' + a.id + '">' + a.titre + '</option>';
                    });
                });
        });
}
chargerAnnonces();

// Charger les box disponibles
fetch('http://localhost:8080/api/box')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var sel = document.getElementById('id_box');
        sel.innerHTML = '';
        if (!data || data.length === 0) {
            sel.innerHTML = '<option value="">Aucune box disponible</option>';
            return;
        }
        sel.innerHTML = '<option value="">-- Sélectionner une box --</option>';
        data.forEach(function(b) {
            sel.innerHTML += '<option value="' + b.id + '">' + b.adresse + '</option>';
        });
    });

function envoyer() {
    var idAnnonce = document.getElementById('id_annonce').value;
    var idBox = document.getElementById('id_box').value;

    if (!idAnnonce) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Choisissez une annonce.</div>';
        return;
    }
    if (!idBox) {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Choisissez une box.</div>';
        return;
    }

    fetch('http://localhost:8080/api/demandes_box', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_user: userId,
            id_annonce: parseInt(idAnnonce),
            id_box: parseInt(idBox)
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Demande envoyée ! L\'admin va vérifier et vous envoyer un code.</div>';
            document.getElementById('id_annonce').value = '';
            document.getElementById('id_box').value = '';
            chargerDemandes();
            chargerAnnonces(); // on recharge pour retirer l'annonce qu'on vient d'utiliser
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur lors de l\'envoi.</div>';
        }
    });
}

// Marquer la demande comme "déposée" (le particulier a mis l'objet dans le casier)
function marquerDepose(idDemande) {
    if (!confirm("Confirmez-vous avoir déposé votre objet dans le casier ?")) return;

    fetch('http://localhost:8080/api/demandes_box/' + idDemande, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ statut: 'depose' })
    }).then(function(res) {
        if (res.ok) {
            chargerDemandes();
        }
    });
}

// Charger les demandes de l'utilisateur
function chargerDemandes() {
    fetch('http://localhost:8080/api/demandes_box')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('loader').style.display = 'none';
            var html = '';

            var mesDemandes = data ? data.filter(function(d) { return d.id_user === userId; }) : [];

            if (mesDemandes.length === 0) {
                document.getElementById('mes-demandes').innerHTML = '<p class="text-muted">Vous n\'avez pas encore de demandes.</p>';
                return;
            }

            mesDemandes.forEach(function(d) {
                var badge;
                if (d.statut === 'valide') {
                    badge = '<span class="badge bg-success">Validée - à déposer</span>';
                } else if (d.statut === 'depose') {
                    badge = '<span class="badge bg-info text-dark">Objet déposé</span>';
                } else if (d.statut === 'recupere') {
                    badge = '<span class="badge bg-primary">Récupéré par un artisan</span>';
                } else if (d.statut === 'refuse') {
                    badge = '<span class="badge bg-danger">Refusée</span>';
                } else {
                    badge = '<span class="badge bg-warning text-dark">En attente</span>';
                }

                // Afficher le code et le casier si validé ou plus
                var codeSection = '';
                if ((d.statut === 'valide' || d.statut === 'depose') && d.code_ouverture) {
                    codeSection = '<div class="alert alert-success mt-2">' +
                        '<strong>Casier : ' + (d.numero_casier || 'N/A') + '</strong><br>' +
                        '<strong>Code d\'ouverture : ' + d.code_ouverture + '</strong><br>' +
                        '<strong>Code-barres à coller sur l\'objet : ' + d.code_barre_scan + '</strong><br>' +
                        '<small class="text-muted">Présentez le code pour ouvrir le casier et collez le code-barres sur votre objet.</small>' +
                        '</div>';
                }

                // Bouton "J'ai déposé" si statut validé
                var boutonDepose = '';
                if (d.statut === 'valide') {
                    boutonDepose = '<button class="btn btn-sm btn-primary-upcycle mt-2" onclick="marquerDepose(' + d.id + ')">J\'ai déposé l\'objet</button>';
                }

                // Afficher le motif si refusé
                var motifSection = '';
                if (d.statut === 'refuse' && d.motif_refus) {
                    motifSection = '<p class="text-danger small mt-1">Motif : ' + d.motif_refus + '</p>';
                }

                html += '<div class="card mb-3 p-3">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div class="w-100">' +
                    '<strong>' + (d.titre_annonce || d.description || 'Objet non précisé') + '</strong><br>' +
                    '<span class="text-muted small">Box : ' + (d.adresse_box || '-') + '</span><br>' +
                    '<span class="text-muted small">Date : ' + (d.date || '-') + '</span><br>' +
                    '<div class="mt-1">' + badge + '</div>' +
                    motifSection +
                    codeSection +
                    boutonDepose +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            document.getElementById('mes-demandes').innerHTML = html;
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