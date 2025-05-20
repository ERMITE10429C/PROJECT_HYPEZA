<?php
error_log("Script démarré");
error_log("POST data: " . print_r($_POST, true));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // Vérifier si c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    // Charger l'autoloader de Composer
    $autoloadFile = 'vendor/autoload.php';
    if (!file_exists($autoloadFile)) {
        throw new Exception("Erreur de configuration : autoload.php non trouvé");
    }
    require $autoloadFile;

    // Récupérer et valider les données du formulaire
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    // Validation des champs
    if (!$name || !$email || !$date || !$time) {
        throw new Exception("Tous les champs requis doivent être remplis");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'adresse email n'est pas valide");
    }

    // Log des données reçues
    error_log("Données reçues : " . print_r($_POST, true));

    $mail = new PHPMailer(true);

    // Configuration SMTP avec debug
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer Debug: $str");
    };

    // Configuration du serveur
    $mail->isSMTP();
    $mail->Host = 'smtp.titan.email';
    $mail->SMTPAuth = true;
    $mail->Username = 'team@hypza.tech';
    $mail->Password = 'azerty@123';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Options SSL plus permissives pour le débogage
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Configuration de l'email
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
                                <p>Bonjour " . htmlspecialchars($name) . ",</p>
                                <p>Nous vous remercions d'avoir choisi HYPEZA pour votre costume sur mesure. Voici les détails de votre rendez-vous :</p>
                                
                                <div style='background-color: #f8f8f8; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                                    <p><strong>Date :</strong> " . htmlspecialchars($date) . "</p>
                                    <p><strong>Heure :</strong> " . htmlspecialchars($time) . "</p>
                                    <p><strong>Téléphone :</strong> " . htmlspecialchars($phone) . "</p>
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

    // Tentative d'envoi
    if(!$mail->send()) {
        throw new Exception("Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Email envoyé avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur d'envoi d'email : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>