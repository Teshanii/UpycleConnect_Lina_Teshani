<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);


$data       = json_decode(file_get_contents('php://input'), true);
$id_demande = intval($data['id_demande']);
$prix       = intval($data['prix']); // en centimes
$titre      = htmlspecialchars($data['titre']);

// Créer la session Stripe
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'customer_email' => $_SESSION['user_email'],
    'line_items' => [[
        'price_data' => [
            'currency'     => 'eur',
            'product_data' => ['name' => $titre],
            'unit_amount'  => $prix,
        ],
        'quantity' => 1,
    ]],
    'mode'        => 'payment',
    // On passe l'id_demande dans l'URL de succès pour réserver après paiement
    'success_url' => BASE_URL . '/artisan/success_objet.php?id_demande=' . $id_demande . '&session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => BASE_URL . '/artisan/catalogue.php',
]);

header('Content-Type: application/json');
echo json_encode(['url' => $session->url]);