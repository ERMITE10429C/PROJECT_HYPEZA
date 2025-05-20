<?php
    session_start();

    // Vérification du rôle administrateur
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: connexion2.html");
        exit();
    }

    // Database connection parameters
    $host = "hypezaserversql.mysql.database.azure.com";
    $user = "user";
    $pass = "HPL1710COMPAq";
    $db = "users_db";

    // Path to SSL certificate
    $ssl_cert = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';

    try {
        // Create connection with SSL
        $conn = mysqli_init();

        if (!$conn) {
            throw new Exception("mysqli_init failed");
        }

        mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

        if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
            throw new Exception("Connection failed: " . mysqli_connect_error());
        }

        // Récupération des tickets
        $query = "SELECT t.*, u.firstname, u.lastname FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($conn));
        }

        $tickets = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tickets[] = $row;
        }

        mysqli_free_result($result);
        mysqli_close($conn);
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
    ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Tickets - Administration</title>
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

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
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
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-color: rgb(200,155,60);
        }

        .ticket-title {
            font-size: 18px;
            font-weight: 600;
            color: rgb(200,155,60);
            margin-bottom: 10px;
        }

        .ticket-user {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
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
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            margin: 50px auto;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-respond {
            background: linear-gradient(45deg, #2980b9, #3498db);
            color: white;
        }

        .btn-back {
            background: linear-gradient(45deg, #7f8c8d, #95a5a6);
            color: white;
            margin-top: 20px;
        }

        .stats-container {
            text-align: center;
            margin-bottom: 30px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestion des Tickets - Administration</h1>

    <div class="stats-container">
        <p>Il y a actuellement <?= count($tickets) ?> ticket(s) à gérer</p>
    </div>

    <form method="GET" class="search-form">
        <input type="text" name="search" class="search-input"
               placeholder="Rechercher un ticket..."
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="search-button">Rechercher</button>
    </form>

    <div class="tickets-grid">
        <?php foreach ($tickets as $ticket): ?>
            <div class="ticket-card" onclick="showTicketDetails(<?= htmlspecialchars(json_encode($ticket)) ?>)">
                <div class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></div>
                <div class="ticket-user">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']) ?>
                </div>
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

    <a href="admin_dashboard.php" class="btn btn-back">Retour au tableau de bord</a>
</div>

<!-- Modal pour afficher les détails du ticket -->
<div id="ticketModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle"></h2>
        <div id="modalUser"></div>
        <div id="modalDescription"></div>
        <div id="modalImage"></div>
        <div id="modalMeta"></div>
        <div class="btn-actions">
            <a href="#" id="modalRespond" class="btn btn-respond">Répondre</a>
        </div>
    </div>
</div>

<script>
    function showTicketDetails(ticket) {
        document.getElementById('modalTitle').textContent = ticket.title;
        document.getElementById('modalUser').innerHTML = `<p><strong>Utilisateur:</strong> ${ticket.firstname} ${ticket.lastname}</p>`;
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

        document.getElementById('modalRespond').href = `respond_ticket.php?id=${ticket.id}`;
        document.getElementById('ticketModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('ticketModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('ticketModal')) {
            closeModal();
        }
    }

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
</script>
</body>
</html>