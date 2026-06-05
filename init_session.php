<?php
// init_session.php
session_start();

// On récupère les données envoyées en JSON par le JavaScript de connexion.php
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data && isset($data['id'])) {
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['user_nom'] = $data['nom'];
    $_SESSION['user_prenom'] = $data['pre'];
    $_SESSION['user_role'] = $data['id_role'];
    $_SESSION['user_email']  = $data['mail'];
    // On répond au JavaScript que tout est OK
    http_response_code(200);
    echo json_encode(["message" => "Session initialisée"]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Données invalides"]);
}
?>