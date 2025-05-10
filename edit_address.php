<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['address_id'])) {
    header("Location: espace_client.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "root";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
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
    $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
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