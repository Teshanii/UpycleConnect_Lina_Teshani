<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$id_prestation = intval($_GET['id_prestation']);
$session_id    = $_GET['session_id'];
$user_id       = $_SESSION['user_id'];

$session = \Stripe\Checkout\Session::retrieve($session_id);
$ok = false;
$nom_prestation = '';

if ($session->payment_status === 'paid') {
    $montant = $session->amount_total / 100;

    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');

    
    $requete = $pdo->prepare("SELECT p.nom_prestation, COALESCE(u.abonnement,'gratuit') 
        FROM prestations p JOIN utilisateurs u ON p.id_createur = u.id_user 
        WHERE p.id_prestation = ?");
    $requete->execute([$id_prestation]);
    $ligne = $requete->fetch(PDO::FETCH_NUM);
    $nom_prestation = $ligne[0] ?: 'Prestation';
    $aboArtisan = $ligne[1];

    
    $taux = ($aboArtisan === 'premium') ? 0.03 : 0.07;
    $commission = round($montant * $taux, 2);

    
    $pdo->prepare("UPDATE prestations SET vendu = 1 WHERE id_prestation = ?")->execute([$id_prestation]);

    
    $pdo->prepare("INSERT INTO transactions (montant, reference_stripe, statut_paiement, id_user, type, commission) VALUES (?, ?, 'succeeded', ?, 'prestation', ?)")
        ->execute([$montant, $session->payment_intent, $user_id, $commission]);

    
    $ch = curl_init('http://api:8080/api/vente-prestation');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id_prestation' => $id_prestation, 'montant' => $montant]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

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
</head>
<body>
<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>
<div class="container mt-5 text-center">
    <?php if ($ok): ?>
        <div class="alert alert-success">
            <h4>Paiement confirmé !</h4>
            <p>Votre commande pour <strong><?= htmlspecialchars($nom_prestation) ?></strong> a bien été enregistrée.</p>
        </div>
        <a href="facture.php?ref=<?= $session->payment_intent ?>&montant=<?= $session->amount_total / 100 ?>&libelle=<?= urlencode($nom_prestation) ?>"
           class="btn btn-outline-success mt-2" target="_blank">Télécharger la facture</a>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Paiement non confirmé.</h4>
        </div>
    <?php endif; ?>
    <br>
    <a href="prestations.php" class="btn btn-success mt-3">← Retour au catalogue</a>
</div>
</body>
</html>