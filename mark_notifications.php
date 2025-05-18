<?php
// Create a new file: mark_as_read.php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['ticket_id'])) {
    header("Location: espace_client.php");
    exit();
}

$ticket_id = (int)$_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

// Database connection
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";
$ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

// Mark notification as read, and verify this belongs to the user
$stmt = $conn->prepare("UPDATE tickets SET has_new_response = 0 
                       WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();

header("Location: view_ticket.php?id=".$ticket_id);
exit();
?>