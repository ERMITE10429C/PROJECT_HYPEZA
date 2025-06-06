<?php
// Add this PHP code at the very top of checkout_1.php
// This part will handle the email sending request when accessed with POST
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Only process if this is a POST request with JSON content type
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {


    // Set content type to JSON for AJAX response
    header('Content-Type: application/json');

    try {
        // Load Composer's autoloader
        require 'vendor/autoload.php';
        require_once 'generate_receipt_pdf.php';

        // Get and decode JSON data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // Validate required fields
        $requiredFields = ['email', 'firstName', 'lastName', 'orderNumber', 'address', 'city', 'postalCode', 'country', 'subtotal', 'shipping', 'total'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // Configure SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'azerty@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // SSL Configuration
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true
            ]
        ];

        // Set sender and recipient
        $mail->setFrom('team@hypza.tech', 'HYPEZA');
        $mail->addReplyTo('service-client@hypza.tech', 'Service Client HYPEZA');
        $mail->addAddress($data['email'], $data['firstName'] . ' ' . $data['lastName']);


// Email content with the same structure as send_confirmation.php
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64'; // Better encoding for international characters
$mail->XMailer = 'HYPEZA Mailer';
$mail->isHTML(true);
$mail->Priority = 3; // Highest priority

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

// Use the same comprehensive HTML structure as send_confirmation.php
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
                            <h1 style='color: {$goldColor}; padding: 15px;  margin-top: 20px; font-size: 32px; letter-spacing: 2px;'>HYPEZA</h1>
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



// Generate PDF receipt
        $pdfContent = generateReceiptPDF($data);

// Create a temporary file for the PDF
        $tempFile = tempnam(sys_get_temp_dir(), 'receipt_');
        file_put_contents($tempFile, $pdfContent);

// Attach the PDF to the email
        $mail->addAttachment($tempFile, "HYPEZA_Receipt_{$data['orderNumber']}.pdf");

// Send email
        if ($mail->send()) {
            // Delete the temporary file after sending
            @unlink($tempFile);

            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully with receipt PDF',
                'orderNumber' => $data['orderNumber']
            ]);
        } else {
            // Delete the temporary file if email fails
            @unlink($tempFile);
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }

    }
    catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    // Important: exit here to prevent HTML content from being sent in JSON response
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HYPEZA - Checkout</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prata&family=Cormorant+Garamond:wght@300;400;500&display=swap">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* HYPEZA Brand Style Guide Implementation */
    :root {
      /* Color Palette */
      --gold: #C89B3C;
      --black: #000000;
      --white: #FFFFFF;
      --dark-gray: #111111;
      --medium-gray: #333333;
      --light-gray: #777777;

      /* Spacing System */
      --space-xs: 8px;
      --space-sm: 16px;
      --space-md: 24px;
      --space-lg: 40px;
      --space-xl: 80px;

      /* Border Radius */
      --radius-sm: 5px;
      --radius-md: 15px;
      --radius-lg: 25px;
    }

    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      transition: all 0.3s ease;
    }

    body {

      background-color: var(--black);
      color: var(--white);
      line-height: 1.5;
      overflow-x: hidden;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    /* Hide all scrollbars throughout the site */
    html, body, div, ::-webkit-scrollbar {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    .checkout-summary::-webkit-scrollbar,
    .checkout-container::-webkit-scrollbar,
    .summary-items::-webkit-scrollbar,
    .container::-webkit-scrollbar,
    div::-webkit-scrollbar,
    #summary-items::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    /* Keep scroll functionality */
    body {
      overflow-y: scroll;
      overflow-x: hidden;
    }

    /* Ensure no horizontal scrolling on any element */
    * {
      max-width: 100%;
      box-sizing: border-box;
      overflow-x: hidden;
    }

    /* Specifically target the order summary container */
    #summary-items {
      overflow-y: auto;
      overflow-x: hidden;
    }
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Prata', serif;
      font-weight: 400;
      margin-bottom: var(--space-md);
      line-height: 1.2;
    }

    h1 {
      font-size: 2.5rem;
    }

    h2 {
      font-size: 2rem;
    }

    h3 {
      font-size: 1.5rem;
    }

    p {
      margin-bottom: var(--space-sm);
      font-size: 1.1rem;
    }

    a {
      color: var(--white);
      text-decoration: none;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: var(--space-lg);
    }

    /* Header */
    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 5px 20px;
      color: #333;
      backdrop-filter: blur(10px);
      position: fixed;
      width: 100%;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      height: 70px;
      background-color: rgba(0, 0, 0, 0.9);
    }

    .logo {
      font-size: 2rem;
      color: var(--gold);
      letter-spacing: 2px;
      text-align: center;
      margin-bottom: var(--space-md);
    }

    /* Checkout Page */
    .checkout-container {
      padding-top: 100px;
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-lg);
    }

    .checkout-form {
      flex: 2;
      min-width: 300px;
    }

    .checkout-summary {
      flex: 1;
      min-width: 300px;
      background-color: var(--dark-gray);
      border-radius: var(--radius-md);
      padding: var(--space-md);
      position: sticky;
      top: 100px;
      max-height: calc(100vh - 120px);
      overflow-y: auto;
    }

    .checkout-section {
      margin-bottom: var(--space-lg);
      background-color: var(--dark-gray);
      border-radius: var(--radius-md);
      padding: var(--space-md);
    }

    .checkout-section-title {
      display: flex;
      align-items: center;
      margin-bottom: var(--space-md);
      border-bottom: 1px solid rgba(200, 155, 60, 0.3);
      padding-bottom: var(--space-sm);
    }

    .checkout-section-title i {
      color: var(--gold);
      margin-right: var(--space-sm);
    }

    .form-group {
      margin-bottom: var(--space-sm);
    }

    .form-group label {
      display: block;
      margin-bottom: var(--space-xs);
      color: var(--light-gray);
    }

    .form-control {
      width: 100%;
      padding: var(--space-sm);
      background-color: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      color: var(--white);
      font-family: 'Cormorant Garamond', serif;
      font-size: 1rem;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--gold);
      box-shadow: 0 0 0 2px rgba(200, 155, 60, 0.2);
    }

    .form-row {
      display: flex;
      gap: var(--space-md);
    }

    .form-col {
      flex: 1;
    }

    /* Payment Methods */
    .payment-methods {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-sm);
      margin-bottom: var(--space-md);
    }

    .payment-method {
      flex: 1;
      min-width: 120px;
      padding: var(--space-sm);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--space-xs);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-method.active {
      border-color: var(--gold);
      background-color: rgba(200, 155, 60, 0.1);
    }

    .payment-method:hover {
      border-color: rgba(200, 155, 60, 0.5);
    }

    .payment-method i {
      font-size: 1.5rem;
    }

    .payment-details {
      display: none;
      margin-top: var(--space-md);
      padding-top: var(--space-md);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .payment-details.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }

    /* Order Summary */
    .summary-item {
      display: flex;
      gap: var(--space-md);
      margin-bottom: var(--space-md);
      padding-bottom: var(--space-md);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .summary-item-image {
      width: 80px;
      height: 100px;
      object-fit: cover;
      border-radius: var(--radius-sm);
    }

    .summary-item-details {
      flex: 1;
    }

    .summary-item-title {
      font-size: 1.1rem;
      margin-bottom: 5px;
    }

    .summary-item-options {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 5px;
    }

    .summary-item-price {
      color: var(--gold);
      font-size: 1.1rem;
    }

    .summary-subtotal,
    .summary-shipping,
    .summary-total {
      display: flex;
      justify-content: space-between;
      margin-bottom: var(--space-sm);
    }

    .summary-subtotal,
    .summary-shipping {
      color: rgba(255, 255, 255, 0.7);
    }

    .summary-total {
      font-size: 1.2rem;
      color: var(--white);
      font-weight: bold;
      padding-top: var(--space-sm);
      border-top: 1px solid rgba(200, 155, 60, 0.3);
    }

    .summary-total-amount {
      color: var(--gold);
    }

    /* Buttons */
    .btn {
      display: inline-block;
      padding: var(--space-sm) var(--space-lg);
      border: 1px solid var(--gold);
      background-color: transparent;
      color: var(--white);
      font-family: 'Prata', serif;
      font-size: 1rem;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      border-radius: var(--radius-sm);
      width: 100%;
      text-align: center;
      margin-top: var(--space-md);
    }

    .btn-primary {
      background-color: var(--gold);
      color: var(--black);
    }

    .btn:hover {
      background-color: var(--gold);
      color: var(--black);
      transform: scale(1.02);
    }

    .btn-link {
      background: none;
      border: none;
      color: var(--gold);
      padding: var(--space-xs) 0;
      text-decoration: underline;
      cursor: pointer;
    }

    .btn-link:hover {
      color: var(--white);
    }

    /* Progress Bar */
    .checkout-progress {
      display: flex;
      justify-content: space-between;
      margin-bottom: var(--space-lg);
      position: relative;
    }

    .checkout-progress::before {
      content: '';
      position: absolute;
      top: 12px;
      left: 0;
      width: 100%;
      height: 1px;
      background-color: rgba(255, 255, 255, 0.1);
      z-index: 1;
    }

    .progress-step {
      position: relative;
      z-index: 2;
      text-align: center;
      width: 33.33%;
    }

    .step-indicator {
      width: 25px;
      height: 25px;
      background-color: var(--dark-gray);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto var(--space-xs);
    }

    .progress-step.active .step-indicator {
      background-color: var(--gold);
      color: var(--black);
    }

    .progress-step.completed .step-indicator {
      background-color: var(--gold);
      color: var(--black);
    }

    .progress-step.completed .step-indicator::after {
      content: '✓';
    }

    .step-label {
      font-size: 0.9rem;
      color: var(--light-gray);
    }

    .progress-step.active .step-label {
      color: var(--gold);
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    /* Error Messages */
    .error-message {
      color: #ff4d4d;
      font-size: 0.9rem;
      margin-top: 5px;
      display: none;
    }

    .form-control.error {
      border-color: #ff4d4d;
    }

    /* Success Message */
    .success-message {
      text-align: center;
      padding: var(--space-xl) var(--space-lg);
      display: none;
    }

    .success-icon {
      font-size: 4rem;
      color: var(--gold);
      margin-bottom: var(--space-md);
    }

    .success-title {
      font-size: 2rem;
      margin-bottom: var(--space-sm);
    }

    .success-details {
      color: var(--light-gray);
      margin-bottom: var(--space-lg);
    }

    .back-link {
      color: var(--gold);
      margin-top: var(--space-lg);
      display: inline-block;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    /* Shimmer Effect */
    .shimmer {
      position: relative;
      overflow: hidden;
    }

    .shimmer::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        rgba(255, 255, 255, 0) 100%
      );
      transform: rotate(30deg);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0% {
        transform: translateX(-100%) rotate(30deg);
      }
      100% {
        transform: translateX(100%) rotate(30deg);
      }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .checkout-container {
        flex-direction: column;
      }

      .form-row {
        flex-direction: column;
        gap: var(--space-sm);
      }
    }



    .remove-item-btn {
      background-color: transparent;
      color: var(--light-gray);
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0 10px;
      transition: color 0.3s ease;
      align-self: center;
    }

    .remove-item-btn:hover {
      color: var(--gold);
    }

    .summary-item {
      display: flex;
      gap: var(--space-md);
      margin-bottom: var(--space-md);
      padding-bottom: var(--space-md);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
    }

    /* Quantity control styles */
    .summary-item-options {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 8px;
    }

    .item-option {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .item-option label {
      color: var(--light-gray);
      font-size: 0.85rem;
      min-width: 45px;
    }

    .size-select, .color-select {
      background-color: rgba(255, 255, 255, 0.05);
      color: var(--white);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      padding: 4px 8px;
      font-family: 'Cormorant Garamond', serif;
      font-size: 0.85rem;
      cursor: pointer;
    }

    .quantity-wrapper {
      display: flex;
      align-items: center;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      overflow: hidden;
    }

    .quantity-btn {
      background-color: rgba(255, 255, 255, 0.05);
      color: var(--white);
      border: none;
      padding: 0 8px;
      height: 24px;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .quantity-btn:hover {
      background-color: var(--gold);
      color: var(--black);
    }

    .quantity-input {
      width: 40px;
      border: none;
      background: transparent;
      color: var(--white);
      font-size: 0.85rem;
      text-align: center;
      -moz-appearance: textfield;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Update summary-item layout */
    .summary-item {
      display: flex;
      gap: var(--space-md);
      margin-bottom: var(--space-md);
      padding-bottom: var(--space-md);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
    }

    .summary-item-image {
      width: 80px;
      height: 100px;
      object-fit: cover;
      border-radius: var(--radius-sm);
      align-self: flex-start;
    }

    /* Fix dropdown menu text visibility */
    .size-select option, .color-select option {
      background-color: var(--dark-gray);
      color: var(--white);
    }

    /* Style for the dropdown when open */
    .size-select:focus, .color-select:focus {
      outline: 1px solid var(--gold);
    }

    /* Fix for some browsers that might render select differently */
    @-moz-document url-prefix() {
      .size-select, .color-select {
        background-color: var(--dark-gray);
        color: var(--white);
      }
    }

    /* Styling for webkit browsers */
    .size-select, .color-select {
      -webkit-appearance: none;
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg fill='white' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
      background-repeat: no-repeat;
      background-position: right 5px center;
      padding-right: 25px;
    }
    /* Fix country dropdown menu text visibility */
    .form-control option {
      background-color: var(--dark-gray);
      color: var(--white);
    }

    /* Style for country dropdown when open */
    select.form-control {
      -webkit-appearance: none;
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg fill='white' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
      background-repeat: no-repeat;
      background-position: right 10px center;
      padding-right: 30px;
    }

    /* Fix for select elements in Firefox */
    @-moz-document url-prefix() {
      select.form-control {
        background-color: var(--dark-gray);
        color: var(--white);
      }
    }

  </style>
</head>
<body>


    <!-- Header -->
    <header class="header">
        <h1 class="logo">HYPEZA</h1>
    </header>

    <!-- Checkout Container -->
    <div class="container checkout-container">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <!-- Progress Bar -->
            <div class="checkout-progress">
                <div class="progress-step completed">
                    <div class="step-indicator">1</div>
                    <div class="step-label">Cart</div>
                </div>
                <div class="progress-step active">
                    <div class="step-indicator">2</div>
                    <div class="step-label">Checkout</div>
                </div>
                <div class="progress-step">
                    <div class="step-indicator">3</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <!-- Shipping Section -->
            <div class="checkout-section">
                <div class="checkout-section-title">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Shipping Information</h3>
                </div>
                <div class="form-group">
                    <label for="email">Email Address*</label>
                    <input type="email" id="email" class="form-control" required>
                    <div class="error-message" id="email-error">Please enter a valid email address</div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="firstName">First Name*</label>
                            <input type="text" id="firstName" class="form-control" required>
                            <div class="error-message" id="firstName-error">Please enter your first name</div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="lastName">Last Name*</label>
                            <input type="text" id="lastName" class="form-control" required>
                            <div class="error-message" id="lastName-error">Please enter your last name</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address Line 1*</label>
                    <input type="text" id="address" class="form-control" required>
                    <div class="error-message" id="address-error">Please enter your address</div>
                </div>
                <div class="form-group">
                    <label for="address2">Address Line 2</label>
                    <input type="text" id="address2" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="city">City*</label>
                            <input type="text" id="city" class="form-control" required>
                            <div class="error-message" id="city-error">Please enter your city</div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="postalCode">Postal Code*</label>
                            <input type="text" id="postalCode" class="form-control" required>
                            <div class="error-message" id="postalCode-error">Please enter a valid postal code</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="country">Country*</label>
                    <select id="country" class="form-control" required>
                        <option value="">Select Country</option>
                        <option value="France">France</option>
                        <option value="United States">United States</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Germany">Germany</option>
                        <option value="Italy">Italy</option>
                        <option value="Spain">Spain</option>
                        <option value="Canada">Canada</option>
                        <option value="Australia">Australia</option>
                        <option value="Japan">Japan</option>
                    </select>
                    <div class="error-message" id="country-error">Please select your country</div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number*</label>
                    <input type="tel" id="phone" class="form-control" required>
                    <div class="error-message" id="phone-error">Please enter a valid phone number</div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="checkout-section">
                <div class="checkout-section-title">
                    <i class="fas fa-credit-card"></i>
                    <h3>Payment Method</h3>
                </div>
                <div class="payment-methods">
                    <div class="payment-method active" data-method="card">
                        <i class="fas fa-credit-card"></i>
                        <span>Credit Card</span>
                    </div>
                    <div class="payment-method" data-method="paypal">
                        <i class="fab fa-paypal"></i>
                        <span>PayPal</span>
                    </div>
                    <div class="payment-method" data-method="apple">
                        <i class="fab fa-apple-pay"></i>
                        <span>Apple Pay</span>
                    </div>
                </div>

                <!-- Credit Card Details -->
                <div class="payment-details active" id="card-payment">
                    <div class="form-group">
                        <label for="cardName">Cardholder Name*</label>
                        <input type="text" id="cardName" class="form-control" required>
                        <div class="error-message" id="cardName-error">Please enter the cardholder name</div>
                    </div>
                    <div class="form-group">
                        <label for="cardNumber">Card Number*</label>
                        <input type="text" id="cardNumber" class="form-control" placeholder="XXXX XXXX XXXX XXXX" required>
                        <div class="error-message" id="cardNumber-error">Please enter a valid card number</div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="expiryDate">Expiry Date*</label>
                                <input type="text" id="expiryDate" class="form-control" placeholder="MM/YY" required>
                                <div class="error-message" id="expiryDate-error">Please enter a valid expiry date</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="cvv">CVV*</label>
                                <input type="text" id="cvv" class="form-control" placeholder="XXX" required>
                                <div class="error-message" id="cvv-error">Please enter a valid CVV</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PayPal Details -->
                <div class="payment-details" id="paypal-payment">
                    <p>You will be redirected to PayPal to complete your payment.</p>
                </div>

                <!-- Apple Pay Details -->
                <div class="payment-details" id="apple-payment">
                    <p>You will be prompted to confirm payment with Apple Pay.</p>
                </div>
            </div>

            <!-- Place Order Button -->
            <button id="place-order-btn" class="btn btn-primary shimmer">Place Order</button>
        </div>

        <!-- Order Summary -->
        <div class="checkout-summary">
            <div class="checkout-section-title">
                <i class="fas fa-shopping-bag"></i>
                <h3>Order Summary</h3>
            </div>
            <div id="summary-items">
                <!-- Items will be loaded dynamically -->
            </div>
            <div class="summary-subtotal">
                <span>Subtotal:</span>
                <span id="subtotal-amount">$0.00</span>
            </div>
            <div class="summary-shipping">
                <span>Shipping:</span>
                <span id="shipping-amount">$0.00</span>
            </div>
            <div class="summary-total">
                <span>Total:</span>
                <span id="total-amount" class="summary-total-amount">$0.00</span>
            </div>


            <a href="womans_collections/Women's_Collections.html">

                <button class="btn-link" id="return-to-cart">Return to Cart</button>

            </a>

        </div>

    </div>

    <!-- Success Message (Hidden by default) -->
    <div class="container success-message" id="success-message">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="success-title">Order Placed Successfully!</h1>
        <p class="success-details">Thank you for your order. A confirmation has been sent to your email.</p>
        <p>Your order number is: <strong id="order-number">HYPZ-123456</strong></p>
        <a href="home.php" class="btn shimmer">Continue Shopping</a>
        <p class="back-link">Check Your Gmail Inbox For the purchase Details </p>
    </div>

    <script src="cartManager.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load cart items from localStorage if available

            // Payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    // Remove active class from all methods
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    // Add active class to clicked method
                    this.classList.add('active');

                    // Show respective payment details
                    const paymentMethod = this.getAttribute('data-method');
                    document.querySelectorAll('.payment-details').forEach(detail => detail.classList.remove('active'));
                    document.getElementById(`${paymentMethod}-payment`).classList.add('active');
                });
            });

            // Return to cart button
            document.getElementById('return-to-cart').addEventListener('click', function() {
                window.history.back();
            });

            // Form validation and submission
            document.getElementById('place-order-btn').addEventListener('click', function(e) {
                e.preventDefault();

                if (validateForm()) {
                    // Simulate order processing
                    const btn = this;
                    btn.textContent = 'Processing...';
                    btn.disabled = true;

                    setTimeout(() => {
                        // Hide checkout form and show success message
                        document.querySelector('.checkout-container').style.display = 'none';
                        document.getElementById('success-message').style.display = 'block';

                        // Generate random order number
                        document.getElementById('order-number').textContent = `HYPZ-${Math.floor(100000 + Math.random() * 900000)}`;

                        // Clear cart
                        localStorage.removeItem('cartItems');
                    }, 1500);
                }
            });

            // Format credit card number as user types
            document.getElementById('cardNumber').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 16) value = value.slice(0, 16);

                // Add spaces every 4 digits
                let formatted = '';
                for(let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) formatted += ' ';
                    formatted += value[i];
                }

                e.target.value = formatted;
            });

            // Format expiry date as user types
            document.getElementById('expiryDate').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) value = value.slice(0, 4);

                if (value.length > 2) {
                    e.target.value = value.slice(0, 2) + '/' + value.slice(2);
                } else {
                    e.target.value = value;
                }
            });

            // Format CVV as user types
            document.getElementById('cvv').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 3) value = value.slice(0, 3);
                e.target.value = value;
            });
        });

        // Load cart items from localStorage

        // Calculate shipping (free for orders over $500)

        // Update summary amounts

        // Validate form fields
        function validateForm() {
            let isValid = true;

            // Shipping information validation
            const email = document.getElementById('email');
            if (!validateEmail(email.value)) {
                showError(email, 'email-error');
                isValid = false;
            }

            const firstName = document.getElementById('firstName');
            if (!firstName.value.trim()) {
                showError(firstName, 'firstName-error');
                isValid = false;
            }

            const lastName = document.getElementById('lastName');
            if (!lastName.value.trim()) {
                showError(lastName, 'lastName-error');
                isValid = false;
            }

            const address = document.getElementById('address');
            if (!address.value.trim()) {
                showError(address, 'address-error');
                isValid = false;
            }

            const city = document.getElementById('city');
            if (!city.value.trim()) {
                showError(city, 'city-error');
                isValid = false;
            }

            const postalCode = document.getElementById('postalCode');
            if (!postalCode.value.trim()) {
                showError(postalCode, 'postalCode-error');
                isValid = false;
            }

            const country = document.getElementById('country');
            if (!country.value) {
                showError(country, 'country-error');
                isValid = false;
            }

            const phone = document.getElementById('phone');
            if (!validatePhone(phone.value)) {
                showError(phone, 'phone-error');
                isValid = false;
            }

            // Only validate payment details if credit card is selected
            if (document.querySelector('.payment-method.active').getAttribute('data-method') === 'card') {
                const cardName = document.getElementById('cardName');
                if (!cardName.value.trim()) {
                    showError(cardName, 'cardName-error');
                    isValid = false;
                }

                const cardNumber = document.getElementById('cardNumber');
                if (!validateCardNumber(cardNumber.value)) {
                    showError(cardNumber, 'cardNumber-error');
                    isValid = false;
                }

                const expiryDate = document.getElementById('expiryDate');
                if (!validateExpiryDate(expiryDate.value)) {
                    showError(expiryDate, 'expiryDate-error');
                    isValid = false;
                }

                const cvv = document.getElementById('cvv');
                if (!validateCVV(cvv.value)) {
                    showError(cvv, 'cvv-error');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Helper validation functions
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        function validatePhone(phone) {
            const re = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
            return re.test(String(phone));
        }

        function validateCardNumber(cardNumber) {
            const re = /^[\d\s]{15,19}$/;
            return re.test(String(cardNumber));
        }

        function validateExpiryDate(expiryDate) {
            const re = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
            if (!re.test(expiryDate)) return false;

            const [month, year] = expiryDate.split('/');
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear() % 100;
            const currentMonth = currentDate.getMonth() + 1;

            const expYear = parseInt(year, 10);
            const expMonth = parseInt(month, 10);

            return (expYear > currentYear) || (expYear === currentYear && expMonth >= currentMonth);
        }

        function validateCVV(cvv) {
            const re = /^[0-9]{3,4}$/;
            return re.test(String(cvv));
        }

        // Show error message for a field
        function showError(field, errorId) {
            field.classList.add('error');
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'block';
            }

            // Remove error when user starts typing again
            field.addEventListener('input', function() {
                field.classList.remove('error');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }, { once: true });
        }

        // Ajoutez cette fonction dans votre script existant

        async function sendConfirmationEmail(orderData) {
            try {
                // Send request to the same page (checkout_1.php)
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData),
                    signal: AbortSignal.timeout(10000)
                });

                if (!response.ok) {
                    throw new Error(`Server responded with status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Email sending result:', result);
                return result.success;
            } catch (error) {
                console.error('Error sending confirmation email:', error);
                return false;
            }
        }





        // Modifiez la partie du code qui gère la soumission de la commande
        document.getElementById('place-order-btn').addEventListener('click', async function(e) {
            e.preventDefault();

            if (validateForm()) {
                const btn = this;
                btn.textContent = 'Processing...';
                btn.disabled = true;

                // Récupération des données du formulaire
                const orderData = {
                    orderNumber: `HYPZ-${Math.floor(100000 + Math.random() * 900000)}`,
                    email: document.getElementById('email').value,
                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    address: document.getElementById('address').value,
                    city: document.getElementById('city').value,
                    postalCode: document.getElementById('postalCode').value,
                    country: document.getElementById('country').value,
                    subtotal: document.getElementById('subtotal-amount').textContent,
                    shipping: document.getElementById('shipping-amount').textContent,
                    total: document.getElementById('total-amount').textContent
                };

                // Envoi de l'email de confirmation
                const emailSent = await sendConfirmationEmail(orderData);

                if (emailSent) {
                    // Affichage du message de succès
                    document.querySelector('.checkout-container').style.display = 'none';
                    document.getElementById('success-message').style.display = 'block';
                    document.getElementById('order-number').textContent = orderData.orderNumber;

                    // Nettoyage du panier
                    localStorage.removeItem('cartItems');
                } else {
                    alert('Une erreur est survenue lors de l\'envoi de l\'email de confirmation');
                    btn.textContent = 'Place Order';
                    btn.disabled = false;
                }
            }
        });

    </script>

</body>