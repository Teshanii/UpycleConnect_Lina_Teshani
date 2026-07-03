<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
$dashboard = ($_SESSION['user_role'] == 3) ? '../artisan/dashboard.php' : 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon portefeuille | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="<?= $dashboard ?>">UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="<?= $dashboard ?>" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Mon portefeuille</h4>

    <div class="card shadow-sm mb-4" style="max-width:400px;">
        <div class="card-body text-center">
            <span class="text-muted small">Solde disponible</span>
            <h2 class="fw-bold my-2" style="color:var(--primary-green);"><span id="solde">0.00</span> €</h2>
            <button class="btn btn-outline-success btn-sm" onclick="ouvrirRetrait()">Retirer mon argent</button>
        </div>
    </div>

    <div class="alert alert-light border small" style="max-width:600px;">
        <?php if ($_SESSION['user_role'] == 3): ?>
            Une commission est déduite de chaque vente (7% en gratuit, 3% en Premium).
        <?php else: ?>
            Une commission de 7% est déduite de chaque vente.
        <?php endif; ?>
    </div>

    <div id="msg"></div>

    <h5 style="color:var(--primary-green);">Historique</h5>
    <div id="historique">
        <div class="text-center"><div class="spinner-border" style="color:var(--primary-green);"></div></div>
    </div>
</div>

<div class="modal fade" id="modalRetrait" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retirer mon argent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Solde disponible : <strong><span id="solde-modal">0.00</span> €</strong></p>
                <label class="form-label">Montant à retirer (€)</label>
                <input type="number" step="0.01" class="form-control" id="montant-retrait" placeholder="Ex: 20">
                <div id="modal-msg" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmerRetrait()">Confirmer le retrait</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var userId = <?php echo $_SESSION['user_id']; ?>;
var soldeActuel = 0;
var modalRetrait = new bootstrap.Modal(document.getElementById('modalRetrait'));

function charger() {
    fetch('/api/portefeuille?id_user=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            soldeActuel = data.solde || 0;
            document.getElementById('solde').innerText = soldeActuel.toFixed(2);

            var div = document.getElementById('historique');
            var mouvements = data.mouvements || [];

            if (mouvements.length === 0) {
                div.innerHTML = '<p class="text-muted">Aucun mouvement pour le moment.</p>';
                return;
            }

            var html = '';
            mouvements.forEach(function(m) {
                var couleur = m.montant >= 0 ? 'text-success' : 'text-danger';
                var signe = m.montant >= 0 ? '+' : '';

                var explication = '';
                if (m.type === 'vente_objet' || m.type === 'vente_prestation') {
                    explication = '<br><span class="text-muted small">Commission déduite du prix de vente.</span>';
                }

                html += '<div class="card mb-2 p-2">' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                    '<div>' +
                    '<strong>' + m.description + '</strong>' + explication + '<br>' +
                    '<span class="text-muted small">' + (m.date ? m.date.split('T')[0] : '') + '</span>' +
                    '</div>' +
                    '<div class="' + couleur + ' fw-bold">' + signe + m.montant.toFixed(2) + ' €</div>' +
                    '</div>' +
                    '</div>';
            });
            div.innerHTML = html;
        });
}
charger();

function ouvrirRetrait() {
    document.getElementById('solde-modal').innerText = soldeActuel.toFixed(2);
    document.getElementById('montant-retrait').value = '';
    document.getElementById('modal-msg').innerHTML = '';
    modalRetrait.show();
}

function confirmerRetrait() {
    var montant = parseFloat(document.getElementById('montant-retrait').value);

    if (!montant || montant <= 0) {
        document.getElementById('modal-msg').innerHTML = '<div class="alert alert-danger py-1">Entrez un montant valide.</div>';
        return;
    }
    if (montant > soldeActuel) {
        document.getElementById('modal-msg').innerHTML = '<div class="alert alert-danger py-1">Solde insuffisant.</div>';
        return;
    }

    fetch('/api/portefeuille', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_user: userId, montant: montant })
    }).then(function(res) {
        if (res.ok) {
            modalRetrait.hide();
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Retrait effectué !</div>';
            charger();
        } else {
            res.json().then(function(data) {
                document.getElementById('modal-msg').innerHTML = '<div class="alert alert-danger py-1">' + (data.error || 'Erreur.') + '</div>';
            });
        }
    });
}
</script>

</body>
</html>