<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID manquant.");
}

$conn = new mysqli("localhost", "root", "root", "users_db");
if ($conn->connect_error) {
    die("Erreur : " . $conn->connect_error);
}

$id = $_GET['id'];

// Ne jamais permettre de supprimer son propre compte admin par accident
if ($id == $_SESSION['user_id']) {
    die("Vous ne pouvez pas supprimer votre propre compte admin !");
}

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_dashboard.php");
exit();
?>
