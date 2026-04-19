<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ../connexion.php?error=access_denied');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style_admin.css">
</head>
<body>

    <div class="sidebar">
        <h3>UpcycleConnect</h3>
        <p class="small text-muted">Espace Administration</p>
        <hr>
        <a href="index.php" class="active">Tableau de bord</a>
        <a href="utilisateurs.php">Utilisateurs</a>
        <a href="categories.php">Catégories</a>
        <a href="prestations.php">Prestations</a>
        <a href="evenements.php">Événements</a>
        <a href="annonces.php">Annonces</a>
        <a href="box.php">Box / Conteneurs</a>
        <a href="messages.php">Forum</a>
        <a href="finance.php">Finance</a>
        <a href="langues.php">Langues</a>
        <hr>
        <a href="../deconnexion.php" class="text-danger mt-5">Déconnexion</a>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tableau de bord</h2>
            <div class="badge bg-light text-dark p-2">Connecté en tant que : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>
        
        <p class="text-muted">Aperçu en temps réel de l'activité de la plateforme.</p>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-card bg-users">
                    <h5>Utilisateurs</h5>
                    <h2 id="nb-u">...</h2>
                    <a href="utilisateurs.php" class="small-link">Gérer &rarr;</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card bg-cats">
                    <h5>Catégories</h5>
                    <h2 id="nb-c">...</h2>
                    <a href="categories.php" class="small-link">Gérer &rarr;</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card bg-presta">
                    <h5>Prestations</h5>
                    <h2 id="nb-p">...</h2>
                    <a href="prestations.php" class="small-link">Gérer &rarr;</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card bg-events">
                    <h5>Événements</h5>
                    <h2 id="nb-e">...</h2>
                    <a href="evenements.php" class="small-link">Gérer &rarr;</a>
                </div>
            </div>
            
            </div>
    </div>

    <script>
        async function chargerStats() {
            const fetchLen = async (endpoint) => {
                try {
                    const res = await fetch(`http://localhost:8080/api/${endpoint}`);
                    if (!res.ok) return 0;
                    const data = await res.json();
                    return Array.isArray(data) ? data.length : 0;
                } catch (err) { return 0; }
            };

            document.getElementById('nb-u').innerText = await fetchLen('users');
            document.getElementById('nb-c').innerText = await fetchLen('categories');
            document.getElementById('nb-p').innerText = await fetchLen('prestations');
            document.getElementById('nb-e').innerText = await fetchLen('evenements');
            // ... charger les autres stats ici
        }

        chargerStats();
        setInterval(chargerStats, 60000); // Rafraîchissement toutes les minutes
    </script>
</body>
</html>