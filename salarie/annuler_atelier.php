<?php
session_start();
// Seul un salarié connecté peut annuler un atelier
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    http_response_code(403);
    exit;
}

require_once '../includes/mail.php';

// On récupère l'id de l'atelier envoyé par le JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id === 0) {
    http_response_code(400);
    exit;
}

function appelApi($url, $methode = 'GET') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methode);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $reponse = curl_exec($ch);
    curl_close($ch);
    return $reponse;
}


$titreAtelier = "votre atelier";
$tousEvents = json_decode(appelApi('http://api:8080/api/evenements'), true);
if (is_array($tousEvents)) {
    foreach ($tousEvents as $e) {
        if ($e['id'] == $id) {
            $titreAtelier = $e['titre'];
            break;
        }
    }
}


$inscrits = json_decode(appelApi('http://api:8080/api/inscrits-evenement/' . $id), true);

// On prévient chaque inscrit par email
if (is_array($inscrits)) {
    foreach ($inscrits as $inscrit) {
        $contenu = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:20px;'>
                <h2 style='color:#2d6a4f;'>Atelier annulé</h2>
                <p>Bonjour " . htmlspecialchars($inscrit['pre']) . ",</p>
                <p>Nous sommes désolés : l'atelier <strong>" . htmlspecialchars($titreAtelier) . "</strong>
                auquel vous étiez inscrit(e) a été annulé.</p>
                <p>N'hésitez pas à consulter notre planning pour découvrir d'autres ateliers.</p>
                <p>L'équipe UpcycleConnect</p>
            </div>";
        envoyerMail($inscrit['mail'], "Annulation : " . $titreAtelier, $contenu);
    }
}

appelApi('http://api:8080/api/evenements/' . $id, 'DELETE');

http_response_code(200);
echo json_encode(["message" => "Atelier annulé et inscrits prévenus."]);