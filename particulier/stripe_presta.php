<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$data = json_decode(file_get_contents('php://input'), true);
$id_prestation = intval($data['id_prestation']);
$prix          = intval($data['prix']);
$nom           = htmlspecialchars($data['nom']);

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'customer_email' => $_SESSION['user_email'],
    'line_items' => [[
        'price_data' => [
            'currency'     => 'eur',
            'product_data' => ['name' => $nom],
            'unit_amount'  => $prix,
        ],
        'quantity' => 1,
    ]],
    'mode'        => 'payment',
    'success_url' => BASE_URL . '/particulier/success_prestation.php?id_prestation=' . $id_prestation . '&session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => BASE_URL . '/particulier/prestations.php',
]);

header('Content-Type: application/json');
echo json_encode(['url' => $session->url]);