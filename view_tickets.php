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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Tickets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .search-form {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .search-input {
            padding: 12px 12px 12px 40px;
            width: 50%;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: rgb(200,155,60);
            outline: none;
            box-shadow: 0 0 10px rgba(200,155,60,0.2);
        }

        .search-icon {
            position: absolute;
            left: 26%;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .search-button {
            padding: 12px 30px;
            background-color: rgb(200,155,60);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }

        .search-button:hover {
            background-color: rgb(180,135,40);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .ticket-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #eee;
            position: relative;
            overflow: hidden;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .ticket-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: rgb(200,155,60);
        }

        .ticket-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-right: 60px;
        }

        .ticket-preview {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 14px;
        }

        .ticket-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .ticket-date {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 13px;
        }

        .ticket-date i {
            margin-right: 5px;
            color: rgb(200,155,60);
        }

        .ticket-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Statuts personnalisés */
        .status-en-attente {
            background-color: #fff3e0;
            color: #f57c00;
            border: 1px solid #ffe0b2;
        }

        .status-en-cours {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }

        .status-resolu {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .status-urgent {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        /* Modal améliorée */
        .modal-content {
            position: relative;
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 24px;
            color: #2c3e50;
            margin: 0;
        }

        .modal-close {
            position: absolute;
            right: 25px;
            top: 25px;
            width: 30px;
            height: 30px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: #e9ecef;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 20px 0;
        }

        .modal-description {
            line-height: 1.6;
            color: #4a4a4a;
            margin-bottom: 20px;
        }

        .modal-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .modal-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-meta-item i {
            color: rgb(200,155,60);
        }

        .btn-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn i {
            font-size: 16px;
        }

        .btn-edit {
            background-color: #2196f3;
            color: white;
        }

        .btn-edit:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: #757575;
            color: white;
        }

        .btn-back:hover {
            background-color: #616161;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-ticket-alt"></i> Mes Tickets</h1>

    <form method="GET" class="search-form">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" class="search-input"
               placeholder="Rechercher un ticket..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="search-button">
            <i class="fas fa-search"></i> Rechercher
        </button>
    </form>

    <?php if (count($tickets) > 0): ?>
        <div class="tickets-grid">
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card" onclick="showTicketDetails(<?= htmlspecialchars(json_encode($ticket)) ?>)">
                    <div class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></div>
                    <div class="ticket-preview"><?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?>...</div>
                    <div class="ticket-meta">
                        <div class="ticket-date">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d/m/Y', strtotime($ticket['created_at'])) ?>
                        </div>
                        <span class="ticket-status status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
                                <i class="fas fa-circle"></i>
                                <?= htmlspecialchars($ticket['status']) ?>
                            </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-search" style="font-size: 48px; color: #ccc;"></i>
            <p style="color: #666; margin-top: 20px;">Aucun ticket trouvé pour votre recherche.</p>
        </div>
    <?php endif; ?>

    <a href="espace_client.php" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Retour à l'espace client
    </a>
</div>

<!-- Modal pour afficher les détails du ticket -->
<div id="ticketModal" class="modal">
    <div class="modal-content">
        <div class="modal-close" onclick="closeModal()">
            <i class="fas fa-times"></i>
        </div>
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle"></h2>
        </div>
        <div class="modal-body">
            <div class="modal-description" id="modalDescription"></div>
            <div id="modalImage"></div>
            <div class="modal-meta" id="modalMeta"></div>
            <div class="btn-actions" id="modalActions"></div>
        </div>
    </div>
</div>

<script>
    function showTicketDetails(ticket) {
        document.getElementById('modalTitle').textContent = ticket.title;
        document.getElementById('modalDescription').textContent = ticket.description;
        document.getElementById('modalMeta').innerHTML = `
                <div class="modal-meta-item">
                    <i class="fas fa-tag"></i>
                    <span><strong>Statut:</strong>
                        <span class="ticket-status status-${ticket.status.toLowerCase().replace(' ', '-')}">
                            ${ticket.status}
                        </span>
                    </span>
                </div>
                <div class="modal-meta-item">
                    <i class="far fa-calendar-alt"></i>
                    <span><strong>Créé le:</strong> ${new Date(ticket.created_at).toLocaleDateString()}</span>
                </div>
            `;

        if (ticket.image_path) {
            document.getElementById('modalImage').innerHTML = `
                    <img src="${ticket.image_path}" alt="Image du ticket" style="max-width: 100%; border-radius: 8px; margin: 15px 0;">
                `;
        } else {
            document.getElementById('modalImage').innerHTML = '';
        }

        document.getElementById('modalActions').innerHTML = `
                <a href="edit_ticket.php?id=${ticket.id}" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <button onclick="deleteTicket(${ticket.id})" class="btn btn-delete">
                    <i class="fas fa-trash-alt"></i> Supprimer
                </button>
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