<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use FPDF\FPDF;

require 'vendor/autoload.php';

// Set proper content type for JSON response
header('Content-Type: application/json');

try {
    // Azure Database connection with SSL
    $host = "hypezaserversql.mysql.database.azure.com";
    $user = "user";
    $pass = "HPL1710COMPAq";
    $db = "users_db";

    // Ensure the mysqli extension is declared in composer.json for compatibility
    // Note: Update composer.json manually or via composer require if needed.

    // Path to SSL certificate - try both locations
    $ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
    $ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

    // Choose the certificate that exists
    $ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

    // Create connection with SSL
    $mysqli = mysqli_init();
    mysqli_ssl_set($mysqli, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($mysqli, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and decode JSON input
        $input = file_get_contents('php://input');

        if (empty($input)) {
            throw new Exception("No input data received");
        }

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // Validate required fields
        $requiredFields = ['email', 'firstName', 'lastName', 'address', 'city', 'postalCode', 'country', 'subtotal', 'shipping', 'total'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate email address format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Generate a unique order number
        $orderNumber = 'HYPZ-' . mt_rand(100000, 999999);

        // Include the order number in the email body and database log
        $data['orderNumber'] = $orderNumber;

        $mail = new PHPMailer(true);

        // Reduce debug level for production
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER during testing if needed

        // Server settings (Titan SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Changed from STARTTLS to SMTPS (SSL)
        $mail->Port       = 465; // Changed from 587 to 465

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

        // Email properties
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64'; // Better encoding for international characters
        $mail->XMailer = 'HYPEZA Mailer';
        $mail->isHTML(true);
        $mail->Priority = 1; // Highest priority

        // More specific subject with order number
        $mail->Subject = 'Confirmation de commande #' . $orderNumber . ' - HYPEZA';

        // Create a unique message ID
        $mail->MessageID = '<' . time() . '.' . md5($data['email'] . $orderNumber) . '@hypza.tech>';

        // Add custom headers to improve deliverability
        $unsubscribeLink = 'https://hypza.tech/unsubscribe?email=' . urlencode($data['email']) . '&token=' . md5($data['email'] . 'some-secret-key');
        $mail->addCustomHeader('List-Unsubscribe', '<' . $unsubscribeLink . '>, <mailto:unsubscribe@hypza.tech?subject=unsubscribe>');
        $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $mail->addCustomHeader('Feedback-ID', $orderNumber . ':HYPEZA:order:gmail');
        $mail->addCustomHeader('X-Entity-Ref-ID', $orderNumber);

        // Use a cleaner HTML structure with minimal inline styles
        $goldColor = '#C89B3C';
        $emailBody = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmation de votre commande HYPEZA</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    color: #333333;
                    background-color: #f9f9f9;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 6px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                .email-header {
                    background-color: #000000;
                    padding: 30px;
                    border-radius: 6px 6px 0 0;
                    text-align: center;
                }
                .email-header h1 {
                    color: {$goldColor};
                    margin: 0;
                    font-size: 32px;
                    letter-spacing: 2px;
                }
                .email-content {
                    padding: 30px;
                }
                .email-footer {
                    background-color: #f8f8f8;
                    padding: 20px;
                    border-radius: 0 0 6px 6px;
                    border-top: 1px solid #eee;
                    text-align: center;
                }
                .email-footer p {
                    margin: 0;
                    font-size: 12px;
                    color: #999;
                }
                .email-footer a {
                    color: #666;
                    text-decoration: none;
                    margin: 0 10px;
                }
                .order-summary, .shipping-details {
                    background-color: #f8f8f8;
                    border-radius: 5px;
                    margin: 25px 0;
                    padding: 20px;
                }
                .order-summary h3, .shipping-details h3 {
                    color: {$goldColor};
                    margin: 0 0 15px;
                    font-size: 18px;
                }
                .order-summary table, .shipping-details table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .order-summary td, .shipping-details td {
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                }
                .order-summary td:last-child, .shipping-details td:last-child {
                    text-align: right;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>HYPEZA</h1>
                </div>
                <div class='email-content'>
                    <p>Bonjour {$data['firstName']} {$data['lastName']},</p>
                    <p>Nous vous remercions d'avoir choisi HYPEZA. Votre commande a été confirmée et est en cours de traitement.</p>
                    <div class='order-summary'>
                        <h3>Résumé de la commande</h3>
                        <table>
                            <tr>
                                <td>Sous-total</td>
                                <td>{$data['subtotal']}</td>
                            </tr>
                            <tr>
                                <td>Frais de livraison</td>
                                <td>{$data['shipping']}</td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td style='color: {$goldColor}; font-weight: bold;'>{$data['total']}</td>
                            </tr>
                        </table>
                    </div>
                    <div class='shipping-details'>
                        <h3>Adresse de livraison</h3>
                        <p>{$data['firstName']} {$data['lastName']}<br>{$data['address']}<br>{$data['city']}, {$data['postalCode']}<br>{$data['country']}</p>
                    </div>
                    <p>Nous vous informerons dès que votre commande sera expédiée. Pour toute question, n'hésitez pas à contacter notre service client à 
                    <a href='mailto:service-client@hypza.tech' style='color: {$goldColor};'>service-client@hypza.tech</a>.</p>
                </div>
                <div class='email-footer'>
                    <p>Merci de votre confiance<br><strong style='color: {$goldColor};'>L'équipe HYPEZA</strong></p>
                    <p>HYPEZA, 123 Rue Example, 75000 Paris, France</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));

        // Generate a PDF receipt
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Add recipient details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Recipient: ' . $data['firstName'] . ' ' . $data['lastName'], 0, 1);
        $pdf->Cell(0, 10, 'Email: ' . $data['email'], 0, 1);
        $pdf->Cell(0, 10, 'Address: ' . $data['address'] . ', ' . $data['city'] . ', ' . $data['postalCode'] . ', ' . $data['country'], 0, 1);
        $pdf->Ln(10);

        // Add order details
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Order Details', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Order Number: ' . $orderNumber, 0, 1);
        $pdf->Cell(0, 10, 'Subtotal: ' . $data['subtotal'], 0, 1);
        $pdf->Cell(0, 10, 'Shipping: ' . $data['shipping'], 0, 1);
        $pdf->Cell(0, 10, 'Total: ' . $data['total'], 0, 1);

        // Save the PDF to a temporary file
        $pdfFilePath = sys_get_temp_dir() . '/order_receipt_' . $orderNumber . '.pdf';
        $pdf->Output('F', $pdfFilePath);

        // Attach the PDF to the email
        $mail->addAttachment($pdfFilePath, 'Order_Receipt_' . $orderNumber . '.pdf');

        // Log email details to database
        $stmt = $mysqli->prepare("INSERT INTO email_logs (order_number, email, recipient_name, send_date, status) VALUES (?, ?, ?, NOW(), 'sending')");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $mysqli->error);
        }

        $recipientName = $data['firstName'] . ' ' . $data['lastName'];
        $stmt->bind_param("sss", $orderNumber, $data['email'], $recipientName);
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
                'orderNumber' => $orderNumber
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

    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }

} catch (Exception $e) {
    // Ensure proper error response in JSON format
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close the database connection if it exists
    if (isset($mysqli) && $mysqli) {
        $mysqli->close();
    }
}
