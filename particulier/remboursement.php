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
    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect;charset=utf8mb4', 'root', 'root');

    // On récupère les infos de la transaction (type, date, utilisateur, atelier)
    $stmt = $pdo->prepare("SELECT type, id_user, date_transac, id_event FROM transactions WHERE id_transac = ?");
    $stmt->execute([$id_transaction]);
    $transac = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transac) {
        echo json_encode(['ok' => false, 'error' => 'Transaction introuvable.']);
        exit;
    }

    
    if ($transac['type'] === 'abonnement') {
        $dateAchat = new DateTime($transac['date_transac']);
        $aujourdhui = new DateTime();
        $joursEcoules = $aujourdhui->diff($dateAchat)->days;

        if ($joursEcoules > 14) {
            echo json_encode(['ok' => false, 'error' => 'Cet abonnement a été acheté il y a plus de 14 jours, il n\'est plus remboursable. Vous pouvez l\'annuler depuis votre profil.']);
            exit;
        }
    }

    // On fait le remboursement via Stripe (l'argent revient sur la carte du client)
    $remboursement = \Stripe\Refund::create(['payment_intent' => $ref]);

    // Stripe renvoie 'succeeded' ou parfois 'pending' selon la banque, les deux sont OK
    if ($remboursement->status === 'succeeded' || $remboursement->status === 'pending') {
        // On met à jour le statut en BDD
        $pdo->prepare("UPDATE transactions SET statut_paiement = 'refunded' WHERE id_transac = ?")
            ->execute([$id_transaction]);

        // Si c'était un abonnement, on coupe le Premium immédiatement
        // (l'artisan est remboursé, donc il ne doit plus avoir l'accès Premium)
        if ($transac['type'] === 'abonnement') {
            $pdo->prepare("UPDATE utilisateurs SET abonnement = 'gratuit', date_fin_abonnement = NULL, abonnement_annule = 0 WHERE id_user = ?")
                ->execute([$transac['id_user']]);
        }

        // Si c'était un atelier, on annule l'inscription et on libère la place
        if ($transac['type'] === 'atelier' && !empty($transac['id_event'])) {
            // On retrouve l'inscription de ce user à cet atelier
            $stmtInsc = $pdo->prepare("SELECT id_inscription FROM inscriptions WHERE id_user = ? AND id_event = ? LIMIT 1");
            $stmtInsc->execute([$transac['id_user'], $transac['id_event']]);
            $idInscription = $stmtInsc->fetchColumn();

            if ($idInscription) {
                // On appelle l'API Go qui supprime l'inscription ET libère la place (places_max + 1)
                $ch = curl_init('http://api:8080/api/inscriptions/' . $idInscription);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }
        }

        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Remboursement échoué.']);
    }
} catch (Exception $e) {
    // Si Stripe renvoie une erreur (déjà remboursé, paiement introuvable...)
    echo json_encode(['ok' => false, 'error' => 'Impossible de rembourser ce paiement.']);
}