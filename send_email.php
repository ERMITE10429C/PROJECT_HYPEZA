<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclure l'autoload de Composer
require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hypeza.test1@gmail.com'; // Ton email Gmail
    $mail->Password = 'kbfa wlby tjpf oqcq'; // Ton mot de passe d'application
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('hypeza.test1@gmail.com', 'Hypeza');
    $mail->addAddress('aminezakhir6@gmail.com', 'Client');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email avec PHPMailer et Composer';
    $mail->Body    = 'Ceci est un test avec PHPMailer installé via Composer.';
    $mail->AltBody = 'Version texte du test.';

    $mail->send();
    echo 'Message envoyé avec succès';
} catch (Exception $e) {
    echo "Erreur : {$mail->ErrorInfo}";
}
?>
