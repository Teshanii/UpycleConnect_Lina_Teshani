<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }

require_once '../vendor/autoload.php';

$id_event  = isset($_GET['id_event']) ? intval($_GET['id_event']) : 0;
$ref       = htmlspecialchars($_GET['ref']);
$montant   = floatval($_GET['montant']);
$libelle   = isset($_GET['libelle']) ? htmlspecialchars($_GET['libelle']) : '';
$nom       = $_SESSION['user_nom'];
$prenom    = $_SESSION['user_prenom'];
$email     = $_SESSION['user_email'];
$date      = date('d/m/Y');

// On détermine le libellé de la ligne de facture
if ($id_event > 0) {
    // Cas atelier : on récupère le titre depuis la base
    $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root');
    $stmt = $pdo->prepare("SELECT titre FROM evenements WHERE id_event = ?");
    $stmt->execute([$id_event]);
    $ligne = $stmt->fetchColumn() ?: 'Atelier';
} else {
    // Cas abonnement / prestation / autre : on prend le libellé passé en paramètre
    $ligne = $libelle !== '' ? $libelle : 'Achat UpcycleConnect';
}

// Générer le PDF avec FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 20);

// En-tête
$pdf->SetTextColor(45, 106, 79); // vert UpcycleConnect
$pdf->Cell(0, 15, 'UpcycleConnect', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Plateforme de l\'upcycling intelligent', 0, 1, 'C');
$pdf->Ln(5);

// Ligne séparatrice
$pdf->SetDrawColor(45, 106, 79);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// Titre facture
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'FACTURE', 0, 1, 'C');
$pdf->Ln(5);

// Infos client
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Informations client :', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Nom : ' . $prenom . ' ' . $nom, 0, 1);
$pdf->Cell(0, 7, 'Email : ' . $email, 0, 1);
$pdf->Cell(0, 7, 'Date : ' . $date, 0, 1);
$pdf->Ln(5);

// Détail achat
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Detail de l\'achat :', 0, 1);
$pdf->SetFillColor(240, 248, 240);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(130, 10, $ligne, 1, 0, 'L', true);
$pdf->Cell(60, 10, number_format($montant, 2) . ' EUR', 1, 1, 'R', true);
$pdf->Ln(3);

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'TOTAL TTC', 1, 0, 'L');
$pdf->SetTextColor(45, 106, 79);
$pdf->Cell(60, 10, number_format($montant, 2) . ' EUR', 1, 1, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

// Référence paiement
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 7, 'Reference paiement : ' . $ref, 0, 1);
$pdf->Cell(0, 7, 'Paiement traite par Stripe', 0, 1);
$pdf->Ln(10);

// Pied de page
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, 'Merci pour votre confiance ! UpcycleConnect - contact@upcycle-connect.online', 0, 1, 'C');

// Télécharger le PDF
if (!isset($pdo)) { $pdo = new PDO('mysql:host=database;dbname=upcycle_connect', 'root', 'root'); }
$nomFichier = 'facture_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $ref) . '_' . time() . '.pdf';
$pdf->Output('F', __DIR__ . '/../documents/' . $nomFichier);
$stmt = $pdo->prepare("INSERT INTO documents (id_user, type, nom_fichier) VALUES (?, 'facture', ?)");
$stmt->execute([$_SESSION['user_id'], $nomFichier]);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="facture_' . $ref . '.pdf"');
readfile(__DIR__ . '/../documents/' . $nomFichier);
exit;