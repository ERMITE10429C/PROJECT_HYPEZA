<?php
header('Content-Type: application/json');
session_start();

// Log access for debugging
error_log('Login.php accessed');

// Database connection parameters
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate - try both locations
$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

// Choose the certificate that exists
$ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

try {
    // Log which certificate is being used
    error_log('Using SSL certificate: ' . $ssl_cert);
    error_log('Certificate exists: ' . (file_exists($ssl_cert) ? 'Yes' : 'No'));

    // Create connection with SSL
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Check if required POST parameters exist
    if (!isset($_POST['mail']) || !isset($_POST['password'])) {
        throw new Exception("Missing required parameters");
    }

    $email = $_POST['mail'];
    $password = $_POST['password'];

    error_log('Attempting login for email: ' . $email);

    // Prepare and execute query
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Success - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['role'] = $user['role'];

            echo json_encode([
                'success' => true,
                'redirect' => $user['role'] === 'admin' ? 'admin_dashboard.php' : 'home.php'
            ]);
        } else {
            echo json_encode(['error' => 'Mot de passe incorrect']);
        }
    } else {
        echo json_encode(['error' => 'Utilisateur non trouvé']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error and return a user-friendly message
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['error' => 'Une erreur est survenue. Veuillez réessayer plus tard.']);

    // Close connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
}