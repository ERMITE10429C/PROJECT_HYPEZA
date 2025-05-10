<?php
header('Content-Type: application/json');
session_start();

$host = "localhost";
$user = "root";
$pass = "root";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => "Connexion échouée : " . $conn->connect_error]));
}

$email = $_POST['mail'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['role'] = $user['role'];

        echo json_encode([
            'success' => true,
            'redirect' => $user['role'] === 'admin' ? 'admin_dashboard.php' : 'home.php'
        ]);
    } else {
        echo json_encode(['error' => 'Mot de passe incorrect']);
    }
} else {
    echo json_encode(['error' => 'Utilisateur non trouvé']);
}

$stmt->close();
$conn->close();