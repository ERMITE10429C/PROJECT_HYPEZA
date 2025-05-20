<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

    header('Content-Type: application/json');

    try {
        require 'vendor/autoload.php';

        // Récupérer et décoder les données JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON invalide: " . json_last_error_msg());
        }

        // Valider les champs requis
        $requiredFields = ['name', 'email', 'phone', 'date', 'time', 'notes'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Champ requis manquant: $field");
            }
        }

        $mail = new PHPMailer(true);

        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.titan.email';
        $mail->SMTPAuth = true;
        $mail->Username = 'team@hypza.tech';
        $mail->Password = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Configuration SSL
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true
            ]
        ];

        // Paramètres de l'email
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addAddress('team@hypza.tech', 'HYPEZA Team');
        $mail->addReplyTo($data['email'], $data['name']);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle demande de rendez-vous - Costume sur mesure';

        // Corps de l'email
        $emailBody = "
        <!DOCTYPE html>
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #C89B3C;'>Nouvelle demande de rendez-vous</h2>
            <p><strong>Client:</strong> {$data['name']}</p>
            <p><strong>Email:</strong> {$data['email']}</p>
            <p><strong>Téléphone:</strong> {$data['phone']}</p>
            <p><strong>Date souhaitée:</strong> {$data['date']}</p>
            <p><strong>Heure souhaitée:</strong> {$data['time']}</p>
            <p><strong>Remarques:</strong></p>
            <p>{$data['notes']}</p>
        </body>
        </html>";

        $mail->Body = $emailBody;

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Demande de rendez-vous envoyée avec succès']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Erreur lors de l'envoi: {$e->getMessage()}"]);
    }
}