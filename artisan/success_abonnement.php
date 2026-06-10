<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ../connexion.php');
    exit;
}

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$userId     = $_SESSION['user_id'];
$session_id = $_GET['session_id'];

// On vérifie que Stripe confirme bien le paiement
$session = \Stripe\Checkout\Session::retrieve($session_id);
$ok = false;
$montant = 0;
$ref = '';

if ($session->payment_status === 'paid') {
    $montant = $session->amount_total / 100;
    $ref = $session->payment_intent;

    // 1. On passe l'artisan en Premium via l'API Go
    $ch = curl_init("http://api:8080/api/abonnement");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["id" => $userId]));
    curl_exec($ch);
    curl_close($ch);

    // 2. On enregistre la transaction (type 'abonnement')
    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');
    $pdo->prepare("INSERT INTO transactions (montant, reference_stripe, statut_paiement, id_user, type) VALUES (?, ?, 'succeeded', ?, 'abonnement')")
        ->execute([$montant, $ref, $userId]);

    $ok = true;
}
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

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width:500px;">
        <div class="card-body text-center">
            <?php if ($ok): ?>
                <h3 style="color:var(--primary-green);">Paiement réussi !</h3>
                <p class="my-3">Félicitations, votre compte est maintenant <strong>Premium</strong>. Vous avez accès aux statistiques avancées et aux alertes prioritaires.</p>
                <!-- Facture de l'abonnement (libellé générique, pas d'atelier) -->
                <a href="../particulier/facture.php?ref=<?= $ref ?>&montant=<?= $montant ?>&libelle=Abonnement+Premium+UpcycleConnect"
                   class="btn btn-outline-success mb-2" target="_blank">Télécharger ma facture PDF</a>
                <br>
                <a href="dashboard.php" class="btn btn-primary-upcycle mt-2">Retour à mon espace</a>
            <?php else: ?>
                <h3 class="text-danger">Paiement non confirmé</h3>
                <p class="my-3">Le paiement n'a pas pu être validé. Réessayez depuis la page abonnement.</p>
                <a href="abonnement.php" class="btn btn-secondary mt-2">Retour</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>