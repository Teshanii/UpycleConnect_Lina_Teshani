<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// vendor/ est à la racine du projet, on remonte d'un niveau depuis includes/
require_once __DIR__ . '/../vendor/autoload.php';

// Fonction centrale pour envoyer un mail
// On l'appelle depuis n'importe quel fichier PHP avec envoyerMail(...)
function envoyerMail($destinataire, $sujet, $contenu_html) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chellalalina2@gmail.com';
        $mail->Password   = 'irzh ijix puog sysz';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('chellalalina2@gmail.com', 'UpcycleConnect');
        $mail->addAddress($destinataire);
        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $contenu_html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}