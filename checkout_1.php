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

        // Email content
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333333;
            background-color: #f9f9f9;
        }
        .email-container {
            width: 100%;
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
            padding: 15px;
            margin-top: 20px;
            font-size: 32px;
            letter-spacing: 2px;
        }
        .email-body {
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
            margin: 0 0 15px;
            color: #666666;
            font-size: 14px;
        }
        .email-footer a {
            color: #666;
            margin: 0 10px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>
            <h1>HYPEZA</h1>
        </div>
        <div class='email-body'>
            <p>Bonjour {$data['firstName']} {$data['lastName']},</p>
            <p>Nous vous remercions d'avoir choisi HYPEZA. Votre commande a été confirmée et est en cours de traitement.</p>
            <p>Numéro de commande : <strong>{$data['orderNumber']}</strong></p>
            <p>Adresse de livraison :</p>
            <p>{$data['firstName']} {$data['lastName']}<br>{$data['address']}<br>{$data['city']}, {$data['postalCode']}<br>{$data['country']}</p>
            <p>Résumé de la commande :</p>
            <p>Sous-total : {$data['subtotal']}</p>
            <p>Frais de livraison : {$data['shipping']}</p>
            <p>Total : <strong>{$data['total']}</strong></p>
            <p>Nous vous informerons dès que votre commande sera expédiée. Pour toute question, n'hésitez pas à contacter notre service client à <a href='mailto:service-client@hypza.tech'>service-client@hypza.tech</a>.</p>
        </div>
        <div class='email-footer'>
            <p>Merci de votre confiance<br><strong>L'équipe HYPEZA</strong></p>
            <p>HYPEZA, 123 Rue Example, 75000 Paris, France</p>
        </div>
    </div>
</body>
</html>
";

$mail->Body = $emailBody;
$mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));


        // Send email

        if ($mail->send()) {
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully',
                'orderNumber' => $data['orderNumber']
            ]);
        } else {
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

