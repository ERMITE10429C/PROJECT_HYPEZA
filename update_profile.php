<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "Aminezh-263@";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];


if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET firstname=?, lastname=?, email=?, phone=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $email, $phone, $hashed_password, $user_id);
} else {
    $sql = "UPDATE users SET firstname=?, lastname=?, email=?, phone=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $phone, $user_id);
}

if ($stmt->execute()) {
    echo "Mise à jour réussie. <a href='espace_client.php'>Retour à mon espace</a>";
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
