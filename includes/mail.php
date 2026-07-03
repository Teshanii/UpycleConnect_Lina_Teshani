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
        // Identifiants lus depuis le .env (jamais commités)
        $mail->Username   = getenv('GMAIL_USER');
        $mail->Password   = getenv('GMAIL_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(getenv('GMAIL_USER'), 'UpcycleConnect');
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