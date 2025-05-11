<?php
    // test_email.php - Enhanced debugging version
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    // Set proper content type for AJAX response
    header('Content-Type: application/json');

    // Create log file with proper path for Azure
    $log_file = __DIR__ . '/email_test_log.txt';
    function log_message($message) {
        global $log_file;
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
    }

    log_message("Test email script executed on " . php_uname());

    // Array to store all debug messages
    $debug_messages = [];
    $debug_collect = function($str, $level) use (&$debug_messages) {
        $debug_messages[] = $str;
        // Also log to file
        log_message("DEBUG[$level]: $str");
    };

    try {
        // Check environment
        log_message("PHP Version: " . phpversion());
        log_message("Extensions: " . implode(", ", get_loaded_extensions()));

        // Check if Composer autoloader exists - try multiple paths
        $autoloader_paths = [
            __DIR__ . '/vendor/autoload.php',
            __DIR__ . '/../vendor/autoload.php',
            'vendor/autoload.php'
        ];

        $autoloader_found = false;
        foreach ($autoloader_paths as $path) {
            log_message("Checking autoloader at: " . $path);
            if (file_exists($path)) {
                require $path;
                log_message("Autoloader found and included: " . $path);
                $autoloader_found = true;
                break;
            }
        }

        if (!$autoloader_found) {
            throw new Exception("Vendor autoload.php not found. Make sure Composer dependencies are installed.");
        }

        // Simple test without sending actual email
        if (isset($_GET['check_only'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Test file accessed successfully',
                'server_time' => date('Y-m-d H:i:s'),
                'phpmailer_available' => class_exists('PHPMailer\PHPMailer\PHPMailer'),
                'server_info' => [
                    'php_version' => phpversion(),
                    'os' => php_uname(),
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'
                ]
            ]);
            log_message("Basic check completed");
            exit;
        }

        // If we're here, we're attempting to send a test email
        $mail = new PHPMailer(true);
        log_message("PHPMailer instance created");

        // Maximum debug level for most detailed output
        $mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL; // Maximum debug level
        $mail->Debugoutput = $debug_collect;

        // Server settings (Titan SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.titan.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'team@hypza.tech';
        $mail->Password   = 'azerty@123'; // Updated password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->Timeout    = 30; // 30 second timeout
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
        $mail->Subject = 'HYPEZA Email Test from Azure ' . date('Y-m-d H:i:s');
        $mail->Body    = 'This is a test email from HYPEZA. If you receive this, email sending is working correctly.<br>Server time: ' . date('Y-m-d H:i:s');
        $mail->AltBody = 'This is a test email from HYPEZA. If you receive this, email sending is working correctly. Server time: ' . date('Y-m-d H:i:s');

        // Send the email
        log_message("Attempting to send email to {$test_email}...");
        $result = $mail->send();
        log_message("Email sending result: " . ($result ? "Success" : "Failed: " . $mail->ErrorInfo));

        // Format debug messages for display
        $debug_html = implode("<br>\n", $debug_messages);

        echo json_encode([
            'success' => true,
            'message' => 'Test email sent successfully to ' . $test_email,
            'smtp_debug' => $debug_html,
            'log_location' => $log_file,
            'server_info' => [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ]);

    } catch (Exception $e) {
        log_message("ERROR: " . $e->getMessage());
        log_message("Stack trace: " . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'smtp_debug' => implode("<br>\n", $debug_messages),
            'log_location' => $log_file
        ]);
    }
    ?>