<?php
$host = "localhost";
$user = "root"; // ou ton nom d'utilisateur MySQL
$pass = "Aminezh-263@"; // ton mot de passe MySQL
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}



$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (firstname, lastname, email, phone, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $firstname, $lastname, $email, $phone, $password);


if ($stmt->execute()) {
    header("Location: connexion2.html");
    exit();
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$conn->close();

?>
