<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// On récupère les données envoyées par le JS
$data      = json_decode(file_get_contents('php://input'), true);
$prix      = intval($data['prix']); // en centimes (10000 = 100€)
$idProjet  = intval($data['id_projet']);

// Créer la session Stripe
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'customer_email' => $_SESSION['user_email'],
    'line_items' => [[
        'price_data' => [
            'currency'     => 'eur',
            'product_data' => ['name' => 'Mise en avant projet UpcycleConnect (30 jours)'],
            'unit_amount'  => $prix,
        ],
        'quantity' => 1,
    ]],
    'mode'        => 'payment',
    // On passe l'id du projet dans l'URL de retour pour l'activer après paiement
    'success_url' => 'http://localhost/artisan/success_sponsoring.php?id_projet=' . $idProjet . '&session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => 'http://localhost/artisan/mes_creations.php',
]);

header('Content-Type: application/json');
echo json_encode(['url' => $session->url]);