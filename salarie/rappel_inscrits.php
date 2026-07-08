<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    http_response_code(403);
    exit;
}

require_once '../includes/mail.php';

// On récupère l'id de l'atelier et le message envoyés par le JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$message = isset($data['message']) ? trim($data['message']) : '';
if ($id === 0 || $message === '') {
    http_response_code(400);
    exit;
}


function appelApi($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $reponse = curl_exec($ch);
    curl_close($ch);
    return $reponse;
}


$inscrits = json_decode(appelApi('http://api:8080/api/inscrits-evenement/' . $id), true);

// On envoie le rappel à chaque inscrit
if (is_array($inscrits)) {
    foreach ($inscrits as $inscrit) {
        $contenu = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:20px;'>
                <h2 style='color:#2d6a4f;'>Rappel de votre atelier</h2>
                <p>Bonjour " . htmlspecialchars($inscrit['pre']) . ",</p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <p>L'équipe UpcycleConnect</p>
            </div>";
        envoyerMail($inscrit['mail'], "Rappel - UpcycleConnect", $contenu);
    }
}

http_response_code(200);
echo json_encode(["message" => "Rappel envoyé."]);