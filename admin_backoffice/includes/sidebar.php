<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h3>UpcycleConnect</h3>
    <p class="small text-muted">Espace Administration</p>
    <hr>
    
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Tableau de bord</a>
    <a href="utilisateurs.php" class="<?= $current_page == 'utilisateurs.php' ? 'active' : '' ?>">Utilisateurs</a>
    <a href="categories.php" class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">Catégories</a>
    <a href="prestations.php" class="<?= $current_page == 'prestations.php' ? 'active' : '' ?>">Prestations</a>
    <a href="evenements.php" class="<?= $current_page == 'evenements.php' ? 'active' : '' ?>">Événements</a>
    <a href="annonces.php" class="<?= $current_page == 'annonces.php' ? 'active' : '' ?>">Annonces</a>
    <a href="demandes_box.php" class="<?= $current_page == 'demandes_box.php' ? 'active' : '' ?>">Demandes de Box</a>
    <a href="box.php" class="<?= $current_page == 'box.php' ? 'active' : '' ?>">Box / Conteneurs</a>
    <a href="messages.php" class="<?= $current_page == 'messages.php' ? 'active' : '' ?>">Forum</a>
    <a href="finance.php" class="<?= $current_page == 'finance.php' ? 'active' : '' ?>">Finance</a>
    <a href="langues.php" class="<?= $current_page == 'langues.php' ? 'active' : '' ?>">Langues</a>
    
    <hr>
    <div class="mt-auto">
        <p class="small text-light">Connecté : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></p>
        <a href="../deconnexion.php" class="text-danger">Déconnexion</a>
    </div>
</div>