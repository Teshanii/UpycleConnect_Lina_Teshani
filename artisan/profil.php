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
    <title>Mon Profil | UpcycleConnect</title>
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

    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);">Mon Profil</h4>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom">
            </div>
            <div class="mb-3">
                <label class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="email">
            </div>

            <!-- Score upcycling -->
            <div class="mb-3 p-3 rounded" style="background-color:#f0f7f0;">
                <span class="text-muted small">Mon Upcycling Score</span>
                <div class="d-flex align-items-center justify-content-between">
                    <span id="score-affichage" class="fw-bold fs-4" style="color:var(--primary-green);">...</span>
                    <a href="score.php" class="btn btn-sm btn-outline-success">Voir le détail</a>
                </div>
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="sauvegarder()">SAUVEGARDER</button>

            <hr class="mt-4">
            <h5 style="color:var(--primary-green);">Changer le mot de passe</h5>
            <div id="msg-mdp"></div>

            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="nouveau-mdp" placeholder="Minimum 6 caractères">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm-mdp">
            </div>
            <button class="btn btn-outline-secondary w-100" onclick="changerMdp()">CHANGER LE MOT DE PASSE</button>

            <hr class="mt-4">

            <!-- Mon abonnement -->
            <h5 style="color:var(--primary-green);">Mon abonnement</h5>
            <div id="bloc-abonnement">
                <div class="text-center"><div class="spinner-border spinner-border-sm" style="color:var(--primary-green);"></div></div>
            </div>

            <hr class="mt-4">

            <!-- Mes paiements -->
            <h5 style="color:var(--primary-green);">Mes paiements</h5>
            <div id="mes-paiements">
                <div class="text-center"><div class="spinner-border spinner-border-sm" style="color:var(--primary-green);"></div></div>
            </div>

            <hr class="mt-4">
            <button class="btn btn-outline-secondary w-100 mt-2" onclick="relancerTuto()">Revoir le tutoriel</button>

            <hr>
            <button class="btn btn-outline-danger w-100 mt-2" onclick="supprimerCompte()">Supprimer mon compte</button>
        </div>
    </div>
</div>

<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var userRoleId = <?php echo $_SESSION['user_role'] ?? 3; ?>;
var userData = null;

// Charger les infos du profil
fetch('http://localhost:8080/api/users')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        userData = data.find(function(u) { return u.id === userId; });
        if (userData) {
            document.getElementById('nom').value = userData.nom;
            document.getElementById('prenom').value = userData.pre;
            document.getElementById('email').value = userData.mail;
            document.getElementById('score-affichage').innerText = userData.score_upcycling + ' pts';
        }
    });

// Charger l'abonnement
function chargerAbonnement() {
    fetch('http://localhost:8080/api/abonnement?id_user=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('bloc-abonnement');

            if (data.abonnement !== 'premium') {
                // Gratuit : on propose de passer Premium
                div.innerHTML = '<div class="p-3 rounded" style="background-color:#f0f7f0;">' +
                    '<p class="mb-2">Vous êtes en offre <strong>Gratuite</strong>.</p>' +
                    '<a href="abonnement.php" class="btn btn-primary-upcycle btn-sm">Passer Premium</a>' +
                    '</div>';
                return;
            }

            // Premium : on affiche la date de fin
            var dateFin = data.date_fin ? data.date_fin.split('T')[0].split(' ')[0] : '';

            if (data.abonnement_annule == 1) {
                // Déjà annulé : Premium jusqu'à la date, pas de renouvellement
                div.innerHTML = '<div class="alert alert-warning mb-0">' +
                    '<strong>Premium jusqu\'au ' + dateFin + '</strong><br>' +
                    '<small>Votre abonnement ne sera pas renouvelé. Vous repasserez en Gratuit après cette date.</small>' +
                    '</div>';
            } else {
                // Premium actif : on propose d'annuler
                div.innerHTML = '<div class="alert alert-success mb-2">' +
                    '<strong>Premium actif</strong> jusqu\'au ' + dateFin +
                    '</div>' +
                    '<button class="btn btn-outline-danger btn-sm w-100" onclick="annulerAbonnement()">Annuler mon abonnement</button>';
            }
        });
}
chargerAbonnement();

// Annuler l'abonnement (coupe le renouvellement, garde l'accès jusqu'à la date de fin)
function annulerAbonnement() {
    if (!confirm("Annuler votre abonnement Premium ? Vous garderez l'accès jusqu'à la fin de la période déjà payée, sans renouvellement.")) return;

    fetch('http://localhost:8080/api/abonnement', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId, abonnement: 'annuler' })
    }).then(function(res) {
        if (res.ok) {
            chargerAbonnement(); // on recharge le bloc pour montrer le nouvel état
        } else {
            alert("Erreur lors de l'annulation.");
        }
    });
}

// Charger les transactions du user
fetch('http://localhost:8080/api/transactions?id_user=' + userId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var div = document.getElementById('mes-paiements');

        if (!data || data.length === 0) {
            div.innerHTML = '<p class="text-muted small">Aucun paiement effectué.</p>';
            return;
        }

        var html = '';
        data.forEach(function(t) {
            var badge = t.statut === 'succeeded'
                ? '<span class="badge bg-success">Payé</span>'
                : t.statut === 'refunded'
                ? '<span class="badge bg-warning text-dark">Remboursé</span>'
                : '<span class="badge bg-secondary">' + t.statut + '</span>';

            // On traduit le type en libellé lisible
            var libelleType;
            if (t.type === 'atelier') {
                libelleType = 'Atelier';
            } else if (t.type === 'objet') {
                libelleType = 'Objet acheté';
            } else if (t.type === 'abonnement') {
                libelleType = 'Abonnement Premium';
            } else if (t.type === 'prestation') {
                libelleType = 'Prestation';
            } else {
                libelleType = 'Paiement';
            }

            // Remboursement autorisé seulement pour les paiements à la plateforme (abonnement, atelier)
            var actionRemboursement = '';
            if (t.statut === 'succeeded') {
                if (t.type === 'abonnement' || t.type === 'atelier') {
                    actionRemboursement = '<button class="btn btn-outline-warning btn-sm ms-1" onclick="demanderRemboursement(\'' + t.ref_stripe + '\', ' + t.id + ')">Remboursement</button>';
                } else {
                    actionRemboursement = '<span class="text-muted small ms-2">Non remboursable en ligne</span>';
                }
            }

            html += '<div class="card mb-2 p-2">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                '<div>' +
                '<span class="badge bg-light text-dark border mb-1">' + libelleType + '</span><br>' +
                '<strong>' + t.montant.toFixed(2) + ' €</strong> ' + badge + '<br>' +
                '<span class="text-muted small">' + (t.date ? t.date.split('T')[0] : '') + ' — Réf: ' + t.ref_stripe + '</span>' +
                '</div>' +
                '<div>' +
                '<a href="../particulier/facture.php?ref=' + t.ref_stripe + '&montant=' + t.montant + '&libelle=Achat+UpcycleConnect" target="_blank" class="btn btn-outline-success btn-sm">📄 Facture</a>' +
                actionRemboursement +
                '</div>' +
                '</div>' +
                '</div>';
        });

        div.innerHTML = html;
    })
    .catch(function() {
        document.getElementById('mes-paiements').innerHTML = '<p class="text-muted small">Impossible de charger les paiements.</p>';
    });

function demanderRemboursement(ref, idTransaction) {
    if (confirm('Demander un remboursement pour ce paiement ? L\'argent sera recrédité sur votre carte bancaire.')) {
        fetch('../particulier/remboursement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ref: ref, id_transaction: idTransaction })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                alert('Remboursement effectué ! L\'argent sera recrédité sur votre carte sous quelques jours.');
                location.reload();
            } else {
                alert(data.error || 'Erreur lors de la demande.');
            }
        });
    }
}

function sauvegarder() {
    fetch('http://localhost:8080/api/users/' + userId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: document.getElementById('nom').value,
            pre: document.getElementById('prenom').value,
            mail: document.getElementById('email').value,
            id_role: userRoleId,
            est_actif: 1,
            score_upcycling: userData ? userData.score_upcycling : 0,
            est_verifie: userData ? userData.est_verifie : 1
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Profil mis à jour !</div>';
            setTimeout(function() { document.getElementById('msg').innerHTML = ''; }, 3000);
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur lors de la mise à jour.</div>';
        }
    });
}

function changerMdp() {
    var nouveau = document.getElementById('nouveau-mdp').value;
    var confirme = document.getElementById('confirm-mdp').value;

    if (!nouveau) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Entrez un nouveau mot de passe.</div>';
        return;
    }
    if (nouveau.length < 6) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Le mot de passe doit faire au moins 6 caractères.</div>';
        return;
    }
    if (nouveau !== confirme) {
        document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
        return;
    }

    fetch('http://localhost:8080/api/users/' + userId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom: document.getElementById('nom').value,
            pre: document.getElementById('prenom').value,
            mail: document.getElementById('email').value,
            id_role: userRoleId,
            est_actif: 1,
            score_upcycling: userData ? userData.score_upcycling : 0,
            est_verifie: userData ? userData.est_verifie : 1,
            mdp: nouveau
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-success">Mot de passe changé !</div>';
            document.getElementById('nouveau-mdp').value = '';
            document.getElementById('confirm-mdp').value = '';
            setTimeout(function() { document.getElementById('msg-mdp').innerHTML = ''; }, 3000);
        } else {
            document.getElementById('msg-mdp').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}

function supprimerCompte() {
    if (confirm("Supprimer votre compte ? Cette action est irréversible.")) {
        if (confirm("Êtes-vous vraiment sûr ? Toutes vos données seront supprimées.")) {
            fetch('http://localhost:8080/api/users/' + userId, { method: 'DELETE' })
                .then(function(res) {
                    if (res.ok) {
                        window.location.href = '../connexion.php?logout=1';
                    } else {
                        alert("Impossible de supprimer le compte.");
                    }
                });
        }
    }
}

function relancerTuto() {
    document.cookie = "tuto_vu_artisan=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
    window.location.href = 'dashboard.php';
}
</script>

</body>
</html>