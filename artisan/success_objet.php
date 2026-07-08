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
$id_demande = intval($_GET['id_demande']);
$session_id = $_GET['session_id'];

$session = \Stripe\Checkout\Session::retrieve($session_id);
$ok = false;
$ref = '';
$montant = 0;

if ($session->payment_status === 'paid') {
    $montant = $session->amount_total / 100;
    $ref = $session->payment_intent;
    $commission = round($montant * 0.07, 2); // 7% pour UpcycleConnect

    
    $ch = curl_init('http://api:8080/api/acheter-objet');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $id_demande, 'id_artisan' => $userId]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $reponse = curl_exec($ch);
    curl_close($ch);

    // 2. On enregistre la transaction avec la commission (type 'objet')
    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');
    $pdo->prepare("INSERT INTO transactions (montant, reference_stripe, statut_paiement, id_user, type, commission) VALUES (?, ?, 'succeeded', ?, 'objet', ?)")
        ->execute([$montant, $ref, $userId, $commission]);

    $ok = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <?php if ($ok): ?>
   
    <meta http-equiv="refresh" content="3;url=mes_recuperations.php">
    <?php endif; ?>
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
                <p class="my-3">Votre objet est réservé. Vous allez être redirigé vers <strong>Mes récupérations</strong> pour récupérer votre code d'ouverture...</p>
                <a href="../particulier/facture.php?ref=<?= $ref ?>&montant=<?= $montant ?>&libelle=Achat+objet+UpcycleConnect"
                   class="btn btn-outline-success mb-2" target="_blank">Télécharger ma facture PDF</a>
                <br>
                <a href="mes_recuperations.php" class="btn btn-primary-upcycle mt-2">Voir mes récupérations</a>
            <?php else: ?>
                <h3 class="text-danger">Paiement non confirmé</h3>
                <p class="my-3">Le paiement n'a pas pu être validé.</p>
                <a href="catalogue.php" class="btn btn-secondary mt-2">Retour au catalogue</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>