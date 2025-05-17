<?php
session_start();

// Comprehensive error handling and logging setup
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_errors.log');

// Log start of login process with timestamp
error_log('=== LOGIN ATTEMPT STARTED AT ' . date('Y-m-d H:i:s') . ' ===');

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set content type for AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
}

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

    // Create connection with SSL
    $conn = mysqli_init();

    if (!$conn) {
        throw new Exception("mysqli_init failed");
    }

    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    error_log('Database connection successful');

    // Check if required POST parameters exist
    if (empty($_POST['mail']) || empty($_POST['password'])) {
        throw new Exception("Missing required parameters");
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

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            error_log('Password verified successfully');

            // Success - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['role'] = $user['role'];

            $redirect_url = $user['role'] === 'admin' ? 'admin_dashboard.php' : 'home.php';

            if ($isAjax) {
                // Return JSON for AJAX requests
                echo json_encode([
                    'success' => true,
                    'redirect' => $redirect_url
                ]);
            } else {
                // Direct redirect for regular form submissions
                header("Location: " . $redirect_url);
                exit;
            }
        } else {
            $error_message = 'Mot de passe incorrect';
            if ($isAjax) {
                echo json_encode(['error' => $error_message]);
            } else {
                $_SESSION['login_error'] = $error_message;
                header("Location: connexion2.html");
                exit;
            }
        }
    } else {
        $error_message = 'Utilisateur non trouvé';
        if ($isAjax) {
            echo json_encode(['error' => $error_message]);
        } else {
            $_SESSION['login_error'] = $error_message;
            header("Location: connexion2.html");
            exit;
        }
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error
    error_log('LOGIN ERROR: ' . $e->getMessage());

    $error_message = 'Une erreur est survenue. Veuillez réessayer plus tard.';

    if ($isAjax) {
        echo json_encode([
            'error' => $error_message,
            'debug' => $e->getMessage() // Remove in production
        ]);
    } else {
        $_SESSION['login_error'] = $error_message;
        header("Location: connexion2.html");
        exit;
    }

    // Close connection if it exists
    if (isset($conn) && $conn) {
        try { $conn->close(); } catch (Exception $closeError) { }
    }
}

error_log('=== LOGIN ATTEMPT COMPLETED AT ' . date('Y-m-d H:i:s') . ' ===');