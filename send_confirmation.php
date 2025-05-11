<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

header('Content-Type: application/json');

// Azure Database connection with SSL
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate - try both locations
$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

// Choose the certificate that exists
$ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

// Create connection with SSL
$mysqli = mysqli_init();
mysqli_ssl_set($mysqli, NULL, NULL, $ssl_cert, NULL, NULL);

if (!mysqli_real_connect($mysqli, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        $requiredFields = ['email', 'firstName', 'lastName', 'orderNumber', 'address', 'city', 'postalCode', 'country', 'subtotal', 'shipping', 'total'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate email address format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        $mail = new PHPMailer(true);

// Reduce debug level for production
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER during testing if needed

// Server settings (Titan SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'APG$dLj9A!w/=qU'; // Better to use environment variables for this
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

// SSL Certificate Configuration - Fixed to disable strict verification
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        
        // Sender & Recipient - only ONE setFrom call
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addReplyTo('service-client@hypza.tech', 'Service Client HYPEZA');
        $mail->addAddress($data['email'], $data['firstName'] . ' ' . $data['lastName']);

        // No DKIM setup needed - Titan handles it automatically

        // Email properties
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64'; // Better encoding for international characters
        $mail->XMailer = 'HYPEZA Mailer';
        $mail->isHTML(true);
        $mail->Priority = 1; // Highest priority

        // More specific subject with order number
        $mail->Subject = 'Confirmation de commande #' . $data['orderNumber'] . ' - HYPEZA';

        // Create a unique message ID
        $mail->MessageID = '<' . time() . '.' . md5($data['email'] . $data['orderNumber']) . '@hypza.tech>';

        // Add custom headers to improve deliverability
        $unsubscribeLink = 'https://hypza.tech/unsubscribe?email=' . urlencode($data['email']) . '&token=' . md5($data['email'] . 'some-secret-key');
        $mail->addCustomHeader('List-Unsubscribe', '<' . $unsubscribeLink . '>, <mailto:unsubscribe@hypza.tech?subject=unsubscribe>');
        $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $mail->addCustomHeader('Feedback-ID', $data['orderNumber'] . ':HYPEZA:order:gmail');
        $mail->addCustomHeader('X-Entity-Ref-ID', $data['orderNumber']);

        // Use a cleaner HTML structure with minimal inline styles
        $goldColor = '#C89B3C';
        $emailBody = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmation de votre commande HYPEZA</title>
        </head>
        <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; color: #333333; background-color: #f9f9f9;'>
            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                <tr>
                    <td align='center' style='padding: 20px 0;'>
                        <table width='600' cellpadding='0' cellspacing='0' border='0' style='background-color: #ffffff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>
                            <!-- Header -->
                            <tr>
                                <td align='center' style='background-color: #000000; padding: 30px; border-radius: 6px 6px 0 0;'>
                                    <h1 style='color: {$goldColor}; margin: 0; font-size: 32px; letter-spacing: 2px;'>HYPEZA</h1>
                                </td>
                            </tr>
                            
                            <!-- Main Content -->
                            <tr>
                                <td style='padding: 30px;'>
                                    <p style='font-size: 16px; color: #666666;'>Bonjour {$data['firstName']} {$data['lastName']},</p>
                                    
                                    <p style='font-size: 16px; line-height: 1.6; margin: 20px 0;'>
                                        Nous vous remercions d'avoir choisi HYPEZA. Votre commande a été confirmée et est en cours de traitement.
                                    </p>

                                    <!-- Order Number -->
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f8f8f8; border-radius: 5px; margin: 25px 0;'>
                                        <tr>
                                            <td style='padding: 15px;'>
                                                <p style='margin: 0; font-size: 14px;'>Numéro de commande :</p>
                                                <p style='margin: 5px 0 0; font-size: 18px; font-weight: bold; color: {$goldColor};'>{$data['orderNumber']}</p>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Shipping Details -->
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f8f8f8; border-radius: 5px; margin: 25px 0;'>
                                        <tr>
                                            <td style='padding: 20px;'>
                                                <h3 style='color: {$goldColor}; margin: 0 0 15px; font-size: 18px;'>Adresse de livraison</h3>
                                                <p style='margin: 5px 0; font-size: 14px; line-height: 1.6;'>
                                                    {$data['firstName']} {$data['lastName']}<br>
                                                    {$data['address']}<br>
                                                    {$data['city']}, {$data['postalCode']}<br>
                                                    {$data['country']}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Order Summary -->
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='border: 1px solid #eee; border-radius: 5px; margin: 25px 0;'>
                                        <tr>
                                            <td style='background-color: #f8f8f8; padding: 15px; border-radius: 5px 5px 0 0; border-bottom: 1px solid #eee;'>
                                                <h3 style='color: {$goldColor}; margin: 0; font-size: 18px;'>Résumé de la commande</h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 20px;'>
                                                <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                    <tr>
                                                        <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>Sous-total</td>
                                                        <td align='right' style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$data['subtotal']}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>Frais de livraison</td>
                                                        <td align='right' style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$data['shipping']}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 15px 0; font-weight: bold; font-size: 18px;'>Total</td>
                                                        <td align='right' style='padding: 15px 0; font-weight: bold; font-size: 18px; color: {$goldColor};'>{$data['total']}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style='font-size: 14px; line-height: 1.6; color: #666666; margin: 25px 0;'>
                                        Nous vous informerons dès que votre commande sera expédiée. Pour toute question, n'hésitez pas à contacter notre service client à 
                                        <a href='mailto:service-client@hypza.tech' style='color: {$goldColor}; text-decoration: none;'>service-client@hypza.tech</a>.
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8f8f8; padding: 20px; border-radius: 0 0 6px 6px; border-top: 1px solid #eee;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                            <td align='center'>
                                                <p style='margin: 0 0 15px; color: #666666; font-size: 14px;'>
                                                    Merci de votre confiance<br>
                                                    <strong style='color: {$goldColor};'>L'équipe HYPEZA</strong>
                                                </p>
                                                <p style='margin: 15px 0 0; font-size: 12px; color: #999999;'>
                                                    Cet email a été envoyé à {$data['email']}.<br>
                                                    Si vous ne souhaitez plus recevoir nos emails, 
                                                    <a href='{$unsubscribeLink}' style='color: #666666;'>cliquez ici pour vous désabonner</a>.
                                                </p>
                                                
                                                <!-- Social Links -->
                                                <p style='margin-top: 20px;'>
                                                    <a href='https://facebook.com/hypeza' style='color: #666; margin: 0 10px;'>Facebook</a>
                                                    <a href='https://instagram.com/hypeza' style='color: #666; margin: 0 10px;'>Instagram</a>
                                                    <a href='https://hypza.tech' style='color: #666; margin: 0 10px;'>Website</a>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Physical Address - Important for CAN-SPAM compliance -->
                        <table width='600' cellpadding='0' cellspacing='0' border='0'>
                            <tr>
                                <td align='center' style='padding: 20px 0; font-size: 12px; color: #999;'>
                                    HYPEZA, 123 Rue Example, 75000 Paris, France
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));

        // Log email details to database
        $stmt = $mysqli->prepare("INSERT INTO email_logs (order_number, email, recipient_name, send_date, status) VALUES (?, ?, ?, NOW(), 'sending')");
        $recipientName = $data['firstName'] . ' ' . $data['lastName'];
        $stmt->bind_param("sss", $data['orderNumber'], $data['email'], $recipientName);
        $stmt->execute();
        $logId = $stmt->insert_id;
        $stmt->close();

        // Send the email
        if ($mail->send()) {
            // Update the log to indicate successful delivery
            $stmt = $mysqli->prepare("UPDATE email_logs SET status = 'sent' WHERE id = ?");
            $stmt->bind_param("i", $logId);
            $stmt->execute();
            $stmt->close();

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully',
                'orderNumber' => $data['orderNumber']
            ]);
        } else {
            // Update the log to indicate failure
            $stmt = $mysqli->prepare("UPDATE email_logs SET status = 'failed', error_message = ? WHERE id = ?");
            $errorMsg = $mail->ErrorInfo;
            $stmt->bind_param("si", $errorMsg, $logId);
            $stmt->execute();
            $stmt->close();

            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

// Close the database connection
$mysqli->close();

