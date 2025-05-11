<?php
// test_email.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Set proper content type for AJAX response
header('Content-Type: application/json');

// Create log file to track execution
$log_file = 'email_test_log.txt';
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
}

log_message("Test email script executed");

try {
    // Check if Composer autoloader exists
    if (!file_exists('vendor/autoload.php')) {
        throw new Exception("Vendor autoload.php not found. Make sure Composer dependencies are installed.");
    }

    require 'vendor/autoload.php';
    log_message("Autoloader included successfully");

    // Simple test without sending actual email
    if (isset($_GET['check_only'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Test file accessed successfully',
            'server_time' => date('Y-m-d H:i:s'),
            'phpmailer_available' => class_exists('PHPMailer\PHPMailer\PHPMailer')
        ]);
        log_message("Basic check completed - PHPMailer available: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'Yes' : 'No'));
        exit;
    }

    // If we're here, we're attempting to send a test email
    $mail = new PHPMailer(true);
    log_message("PHPMailer instance created");

    // Server settings (Titan SMTP)
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = 'smtp.titan.email';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'team@hypza.tech';
    $mail->Password   = 'APG$dLj9A!w/=qU';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    log_message("SMTP configuration set");

    // SSL Certificate Configuration
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Test recipient email - GET parameter or default
    $test_email = isset($_GET['email']) ? $_GET['email'] : 'test@example.com';

    // Sender & Recipient
    $mail->setFrom('team@hypza.tech', 'HYPEZA Test');
    $mail->addAddress($test_email);
    log_message("Email addresses configured: sending to {$test_email}");

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'HYPEZA Email Test';
    $mail->Body    = 'This is a test email from HYPEZA. If you receive this, email sending is working correctly.';
    $mail->AltBody = 'This is a test email from HYPEZA. If you receive this, email sending is working correctly.';

    // Send the email and capture output buffer
    ob_start();
    $result = $mail->send();
    $debug_output = ob_get_clean();

    log_message("Email sending attempted. Success: " . ($result ? 'Yes' : 'No'));

    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully to ' . $test_email,
        'debug_output' => $debug_output
    ]);

} catch (Exception $e) {
    log_message("ERROR: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>