<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$user = "root";
$pass = "Aminezh-263@";
$db = "users_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérification de l'ID du ticket
if (!isset($_GET['id'])) {
    die("ID du ticket manquant.");
}

$ticket_id = $_GET['id'];

// Récupération des informations du ticket
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    die("Ticket introuvable ou accès non autorisé.");
}

// Mise à jour du ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE tickets SET title = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $title, $description, $ticket_id, $user_id);
    $stmt->execute();

    header("Location: view_tickets.php?status=updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: rgb(200,155,60);
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: rgb(200,155,60);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #d4a349;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Modifier le Ticket</h1>
    <form method="POST">
        <div class="form-group">
            <label for="title">Titre</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($ticket['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($ticket['description']) ?></textarea>
        </div>
        <button type="submit" class="btn">Mettre à jour</button>
    </form>
</div>
</body>
</html>