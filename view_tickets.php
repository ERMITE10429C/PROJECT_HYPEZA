<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$user = "root";
$pass = "root";
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

// Gestion de la recherche
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC");
    $search_param = '%' . $search . '%';
    $stmt->bind_param("iss", $user_id, $search_param, $search_param);
} else {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

// Suppression d'un ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket_id'])) {
    $delete_ticket_id = $_POST['delete_ticket_id'];

    // Supprimer les réponses associées au ticket
    $stmt = $conn->prepare("DELETE FROM ticket_responses WHERE ticket_id = ?");
    $stmt->bind_param("i", $delete_ticket_id);
    $stmt->execute();

    // Supprimer le ticket
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_ticket_id, $user_id);
    $stmt->execute();

    // Rediriger après suppression
    header("Location: view_tickets.php?status=deleted");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: rgb(200,155,60);
            color: white;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .btn-edit {
            background-color: #007bff;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-back {
            background-color: #6c757d;
            margin-top: 20px;
            display: inline-block;
        }
        .search-form {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-input {
            padding: 10px;
            width: 70%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-button {
            padding: 10px 20px;
            background-color: rgb(200,155,60);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Mes Tickets</h1>
    <form method="GET" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Rechercher un ticket..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="search-button">Rechercher</button>
    </form>
    <?php if (count($tickets) > 0): ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Image</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?= htmlspecialchars($ticket['id']) ?></td>
                    <td><?= htmlspecialchars($ticket['title']) ?></td>
                    <td><?= htmlspecialchars($ticket['description']) ?></td>
                    <td>
                        <?php if ($ticket['image_path']): ?>
                            <a href="<?= htmlspecialchars($ticket['image_path']) ?>" target="_blank">Voir</a>
                        <?php else: ?>
                            Aucun fichier
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($ticket['status']) ?></td>
                    <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                    <td class="actions">
                        <a href="edit_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-edit">Modifier</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_ticket_id" value="<?= $ticket['id'] ?>">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun ticket trouvé pour votre recherche.</p>
    <?php endif; ?>
    <a href="espace_client.php" class="btn btn-back">Retour à l'espace client</a>
</div>
</body>
</html>