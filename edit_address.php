<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['address_id'])) {
    header("Location: espace_client.php");
    exit();
}

// Connexion à la base de données Azure MySQL
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate - try both locations
$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

// Choose the certificate that exists
$ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

// Create connection with SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

$address_id = intval($_POST['address_id']);
$user_id = $_SESSION['user_id'];
$address_line1 = $_POST['address_line1'];
$address_line2 = $_POST['address_line2'] ?? '';
$city = $_POST['city'];
$postal_code = $_POST['postal_code'];
$country = $_POST['country'];
$is_default = isset($_POST['is_default']) ? 1 : 0;

if ($is_default) {
    // Fix for security: use prepared statement instead of direct variable in query
    $stmt_default = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
    $stmt_default->bind_param("i", $user_id);
    $stmt_default->execute();
    $stmt_default->close();
}

$stmt = $conn->prepare("UPDATE addresses SET address_line1 = ?, address_line2 = ?, city = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sssssiis", $address_line1, $address_line2, $city, $postal_code, $country, $is_default, $address_id, $user_id);

if ($stmt->execute()) {
    header("Location: espace_client.php#addresses");
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>