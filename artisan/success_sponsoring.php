<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ../connexion.php');
    exit;
}

// On récupère l'id du projet à booster (passé dans l'URL par Stripe)
$idProjet = isset($_GET['id_projet']) ? intval($_GET['id_projet']) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement réussi | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container mt-5 text-center">
    <div class="card p-4 shadow-sm mx-auto" style="max-width:500px;">
        <h3 style="color:var(--primary-green);">Paiement réussi !</h3>
        <p class="text-muted">Activation de la mise en avant de votre projet...</p>
        <div id="msg"></div>
    </div>
</div>

<script>
var idProjet = <?php echo $idProjet; ?>;

// On active le sponsoring du projet via l'API
fetch('http://localhost:8080/api/sponsoriser/' + idProjet, { method: 'PUT' })
    .then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML =
                '<div class="alert alert-success">Votre projet est maintenant mis en avant pour 30 jours !</div>' +
                '<a href="mes_creations.php" class="btn btn-primary-upcycle">Retour à mes créations</a>';
        } else {
            document.getElementById('msg').innerHTML =
                '<div class="alert alert-danger">Erreur lors de l\'activation.</div>';
        }
    });
</script>

</body>
</html>