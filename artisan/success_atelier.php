<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$id_event   = intval($_GET['id_event']);
$session_id = $_GET['session_id'];
$user_id    = $_SESSION['user_id'];

// Vérifier que Stripe confirme le paiement
$session = \Stripe\Checkout\Session::retrieve($session_id);
$ok = false;
$montant = 0;
$ref = '';
$titre_atelier = '';

if ($session->payment_status === 'paid') {
    $montant = $session->amount_total / 100;
    $ref = $session->payment_intent;

    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect;charset=utf8mb4', 'root', 'root');

    // Récupérer le titre de l'atelier
    $stmt = $pdo->prepare("SELECT titre FROM evenements WHERE id_event = ?");
    $stmt->execute([$id_event]);
    $titre_atelier = $stmt->fetchColumn() ?: 'Atelier';

    // Inscrire l'artisan à l'atelier via l'API Go
    $ch = curl_init('http://api:8080/api/inscriptions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id_user' => $user_id, 'id_event' => $id_event]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    // Sauvegarder la transaction (type atelier - l'argent va à la plateforme)
    $pdo->prepare("INSERT INTO transactions (montant, reference_stripe, statut_paiement, id_user, type) VALUES (?, ?, 'succeeded', ?, 'atelier')")
        ->execute([$montant, $ref, $user_id]);

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
            <p>Votre inscription à <strong><?= htmlspecialchars($titre_atelier) ?></strong> a bien été enregistrée.</p>
        </div>
        <a href="../particulier/facture.php?id_event=<?= $id_event ?>&ref=<?= $ref ?>&montant=<?= $montant ?>"
           class="btn btn-outline-success mt-2" target="_blank">
            Télécharger ma facture PDF
        </a>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Paiement non confirmé.</h4>
            <p>Veuillez réessayer.</p>
        </div>
    <?php endif; ?>
    <br>
    <a href="ateliers.php" class="btn btn-success mt-3">← Retour aux ateliers</a>
</div>
</body>
</html>