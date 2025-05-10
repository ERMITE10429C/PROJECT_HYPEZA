<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "root";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifiez si des notifications ont été sélectionnées
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_ids'])) {
    $notification_ids = $_POST['notification_ids'];

    // Préparez une requête pour mettre à jour les notifications sélectionnées
    $placeholders = implode(',', array_fill(0, count($notification_ids), '?'));
    $stmt = $conn->prepare("UPDATE tickets SET is_notified = 0 WHERE id IN ($placeholders)");

    // Liez les paramètres dynamiquement
    $types = str_repeat('i', count($notification_ids));
    $stmt->bind_param($types, ...$notification_ids);
    $stmt->execute();

    header("Location: espace_client.php?status=notifications_updated");
    exit();
} else {
    header("Location: espace_client.php?status=no_selection");
    exit();
}
?>