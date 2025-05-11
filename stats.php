<?php
header('Content-Type: application/json');

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
    echo json_encode(["error" => "Erreur de connexion: " . mysqli_connect_error()]);
    exit();
}

// Compter les utilisateurs
$res_users = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$users = $res_users->fetch_assoc()['total_users'];

// Compter les produits achetés (somme des quantités dans cart_items)
$res_orders = $conn->query("SELECT SUM(quantity) AS total_products FROM cart_items");
$products = $res_orders->fetch_assoc()['total_products'] ?? 0;

echo json_encode([
    "total_users" => $users,
    "total_products" => $products
]);