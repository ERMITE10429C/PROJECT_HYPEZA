<?php
require_once 'vendor/autoload.php';
session_start();

if (!isset($_POST['credential'])) {
    echo "❌ Accès direct interdit : ce fichier doit être appelé par Google Login.";
    exit();
}

$client = new Google_Client();
$client->setClientId('951615846978-gc5ogjg33khp087k2044c3qtd9u9ql5a.apps.googleusercontent.com'); // Remplace par ton vrai client ID
$payload = $client->verifyIdToken($_POST['credential']);

if ($payload) {
    $email = $payload['email'];
    $firstname = $payload['given_name'];
    $lastname = $payload['family_name'];

    // Connexion à la base de données Azure MySQL
    $host = "hypezaserversql.mysql.database.azure.com";
    $user = "user";
    $pass = "HPL1710COMPAq";
    $db = "users_db";

    // Path to SSL certificate - try both locations
    $ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
    $ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

    // Choose the certificate that exists
    $ssl_cert = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

    // Create connection with SSL
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

    if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
        die("Erreur de connexion : " . mysqli_connect_error());
    }

    // Vérifie si l'utilisateur existe déjà
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Déjà inscrit
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Nouveau compte Google → inscription automatique
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $firstname, $lastname, $email);
        $stmt->execute();

        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['role'] = 'user';
    }

    // Redirection vers espace membre
    header("Location: home.php");
    exit();
} else {
    echo "Erreur Google Login.";
}