<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
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

// Récupération des données du formulaire
$user_id = $_SESSION['user_id'];
$address_line1 = $_POST['address_line1'];
$address_line2 = $_POST['address_line2'] ?? '';
$city = $_POST['city'];
$postal_code = $_POST['postal_code'];
$country = $_POST['country'];
$is_default = isset($_POST['is_default']) ? 1 : 0;

// Si l'adresse est définie par défaut, on met à jour les autres adresses
if ($is_default) {
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Insertion de la nouvelle adresse
$stmt = $conn->prepare("INSERT INTO addresses (user_id, address_line1, address_line2, city, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssi", $user_id, $address_line1, $address_line2, $city, $postal_code, $country, $is_default);

if ($stmt->execute()) {
    header("Location: espace_client.php#addresses");
    exit();
} else {
    echo "Erreur lors de l'ajout de l'adresse : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>