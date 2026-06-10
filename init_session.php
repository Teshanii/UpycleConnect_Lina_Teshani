<?php
// init_session.php
session_start();

// On récupère email + mot de passe envoyés par connexion.php
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// On vérifie qu'on a bien reçu les identifiants
if (!$data || !isset($data['email']) || !isset($data['mdp'])) {
    http_response_code(400);
    echo json_encode(["error" => "Données invalides"]);
    exit;
}

// IMPORTANT : on ne fait PAS confiance au navigateur.
// On rappelle l'API Go nous-mêmes (côté serveur) pour vérifier le mot de passe.
// Comme ça personne ne peut se forger un faux rôle admin.
// Note : on utilise le nom du conteneur Docker "api" (pas localhost) car
// cet appel part du serveur PHP, pas du navigateur.
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

// Si l'API Go n'a pas validé (mauvais mdp, compte non vérifié, etc.)
if ($codeHttp !== 200) {
    http_response_code($codeHttp);
    echo $reponse; // on renvoie le message d'erreur de Go (ex: "compte non activé")
    exit;
}

// On crée la session UNIQUEMENT à partir de ce que Go a renvoyé (vérifié)
$user = json_decode($reponse, true);

session_regenerate_id(true); // sécurité : nouvel ID de session au login
$_SESSION['user_id']     = $user['id'];
$_SESSION['user_nom']    = $user['nom'];
$_SESSION['user_prenom'] = $user['pre'];
$_SESSION['user_role']   = $user['id_role'];
$_SESSION['user_email']  = $user['mail'];

http_response_code(200);
// On renvoie le rôle au JS pour qu'il sache où rediriger
echo json_encode(["message" => "Session initialisée", "id_role" => $user['id_role']]);
?>