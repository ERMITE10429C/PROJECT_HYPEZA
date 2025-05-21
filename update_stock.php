<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// Use the same Azure MySQL connection details as in admin_dashboard.php
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';
$ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['product_id']);
    $new_stock = intval($_POST['new_stock']);
    $max_per_order = intval($_POST['max_per_order']);

    $stmt = $conn->prepare("UPDATE products SET stock = ?, max_per_order = ? WHERE id = ?");
    $stmt->bind_param("iii", $new_stock, $max_per_order, $id);
    $stmt->execute();
    $stmt->close();
}
header("Location: admin_dashboard.php#stock_manager");
exit();


?><?php
