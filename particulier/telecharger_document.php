<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }
require_once '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;


$stmt = $pdo->prepare("SELECT nom_fichier FROM documents WHERE id_document = ? AND id_user = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    http_response_code(403);
    exit('Accès refusé : ce document ne vous appartient pas.');
}

$chemin = __DIR__ . '/../documents/' . $doc['nom_fichier'];
if (!file_exists($chemin)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $doc['nom_fichier'] . '"');
readfile($chemin);
exit;
