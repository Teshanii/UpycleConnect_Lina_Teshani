<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}


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