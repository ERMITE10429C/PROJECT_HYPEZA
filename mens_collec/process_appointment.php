<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Vérifier si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    try {
        require 'vendor/autoload.php';

        // Récupérer les données du formulaire
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $notes = $_POST['notes'] ?? '';

        // Validation des champs requis
        if (empty($name) || empty($email) || empty($date) || empty($time)) {
            throw new Exception("Veuillez remplir tous les champs obligatoires");
        }

        // Créer l'instance PHPMailer
        $mail = new PHPMailer(true);

        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.titan.email';
        $mail->SMTPAuth = true;
        $mail->Username = 'team@hypza.tech';
        $mail->Password = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Options SSL
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true
            ]
        ];

        // Paramètres de l'email
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('service-client@hypza.tech', 'Service Client HYPEZA');

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre rendez-vous - HYPEZA Custom Suit';

        // Corps de l'email
        $emailBody = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmation de rendez-vous</title>
        </head>
        <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9;'>
            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <td align='center' style='padding: 20px;'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 6px;'>
                            <tr>
                                <td align='center' style='background-color: #000000; padding: 30px; border-radius: 6px 6px 0 0;'>
                                    <h1 style='color: #C89B3C; margin: 0; font-size: 32px;'>HYPEZA</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 30px;'>
                                    <p>Bonjour {$name},</p>
                                    <p>Nous vous remercions d'avoir choisi HYPEZA pour votre costume sur mesure. Voici les détails de votre rendez-vous :</p>
                                    
                                    <div style='background-color: #f8f8f8; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                                        <p><strong>Date :</strong> {$date}</p>
                                        <p><strong>Heure :</strong> {$time}</p>
                                        <p><strong>Téléphone :</strong> {$phone}</p>
                                    </div>
                                    
                                    <p>Notre équipe vous contactera prochainement pour confirmer votre rendez-vous.</p>
                                    
                                    <p>Pour toute question, n'hésitez pas à nous contacter à <a href='mailto:service-client@hypza.tech' style='color: #C89B3C;'>service-client@hypza.tech</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $mail->Body = $emailBody;

        // Envoyer l'email
        $mail->send();

        echo json_encode(['success' => true, 'message' => 'Email envoyé avec succès']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Erreur lors de l'envoi de l'email: " . $e->getMessage()]);
    }
    exit;
}
?>