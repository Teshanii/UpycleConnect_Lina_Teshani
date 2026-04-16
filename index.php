<?php
session_start();

// Si pas connecté, on redirige vers la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Redirection selon le rôle
switch ($_SESSION['user_role']) {
    case 1:
        header('Location: admin_backoffice/index.php');
        break;
    case 4:
        header('Location: particulier/dashboard.php');
        break;
    default:
        header('Location: connexion.php');
        break;
}
exit;
?>