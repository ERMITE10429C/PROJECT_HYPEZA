<?php
// Azure MySQL connection details
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

try {
    // Create connection with SSL
    $conn = mysqli_init();

    // Try multiple certificate locations
    $ssl_cert_locations = [
        __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem',
        __DIR__ . '/DigiCertGlobalRootCA.crt.pem',
        __DIR__ . '/DigiCertGlobalRootCA.crt'
    ];

    $cert_found = false;

    foreach ($ssl_cert_locations as $cert_path) {
        if (file_exists($cert_path)) {
            error_log('Found SSL certificate at: ' . $cert_path);
            mysqli_ssl_set($conn, NULL, NULL, $cert_path, NULL, NULL);
            $cert_found = true;
            break;
        }
    }

    // If no certificate found, try with empty SSL settings (may work for some configurations)
    if (!$cert_found) {
        error_log('No SSL certificate found, trying with empty SSL settings');
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    }

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        die("Connexion échouée : " . mysqli_connect_error());
    }

    // Validate input data (basic validation)
    if (!isset($_POST['firstname']) || !isset($_POST['lastname']) ||
        !isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['password'])) {
        die("Tous les champs sont requis");
    }

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        die("Cet email est déjà utilisé. Veuillez en choisir un autre.");
    }

    $check_stmt->close();

    // Insert new user
    $sql = "INSERT INTO users (firstname, lastname, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstname, $lastname, $email, $phone, $password);

    if ($stmt->execute()) {
        header("Location: connexion2.html");
        exit();
    } else {
        echo "Erreur : " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error and return a user-friendly message
    error_log('Registration error: ' . $e->getMessage());
    echo "Une erreur est survenue lors de l'inscription. Veuillez réessayer plus tard.";

    // Close connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
}