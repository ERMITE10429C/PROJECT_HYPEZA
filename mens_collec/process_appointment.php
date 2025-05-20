<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoriser les requêtes CORS si nécessaire
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Logger les données reçues
error_log('Requête reçue : ' . file_get_contents('php://input'));

try {
    // Récupérer les données JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erreur de parsing JSON: ' . json_last_error_msg());
    }

    // Valider les données requises
    $required_fields = ['name', 'email', 'phone', 'date', 'time'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }

    // Valider l'email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'adresse email n'est pas valide");
    }

    // Charger PHPMailer
    require 'vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Configuration des destinataires
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addAddress($data['email'], $data['name']);
        $mail->addReplyTo('service-client@hypza.tech', 'Service Client HYPEZA');

        // Configuration du contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirmation de votre rendez-vous - HYPEZA';

        // Corps de l'email
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Confirmation de rendez-vous</h2>
                <p>Bonjour {$data['name']},</p>
                <p>Votre rendez-vous a été programmé pour le {$data['date']} à {$data['time']}.</p>
                <p>Détails :</p>
                <ul>
                    <li>Téléphone : {$data['phone']}</li>
                    <li>Email : {$data['email']}</li>
                </ul>
                <p>Notes : {$data['notes']}</p>
                <p>Merci de nous faire confiance.</p>
                <p>L'équipe HYPEZA</p>
            </body>
            </html>
        ";

        $mail->send();
        echo json_encode([
            'success' => true,
            'message' => 'Email envoyé avec succès'
        ]);

    } catch (Exception $e) {
        error_log("Erreur PHPMailer : " . $e->getMessage());
        throw new Exception("Erreur lors de l'envoi de l'email : " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>