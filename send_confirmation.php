<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hypeza.test1@gmail.com';
        $mail->Password = 'kbfa wlby tjpf oqcq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('hypeza.test1@gmail.com', 'HYPEZA');
        $mail->addAddress($data['email']);

        $mail->isHTML(true);
        $mail->Subject = '🛍️ Confirmation de votre commande HYPEZA';

        $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <!-- En-tête -->
            <div style='background-color: #000000; padding: 20px; text-align: center; margin-bottom: 30px;'>
                <h1 style='color: rgb(200,155,60); margin: 0; font-size: 32px; letter-spacing: 2px;'>HYPEZA</h1>
            </div>

            <!-- Contenu principal -->
            <div style='padding: 0 20px; color: #333333;'>
                <p style='font-size: 16px; color: #666666;'>Bonjour {$data['firstName']} {$data['lastName']},</p>
                
                <p style='font-size: 16px; line-height: 1.5; margin: 20px 0;'>
                    Nous vous remercions d'avoir choisi HYPEZA. Votre commande a été confirmée et est en cours de traitement.
                </p>

                <!-- Numéro de commande -->
                <div style='background-color: #f8f8f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 14px;'>Numéro de commande :</p>
                    <p style='margin: 5px 0 0; font-size: 18px; font-weight: bold; color: rgb(200,155,60);'>{$data['orderNumber']}</p>
                </div>

                <!-- Détails de livraison -->
                <div style='margin: 30px 0; background-color: #f8f8f8; padding: 20px; border-radius: 5px;'>
                    <h3 style='color: rgb(200,155,60); margin: 0 0 15px; font-size: 18px;'>Adresse de livraison</h3>
                    <p style='margin: 5px 0; font-size: 14px; line-height: 1.5;'>
                        {$data['firstName']} {$data['lastName']}<br>
                        {$data['address']}<br>
                        {$data['city']}, {$data['postalCode']}<br>
                        {$data['country']}
                    </p>
                </div>

                <!-- Résumé de la commande -->
                <div style='margin: 30px 0; border: 1px solid #eee; border-radius: 5px;'>
                    <h3 style='background-color: #f8f8f8; margin: 0; padding: 15px; color: rgb(200,155,60); font-size: 18px; border-bottom: 1px solid #eee;'>
                        Résumé de la commande
                    </h3>
                    <div style='padding: 20px;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr style='border-bottom: 1px solid #eee;'>
                                <td style='padding: 10px 0;'>Sous-total</td>
                                <td style='text-align: right;'>{$data['subtotal']}</td>
                            </tr>
                            <tr style='border-bottom: 1px solid #eee;'>
                                <td style='padding: 10px 0;'>Frais de livraison</td>
                                <td style='text-align: right;'>{$data['shipping']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 15px 0; font-weight: bold; font-size: 18px;'>Total</td>
                                <td style='text-align: right; font-weight: bold; font-size: 18px; color: rgb(200,155,60);'>{$data['total']}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <p style='font-size: 14px; line-height: 1.5; color: #666666; margin: 30px 0;'>
                    Nous vous informerons dès que votre commande sera expédiée. Pour toute question, n'hésitez pas à contacter notre service client.
                </p>
            </div>

            <!-- Pied de page -->
            <div style='margin-top: 40px; padding: 20px; background-color: #f8f8f8; text-align: center; border-radius: 5px;'>
                <p style='margin: 0; color: #666666; font-size: 14px;'>
                    Merci de votre confiance<br>
                    <strong style='color: rgb(200,155,60);'>L'équipe HYPEZA</strong>
                </p>
            </div>
        </div>";

        $mail->Body = $emailBody;
        $mail->send();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => "L'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}"
        ]);
    }
}