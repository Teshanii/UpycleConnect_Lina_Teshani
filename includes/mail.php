<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
function envoyerMail($destinataire, $sujet, $contenu_html) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // Identifiants lus depuis le .env (jamais commites)
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
        // On log l'erreur pour comprendre pourquoi le mail part pas
        error_log("ERREUR MAIL : " . $mail->ErrorInfo);
        return false;
    }
}