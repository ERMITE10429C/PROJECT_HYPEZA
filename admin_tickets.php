<?php
session_start();
require_once 'config.php';

// Vérification du rôle administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// Récupération des tickets
$stmt = $pdo->query("SELECT t.*, u.firstname, u.lastname FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Tickets</title>
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
            --background-color: #f5f5f5;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
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
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: var(--light-color);
        }

        .btn-respond {
            background-color: #007bff;
        }

        .btn:hover {
            opacity: 0.9;
        }
        .btn-back {
            background-color: #6c757d;
            margin-top: 20px;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestion des Tickets</h1>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Utilisateur</th>
            <th>Titre</th>
            <th>Description</th>
            <th>Image</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td><?= htmlspecialchars($ticket['id']) ?></td>
                <td><?= htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']) ?></td>
                <td><?= htmlspecialchars($ticket['title']) ?></td>
                <td><?= htmlspecialchars($ticket['description']) ?></td>
                <td>
                    <?php if ($ticket['image_path']): ?>
                        <a href="<?= htmlspecialchars($ticket['image_path']) ?>" target="_blank">Voir l'image</a>
                    <?php else: ?>
                        Aucun fichier
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
                <td>
                    <a href="respond_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-respond">Répondre</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-back">Retour au tableau de bord</a>
</div>
</body>
</html>