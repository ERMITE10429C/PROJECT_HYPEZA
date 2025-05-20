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
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* En-tête amélioré */
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, rgb(200,155,60), #f1c40f);
        }

        /* Barre de recherche améliorée */
        .search-form {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .search-input {
            padding: 15px 20px;
            width: 60%;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .search-input:focus {
            border-color: rgb(200,155,60);
            outline: none;
            box-shadow: 0 2px 10px rgba(200,155,60,0.2);
        }

        .search-button {
            padding: 15px 30px;
            background: linear-gradient(45deg, rgb(200,155,60), #f1c40f);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(200,155,60,0.3);
        }

        /* Cartes de tickets améliorées */
        .ticket-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-color: rgb(200,155,60);
        }

        /* Statuts améliorés */
        .ticket-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-en-attente {
            background-color: #ffeeba;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-en-cours {
            background-color: #b8e7ff;
            color: #004085;
            border: 1px solid #b8e7ff;
        }

        .status-resolu {
            background-color: #c3e6cb;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Modal amélioré */
        .modal-content {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(45deg, #2980b9, #3498db);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(45deg, #c0392b, #e74c3c);
            color: white;
        }

        .btn-back {
            background: linear-gradient(45deg, #7f8c8d, #95a5a6);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

    </style>
</head>
<body>
<div class="container">
    <h1>Bonjour <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'cher utilisateur' ?> 👋</h1>

    <div class="stats-container" style="text-align: center; margin-bottom: 30px;">
        <p>Vous avez actuellement <?= count($tickets) ?> ticket(s)</p>
    </div>


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
    // Animation d'entrée pour les cartes
    document.querySelectorAll('.ticket-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Amélioration de l'affichage modal
    function showTicketDetails(ticket) {
        const modal = document.getElementById('ticketModal');
        modal.style.display = 'block';

        // Animation d'entrée du modal
        const modalContent = modal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(0.7)';
        modalContent.style.opacity = '0';

        setTimeout(() => {
            modalContent.style.transition = 'all 0.3s ease';
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 50);

        // Contenu du modal avec formatage amélioré
        document.getElementById('modalTitle').innerHTML = `
            <h2 style="color: #2c3e50; margin-bottom: 20px;">
                ${ticket.title}
            </h2>
        `;


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