<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_ids'])) {
    header("Location: espace_client.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$notification_ids = array_map('intval', $_POST['notification_ids']);

// Connexion à la base de données
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";
$ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

// Mettre à jour les notifications sélectionnées
$placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
$types = str_repeat('i', count($notification_ids) + 1); // +1 pour user_id

$query = "UPDATE tickets SET has_new_response = 0 
          WHERE id IN ($placeholders) AND user_id = ?";

$stmt = $conn->prepare($query);

// Combiner les IDs des notifications et l'ID utilisateur pour bind_param
$params = array_merge($notification_ids, [$user_id]);
$stmt->bind_param($types, ...$params);
$stmt->execute();

// Redirection avec message de succès
header("Location: espace_client.php?msg=notifications_marked");
exit();
?>