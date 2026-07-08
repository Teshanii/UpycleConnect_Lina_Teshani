<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../connexion.php');
    exit;
}
require_once '../vendor/autoload.php';

$id_event = isset($_GET['id_event']) ? intval($_GET['id_event']) : 0;
$id_user  = isset($_GET['id_user']) ? intval($_GET['id_user']) : 0;

$pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');


$stmt = $pdo->prepare("SELECT titre, date_debut, id_animateur FROM evenements WHERE id_event = ?");
$stmt->execute([$id_event]);
$atelier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$atelier || $atelier['id_animateur'] != $_SESSION['user_id']) {
    http_response_code(403);
    exit('Accès refusé : cet atelier ne vous appartient pas.');
}


$stmt = $pdo->prepare("SELECT u.nom, u.prenom FROM inscriptions i JOIN utilisateurs u ON i.id_user = u.id_user WHERE i.id_event = ? AND i.id_user = ?");
$stmt->execute([$id_event, $id_user]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    http_response_code(404);
    exit('Ce participant n\'est pas inscrit à cet atelier.');
}

$dateAtelier = $atelier['date_debut'] ? date('d/m/Y', strtotime($atelier['date_debut'])) : '';


$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(45, 106, 79);
$pdf->Cell(0, 15, 'UpcycleConnect', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Plateforme de l\'upcycling intelligent', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetDrawColor(45, 106, 79);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(12);

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'ATTESTATION DE PARTICIPATION', 0, 1, 'C');
$pdf->Ln(12);

$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, 'Nous attestons que ' . $participant['prenom'] . ' ' . $participant['nom'] . ' a participe a l\'atelier suivant :');
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(0, 8, $atelier['titre'], 0, 1);
$pdf->SetFont('Arial', '', 12);
if ($dateAtelier !== '') {
    $pdf->Cell(0, 8, 'Date : ' . $dateAtelier, 0, 1);
}
$pdf->Ln(20);

$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 7, 'Fait le ' . date('d/m/Y'), 0, 1);
$pdf->Cell(0, 7, 'L\'equipe UpcycleConnect', 0, 1);


$nomFichier = 'attestation_' . $id_user . '_' . $id_event . '_' . time() . '.pdf';
$pdf->Output('F', __DIR__ . '/../documents/' . $nomFichier);
$stmt = $pdo->prepare("INSERT INTO documents (id_user, type, nom_fichier) VALUES (?, 'attestation', ?)");
$stmt->execute([$id_user, $nomFichier]);

header('Content-Type: application/pdf');
readfile(__DIR__ . '/../documents/' . $nomFichier);
exit;
