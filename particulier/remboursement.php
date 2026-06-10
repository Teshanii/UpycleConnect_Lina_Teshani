<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$data = json_decode(file_get_contents('php://input'), true);
$ref = $data['ref'];
$id_transaction = intval($data['id_transaction']);

header('Content-Type: application/json');

try {
    // Faire le remboursement via Stripe (l'argent revient sur la carte du client)
    $remboursement = \Stripe\Refund::create(['payment_intent' => $ref]);

    // Stripe renvoie 'succeeded' ou parfois 'pending' selon la banque, les deux sont OK
    if ($remboursement->status === 'succeeded' || $remboursement->status === 'pending') {
        // Mettre à jour le statut en BDD
        $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');
        $pdo->prepare("UPDATE transactions SET statut_paiement = 'refunded' WHERE id_transac = ?")
            ->execute([$id_transaction]);

        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Remboursement échoué.']);
    }
} catch (Exception $e) {
    // Si Stripe renvoie une erreur (déjà remboursé, paiement introuvable...)
    echo json_encode(['ok' => false, 'error' => 'Impossible de rembourser ce paiement.']);
}