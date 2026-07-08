<?php

session_start();


$json = file_get_contents('php://input');
$data = json_decode($json, true);


if (!$data || !isset($data['email']) || !isset($data['mdp'])) {
    http_response_code(400);
    echo json_encode(["error" => "Données invalides"]);
    exit;
}


$ch = curl_init("http://api:8080/api/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "email" => $data['email'],
    "mdp"   => $data['mdp']
]));

$reponse = curl_exec($ch);
$codeHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if ($codeHttp !== 200) {
    http_response_code($codeHttp);
    echo $reponse; // on renvoie le message d'erreur de Go (ex: "compte non activé")
    exit;
}


$user = json_decode($reponse, true);

session_regenerate_id(true); 
$_SESSION['user_id']     = $user['id'];
$_SESSION['user_nom']    = $user['nom'];
$_SESSION['user_prenom'] = $user['pre'];
$_SESSION['user_role']   = $user['id_role'];
$_SESSION['user_email']  = $user['mail'];

http_response_code(200);

echo json_encode(["message" => "Session initialisée", "id_role" => $user['id_role']]);
?>