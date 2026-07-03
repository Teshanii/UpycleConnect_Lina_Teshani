<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../connexion.php'); exit; }
require_once '../includes/db.php';

$stmt = $pdo->prepare("SELECT id_document, type, nom_fichier, date_creation FROM documents WHERE id_user = ? ORDER BY date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes documents | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php">UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>
    <h4 class="mt-3" style="color:var(--primary-green);">Mes documents</h4>
    <p class="text-muted small">Retrouvez et téléchargez vos factures et attestations.</p>

    <?php if (count($docs) === 0): ?>
        <p class="text-muted">Vous n'avez encore aucun document.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($docs as $d): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <?php if ($d['type'] === 'facture'): ?>
                            <span class="badge bg-primary me-2">Facture</span>
                        <?php elseif ($d['type'] === 'attestation'): ?>
                            <span class="badge bg-success me-2">Attestation</span>
                        <?php else: ?>
                            <span class="badge bg-secondary me-2">Document</span>
                        <?php endif; ?>
                        <span class="text-muted small">Ajouté le <?php echo date('d/m/Y à H:i', strtotime($d['date_creation'])); ?></span>
                    </div>
                    <a class="btn btn-outline-success btn-sm" href="telecharger_document.php?id=<?php echo (int)$d['id_document']; ?>">Télécharger</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>
