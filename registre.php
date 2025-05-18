<?php
// Comprehensive error handling and logging setup
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/register_errors.log');

// Log start of registration process with timestamp
error_log('=== REGISTRATION ATTEMPT STARTED AT ' . date('Y-m-d H:i:s') . ' ===');

// Azure MySQL connection details
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

try {
    // Log POST data for debugging (excluding password)
    $safe_post = $_POST;
    if (isset($safe_post['password'])) $safe_post['password'] = '[REDACTED]';
    error_log('POST data received: ' . json_encode($safe_post));

    // Path to SSL certificate - we know this works based on testing
    $ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

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

    // Validate input data (comprehensive validation)
    $required_fields = ['first_name', 'last_name', 'email', 'password'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        error_log('Missing required fields: ' . implode(', ', $missing_fields));
        die("Les champs suivants sont requis : " . implode(', ', $missing_fields));
    }

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid email format: ' . $email);
        die("Format d'email invalide");
    }

    // Log sanitized input (excluding password)
    error_log('Sanitized input: firstname=' . $firstname . ', lastname=' . $lastname .
        ', email=' . $email . ', phone=' . $phone);

    // Debugging - Check database table structure before proceeding
    $table_check_query = "SHOW TABLES LIKE 'users'";
    $table_check_result = $conn->query($table_check_query);

    if ($table_check_result->num_rows == 0) {
        error_log('Table "users" does not exist!');
        throw new Exception('Table "users" does not exist in the database');
    }

    error_log('Table "users" exists');

    $table_structure_query = "DESCRIBE users";
    $table_structure_result = $conn->query($table_structure_query);

    if (!$table_structure_result) {
        error_log('Failed to retrieve table structure: ' . $conn->error);
        throw new Exception('Failed to retrieve table structure: ' . $conn->error);
    }

    $columns = [];
    while ($column = $table_structure_result->fetch_assoc()) {
        $columns[$column['Field']] = $column;
    }

    error_log('Table structure: ' . json_encode($columns));

    // Check if the necessary columns exist
    $required_columns = ['id', 'firstname', 'lastname', 'email', 'phone', 'password'];
    foreach ($required_columns as $column) {
        if (!isset($columns[$column])) {
            error_log('Missing required column in users table: ' . $column);
            throw new Exception('The users table is missing a required column: ' . $column);
        }
    }

    // Check if email already exists
    error_log('Checking if email already exists: ' . $email);
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);

    if (!$check_stmt) {
        error_log('Prepare statement failed: ' . $conn->error);
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    $check_stmt->bind_param("s", $email);

    if (!$check_stmt->execute()) {
        error_log('Execute check query failed: ' . $check_stmt->error);
        throw new Exception('Execute check query failed: ' . $check_stmt->error);
    }

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        error_log('Email already exists: ' . $email);
        die("Cet email est déjà utilisé. Veuillez en choisir un autre.");
    }

    $check_stmt->close();
    error_log('Email check passed, email is available');

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    error_log('Password hashed successfully');

    // Insert new user
    error_log('Preparing to insert new user');
    $sql = "INSERT INTO users (firstname, lastname, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('Prepare insert statement failed: ' . $conn->error);
        throw new Exception('Prepare insert statement failed: ' . $conn->error);
    }

    $stmt->bind_param("sssss", $firstname, $lastname, $email, $phone, $hashed_password);
    error_log('Insert statement prepared and parameters bound');

    try {
        if (!$stmt->execute()) {
            error_log('Insert failed: ' . $stmt->error);
            throw new Exception('Failed to insert new user: ' . $stmt->error);
        }

        $new_user_id = $stmt->insert_id;
        error_log('User registered successfully with ID: ' . $new_user_id);

        // Redirect to login page
        error_log('Redirecting to login page');
        header("Location: connexion2.html");
        exit();
    } catch (Exception $exec_error) {
        error_log('Exception during execute: ' . $exec_error->getMessage());
        throw $exec_error;
    }

    $stmt->close();
    $conn->close();
    error_log('Connection closed normally');

} catch (Exception $e) {
    // Log the error and return a user-friendly message
    error_log('REGISTRATION ERROR: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    echo "Une erreur est survenue lors de l'inscription. Veuillez réessayer plus tard.<br>";
    echo "Détails de l'erreur (à supprimer en production): " . $e->getMessage(); // Remove in production

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

error_log('=== REGISTRATION ATTEMPT COMPLETED AT ' . date('Y-m-d H:i:s') . ' ===');