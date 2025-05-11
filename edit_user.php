<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

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

// Vérifier que l'ID est défini
if (!isset($_GET['id'])) {
    die("ID utilisateur manquant.");
}

$id = $_GET['id'];

// Met à jour les données si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $role      = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $role, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php");
    exit();
}

// Récupère les infos de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Vérifier que l'utilisateur existe
if (!$user) {
    die("Utilisateur introuvable.");
}
?>

<h1>Modifier l'utilisateur</h1>
<form method="POST">
    <label>Prénom : <input name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>"></label><br>
    <label>Nom : <input name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>"></label><br>
    <label>Email : <input name="email" value="<?= htmlspecialchars($user['email']) ?>"></label><br>
    <label>Rôle :
        <select name="role">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label><br><br>
    <button type="submit">Enregistrer</button>
</form>