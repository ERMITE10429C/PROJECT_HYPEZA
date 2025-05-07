<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Vérification OpenSSL
        if (!extension_loaded('openssl')) {
            error_log("OpenSSL n'est pas activé");
            return false;
        }

        // Debug détaillé
        $mail->SMTPDebug = 3;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hypeza.test1@gmail.com';
        $mail->Password = 'kbfa wlby tjpf oqcq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('hypeza.test1@gmail.com', 'HYPEZA');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erreur détaillée : " . $e->getMessage());
        error_log("Trace complète : " . $e->getTraceAsString());
        return false;
    }
}
?>