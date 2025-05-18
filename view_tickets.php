<?php
session_start();

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
<html lang="eng">
<head>
    <meta charset="UTF-8">
    <title>Mes Tickets</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: rgb(200,155,60);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .search-form {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .search-input {
            padding: 12px;
            width: 50%;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-button {
            padding: 12px 24px;
            background-color: rgb(200,155,60);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: rgb(180,135,40);
        }

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .ticket-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .ticket-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .ticket-title {
            font-size: 18px;
            font-weight: 600;
            color: rgb(200,155,60);
            margin-bottom: 10px;
        }

        .ticket-preview {
            color: #666;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #888;
        }

        .ticket-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-en-attente { background-color: #fff3cd; color: #856404; }
        .status-en-cours { background-color: #cce5ff; color: #004085; }
        .status-resolu { background-color: #d4edda; color: #155724; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 50px auto;
            padding: 25px;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        .btn-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn-edit { background-color: #007bff; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-back { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>Mes Tickets</h1>

    <form method="GET" class="search-form">
        <input type="text" name="search" class="search-input"
               placeholder="Rechercher un ticket..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="search-button">Rechercher</button>
    </form>

    <?php if (count($tickets) > 0): ?>
        <div class="tickets-grid">
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card" onclick="showTicketDetails(<?= htmlspecialchars(json_encode($ticket)) ?>)">
                    <div class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></div>
                    <div class="ticket-preview"><?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?>...</div>
                    <div class="ticket-meta">
                            <span class="ticket-status status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
                                <?= htmlspecialchars($ticket['status']) ?>
                            </span>
                        <span><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucun ticket trouvé pour votre recherche.</p>
    <?php endif; ?>

    <a href="espace_client.php" class="btn btn-back">Retour à l'espace client</a>
</div>

<!-- Modal pour afficher les détails du ticket -->
<div id="ticketModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle"></h2>
        <div id="modalDescription"></div>
        <div id="modalImage"></div>
        <div id="modalMeta"></div>
        <div class="btn-actions" id="modalActions"></div>
    </div>
</div>

<script>
    function showTicketDetails(ticket) {
        document.getElementById('modalTitle').textContent = ticket.title;
        document.getElementById('modalDescription').textContent = ticket.description;
        document.getElementById('modalMeta').innerHTML = `
                <p><strong>Statut:</strong> ${ticket.status}</p>
                <p><strong>Date de création:</strong> ${new Date(ticket.created_at).toLocaleDateString()}</p>
            `;

        if (ticket.image_path) {
            document.getElementById('modalImage').innerHTML = `
                    <img src="${ticket.image_path}" alt="Image du ticket" style="max-width: 100%; margin: 10px 0;">
                `;
        } else {
            document.getElementById('modalImage').innerHTML = '<p>Aucune image associée</p>';
        }

        document.getElementById('modalActions').innerHTML = `
                <a href="edit_ticket.php?id=${ticket.id}" class="btn btn-edit">Modifier</a>
                <button onclick="deleteTicket(${ticket.id})" class="btn btn-delete">Supprimer</button>
            `;

        document.getElementById('ticketModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('ticketModal').style.display = 'none';
    }

    function deleteTicket(ticketId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="delete_ticket_id" value="${ticketId}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('ticketModal')) {
            closeModal();
        }
    }
</script>
</body>
</html>