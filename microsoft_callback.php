<?php
session_start();

// === CONFIGURATION ===
$client_id = 'afcc759f-baea-44df-9b5a-7fb7404d7094'; // ✅ Ton vrai Client ID
$client_secret = 'EzR8Q~rNfDQ-mYO3aH1jkVFnuF263yHCtdwRQbfy';                // ✅ Ton Secret généré dans Azure
$redirect_uri = 'http://localhost/microsoft_callback.php';
$token_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
$user_info_url = 'https://graph.microsoft.com/v1.0/me';

// === Étape 1 : récupérer le code de Microsoft ===
if (!isset($_GET['code'])) {
    die("Code d'autorisation manquant");
}

$code = $_GET['code'];

// === Étape 2 : échanger le code contre un token d'accès ===
$data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code',
    'scope' => 'https://graph.microsoft.com/user.read'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    die("Erreur lors de l'échange du code : " . $response);
}

// === Étape 3 : récupérer les infos utilisateur ===
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: Bearer " . $token['access_token']
    ]
];
$context = stream_context_create($opts);
$user_data = file_get_contents($user_info_url, false, $context);
$user = json_decode($user_data, true);

// === Connexion/inscription dans la BDD ===
$email = $user['mail'] ?? $user['userPrincipalName'];
$firstname = $user['givenName'] ?? '';
$lastname = $user['surname'] ?? '';

$conn = new mysqli("localhost", "root", "root", "users_db");

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($existing_user = $result->fetch_assoc()) {
    $_SESSION['user_id'] = $existing_user['id'];
    $_SESSION['firstname'] = $existing_user['firstname'];
    $_SESSION['role'] = $existing_user['role'];
} else {
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $firstname, $lastname, $email);
    $stmt->execute();

    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['firstname'] = $firstname;
    $_SESSION['role'] = 'user';
}

header("Location: home.php");
exit();
