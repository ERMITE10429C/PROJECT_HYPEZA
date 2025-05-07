<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

// Connexion à la base de données
$host = "localhost";
$user = "root";
$pass = "Aminezh-263@";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
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