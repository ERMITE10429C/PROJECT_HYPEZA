<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "users_db");
if ($conn->connect_error) {
    die("Erreur : " . $conn->connect_error);
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

// Récupère les infos de l’utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
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
