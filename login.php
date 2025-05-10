<?php
header('Content-Type: application/json');
session_start();

// Comprehensive error handling and logging setup
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_errors.log');

// Log start of login process with timestamp
error_log('=== LOGIN ATTEMPT STARTED AT ' . date('Y-m-d H:i:s') . ' ===');

// Database connection parameters
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate
$ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

try {
    // Log certificate information
    error_log('SSL certificate path: ' . $ssl_cert);
    error_log('SSL certificate exists: ' . (file_exists($ssl_cert) ? 'Yes' : 'No'));
    error_log('SSL certificate readable: ' . (is_readable($ssl_cert) ? 'Yes' : 'No'));
    error_log('SSL certificate size: ' . (file_exists($ssl_cert) ? filesize($ssl_cert) : 0) . ' bytes');

    // Create connection with SSL
    $conn = mysqli_init();

    if (!$conn) {
        throw new Exception("mysqli_init failed");
    }

    error_log('mysqli_init successful');

    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
    error_log('SSL settings applied');

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    error_log('Database connection successful');

    // Check if required POST parameters exist
    error_log('POST data received: ' . json_encode($_POST));

    if (empty($_POST['mail']) || empty($_POST['password'])) {
        throw new Exception("Missing required parameters: " .
            (empty($_POST['mail']) ? 'email ' : '') .
            (empty($_POST['password']) ? 'password' : ''));
    }

    $email = trim($_POST['mail']);
    $password = $_POST['password'];

    error_log('Attempting login for email: ' . $email);

    // Prepare and execute query
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    error_log('SQL statement prepared');

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    error_log('SQL statement executed');

    $result = $stmt->get_result();
    error_log('Result fetched, found rows: ' . $result->num_rows);

    if ($user = $result->fetch_assoc()) {
        error_log('User found, verifying password');

        // Log user data for debugging (excluding password)
        $safe_user = $user;
        if (isset($safe_user['password'])) $safe_user['password'] = '[REDACTED]';
        error_log('User data: ' . json_encode($safe_user));

        if (password_verify($password, $user['password'])) {
            error_log('Password verified successfully');

            // Success - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['role'] = $user['role'];

            error_log('Session variables set - user_id: ' . $_SESSION['user_id'] .
                ', firstname: ' . $_SESSION['firstname'] .
                ', role: ' . $_SESSION['role']);

            echo json_encode([
                'success' => true,
                'redirect' => $user['role'] === 'admin' ? 'admin_dashboard.php' : 'home.php'
            ]);

            error_log('Login successful, JSON response sent');
        } else {
            error_log('Password verification failed');
            echo json_encode(['error' => 'Mot de passe incorrect']);
        }
    } else {
        error_log('User not found with email: ' . $email);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
    }

    $stmt->close();
    $conn->close();
    error_log('Connection closed normally');

} catch (Exception $e) {
    // Log the error and return a user-friendly message
    error_log('LOGIN ERROR: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    echo json_encode([
        'error' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
        'debug' => $e->getMessage() // Remove in production
    ]);

    // Close connection if it exists
    if (isset($conn) && $conn) {
        try {
            $conn->close();
            error_log('Connection closed after error');
        } catch (Exception $closeError) {
            error_log('Error closing connection: ' . $closeError->getMessage());
        }
    }
}

error_log('=== LOGIN ATTEMPT COMPLETED AT ' . date('Y-m-d H:i:s') . ' ===');