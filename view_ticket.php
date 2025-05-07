<?php
//Ce fichier permet l'utilsateur de voir les détails et la réponse à propos son ticket.
session_start();
require_once 'config.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

// Vérification de l'ID du ticket
if (!isset($_GET['id'])) {
    die("ID du ticket manquant.");
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Récupération des informations du ticket
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket introuvable ou accès non autorisé.");
}

// Récupération des réponses du ticket
$stmt = $pdo->prepare("SELECT r.response, r.created_at, u.firstname, u.lastname 
                       FROM ticket_responses r 
                       JOIN users u ON r.admin_id = u.id 
                       WHERE r.ticket_id = ? 
                       ORDER BY r.created_at ASC");
$stmt->execute([$ticket_id]);
$responses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Ticket</title>
    <style>
        /* Styles existants */
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
            max-width: 800px;
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

        .ticket-details, .responses {
            margin-bottom: 20px;
        }

        .ticket-details p, .responses p {
            margin: 10px 0;
            font-size: 1rem;
            color: var(--dark-color);
        }

        .ticket-details strong, .responses strong {
            color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--light-color);
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            text-align: center;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #d4a349;
        }

        .btn-secondary {
            background-color: #555;
        }

        .btn-secondary:hover {
            background-color: #333;
        }

        .image-preview {
            margin-top: 20px;
            text-align: center;
        }

        .image-preview img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .response-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .response-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .response-item small {
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Détails du Ticket</h1>
    <div class="ticket-details">
        <p><strong>Titre :</strong> <?= htmlspecialchars($ticket['title']) ?></p>
        <p><strong>Description :</strong> <?= htmlspecialchars($ticket['description']) ?></p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($ticket['status']) ?></p>
    </div>

    <?php if ($ticket['image_path']): ?>
        <div class="image-preview">
            <p><strong>Image :</strong></p>
            <a href="/<?= htmlspecialchars($ticket['image_path']) ?>" target="_blank">
                <img src="/<?= htmlspecialchars($ticket['image_path']) ?>" alt="Image du ticket">
            </a>
        </div>
    <?php endif; ?>

    <div class="responses">
        <h2>Réponses</h2>
        <?php if (count($responses) > 0): ?>
            <?php foreach ($responses as $response): ?>
                <div class="response-item">
                    <strong><?= htmlspecialchars($response['firstname'] . ' ' . $response['lastname']) ?> :</strong>
                    <p><?= htmlspecialchars($response['response']) ?></p>
                    <small>Répondu le : <?= htmlspecialchars($response['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune réponse pour ce ticket pour le moment.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <a href="view_tickets.php" class="btn btn-secondary">Retour à la liste des tickets</a>
    </div>
</div>
</body>
</html>