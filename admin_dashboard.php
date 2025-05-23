<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// Azure MySQL connection details
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

$users = $conn->query("SELECT * FROM users");
$purchases = $conn->query("SELECT * FROM purchases ORDER BY id DESC");

$statsQuery = $conn->query("SELECT * FROM statistiques WHERE id = 1");
$stats = $statsQuery->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Tableau de bord</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .dashboard {
            display: flex; /* Changé de grid à flex */
            min-height: 100vh;
            position: relative;
        }

        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
            --sidebar-width: 250px;
            --transition-speed: 0.3s;
        }

        .sidebar {
            flex: 0 0 var(--sidebar-width); /* Largeur fixe */
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 2rem;
            position: fixed;
            width: var(--sidebar-width);
            height: 100vh;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-title {
            color: var(--primary-color);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
            font-size: 1.5rem;
            transition: all var(--transition-speed);
        }

        nav {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .nav-link {
            color: var(--light-color);
            text-decoration: none;
            padding: 1rem 1.2rem;
            margin: 0.4rem 0;
            border-radius: 8px;
            transition: all var(--transition-speed);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .nav-link:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: var(--primary-color);
            transform: scaleY(0);
            transition: transform var(--transition-speed);
        }

        .nav-link:hover {
            background-color: rgba(200, 155, 60, 0.1);
            transform: translateX(10px);
        }

        .nav-link:hover:before {
            transform: scaleY(1);
        }

        /* Lien de déconnexion en bas */
        .nav-link[href="logout.php"] {
            margin-top: auto;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .nav-link[href="logout.php"]:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }

        .main-content {
            flex: 1; /* Prend tout l'espace restant */
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            background-color: #f8f9fa;
            min-height: 100vh;
            box-sizing: border-box;
            width: auto; /* Supprime la largeur fixe */
            max-width: calc(100% - var(--sidebar-width)); /* Empêche le débordement */
        }

        .card, .stats, table {
            max-width: 100%;
            overflow-x: auto;
        }

        .card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--primary-color);
            color: var(--light-color);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: var(--light-color);
        }
        .btn {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.2rem;
        }

        .btn-edit {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn-delete {
            background-color: #dc3545;
            color: var(--light-color);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 300px;
            position: relative;
        }

        .welcome-message {
            background: linear-gradient(to right, var(--dark-color), var(--primary-color));
            color: var(--light-color);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-message .greeting {
            font-size: 1.2rem;
            font-weight: 300;
        }


        .welcome-message .sub-greeting {
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        <style>
         .user-controls {
             display: flex;
             gap: 1rem;
             margin-bottom: 1.5rem;
             flex-wrap: wrap;
         }

        .search-box {
            flex: 1;
            min-width: 250px;
            display: flex;
            gap: 0.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(200,155,60,0.2);
            outline: none;
        }

        .select-filter {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            min-width: 150px;
            background-color: white;
        }

        .users-table-container {
            overflow-x: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .role-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #ffd700;
            color: #000;
        }

        .role-user {
            background-color: #e1f5fe;
            color: #0288d1;
        }

        .actions-cell {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
        }

        .btn-icon {
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: none;
            color: #666;
        }

        .btn-icon:hover {
            background-color: #f5f5f5;
            color: var(--primary-color);
        }

        .btn-icon.btn-edit:hover {
            color: #2196f3;
        }

        .btn-icon.btn-delete:hover {
            color: #f44336;
        }

        .load-more-container {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #eee;
        }

        .btn-load-more {
            background: none;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-load-more:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Animation pour le chargement des nouveaux utilisateurs */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .new-row {
            animation: fadeIn 0.5s ease forwards;
        }


        .user-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .user-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            animation: slideIn 0.3s ease;
        }

        .user-modal-header {
            background: linear-gradient(45deg, var(--primary-color), #f1c40f);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
        }

        .user-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .user-modal-close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .user-modal-close:hover {
            transform: scale(1.1);
        }

        .user-modal-body {
            padding: 1.5rem;
        }

        .user-info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .user-avatar {
            display: flex;
            justify-content: center;
            align-items: start;
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, var(--primary-color), #f1c40f);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 3rem;
        }

        .user-details {
            display: grid;
            gap: 1.5rem;
        }

        .detail-group {
            background-color: #f8f9fa;
            padding: 1.2rem;
            border-radius: 8px;
        }

        .detail-group h3 {
            margin: 0 0 1rem 0;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .detail-group p {
            margin: 0.5rem 0;
            color: #666;
        }

        .detail-group i {
            width: 20px;
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        .user-activity {
            margin-top: 2rem;
        }

        .activity-timeline {
            border-left: 2px solid #eee;
            padding-left: 1.5rem;
            margin-left: 1rem;
        }

        .activity-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .activity-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
            position: absolute;
            left: -1.7rem;
            top: 5px;
        }

        .activity-date {
            font-size: 0.9rem;
            color: #888;
        }

        .user-modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .user-info-grid {
                grid-template-columns: 1fr;
            }

            .user-avatar {
                margin-bottom: 1rem;
            }
        }


    </style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2 class="sidebar-title">Administrateur Dashboard</h2>
        <nav>
            <a href="#stats" class="nav-link"><i class="fas fa-chart-line"></i> Statistiques</a>
            <a href="#users" class="nav-link"><i class="fas fa-users"></i> Utilisateurs</a>
            <a href="#orders" class="nav-link"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="#tickets" class="nav-link"><i class="fas fa-ticket-alt"></i> Tickets</a>
            <a href="#stock_manager" class="nav-link"><i class="fas fa-ticket-alt"></i> Stock Manager </a>
            <a href="manage_products.php" class="nav-link"><i class="fas fa-box"></i> Gestion Produits</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="welcome-message">
            <span class="greeting">Bonjour, </span>
            <p class="sub-greeting">Bienvenue dans votre espace administratif</p>
        </div>
        <div class="stats" id="stats">
            <div class="stat-card">
                <h3>Total Commandes</h3>
                <p><?php echo ($stats['total_commandes'] ?? 0); ?></p>
            </div>
            <div class="stat-card" id="stats">
                <h3>Chiffre d'affaires</h3>
                <p><?php echo number_format(($stats['chiffre_affaires'] ?? 0), 2, ',', ' ') . ' €'; ?></p>
            </div>

            <div class="chart-container" style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="profitChart"></canvas>
            </div>
        </div>


        <div class="card" id="users">
            <h2>Gestion des Utilisateurs</h2>

            <div class="user-controls">
                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="Rechercher un utilisateur..." class="search-input">
                    <button class="btn btn-search"><i class="fas fa-search"></i></button>
                </div>
                <div class="filter-box">
                    <select id="roleFilter" class="select-filter">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Admin</option>
                        <option value="user">Utilisateur</option>
                    </select>
                </div>
            </div>

            <div class="users-table-container">
                <table id="usersTable">
                    <thead>
                    <tr>
                        <th onclick="sortTable(0)">Nom <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Email <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Rôle <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $users_per_page = 5; // Nombre d'utilisateurs par page
                    $users_array = [];
                    while ($user = $users->fetch_assoc()) {
                        $users_array[] = $user;
                    }
                    $initial_users = array_slice($users_array, 0, $users_per_page);
                    foreach ($initial_users as $user):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                        <span class="role-badge role-<?= strtolower($user['role']) ?>">
                            <?= htmlspecialchars($user['role']) ?>
                        </span>
                            </td>
                            <td class="actions-cell">
                                <button class="btn-icon" onclick="viewUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($users_array) > $users_per_page): ?>
                    <div class="load-more-container">
                        <button id="loadMoreUsers" class="btn btn-load-more" data-page="1">
                            Voir plus <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <div class="card" id="orders">
            <h2>Gestion des Commandes</h2>
            <table>
                <tr>
                    <th>N° Commande</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                <?php while ($purchase = $purchases->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($purchase['order_number']) ?></td>
                        <td><?= htmlspecialchars($purchase['customer_name']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($purchase['creation_date'])) ?></td>
                        <td><?= number_format($purchase['total'], 2, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($purchase['status']) ?></td>
                        <td>
                            <a href="view_purchase.php?id=<?= $purchase['id'] ?>" class="btn btn-edit">Voir</a>
                            <a href="update_purchase.php?id=<?= $purchase['id'] ?>" class="btn btn-edit">Modifier</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            </div>

            <div class="card" id="tickets">
                <h2>Gestion des Tickets</h2>
                <div>
                    <a href="admin_tickets.php" class="btn">Voir tous les tickets</a>
                </div>
            </div>

    <div>
        <?php
        $result = $conn->query("SELECT id, title, stock, max_per_order FROM products");
        ?>
        <div class="card" id="stock_manager">
            <h2>Gestion de Stock</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Availability</th>
                        <th>Max Per Order</th>
                        <th>Update Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['title']) ?></td>
                            <td><?= htmlspecialchars($product['stock']) ?></td>
                            <td><?= htmlspecialchars($product['max_per_order']) ?></td>
                            <td>
                                <form method="post" action="update_stock.php" class="update-stock-form">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="number" name="new_stock" value="<?= $product['stock'] ?>" min="0" required>
                                    <input type="number" name="max_per_order" value="<?= $product['max_per_order'] ?>" min="1" required>
                                    <button type="submit" class="btn">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<div id="userModal" class="user-modal">
    <div class="user-modal-content">
        <span class="user-modal-close">&times;</span>
        <div class="user-modal-header">
            <h2>Détails de l'Utilisateur</h2>
        </div>
        <div class="user-modal-body">
            <div class="user-info-grid">
                <div class="user-avatar">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="user-details">
                    <div class="detail-group">
                        <h3>Informations Personnelles</h3>
                        <p><i class="fas fa-id-card"></i> <strong>Nom complet:</strong> <span id="userName"></span></p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <span id="userEmail"></span></p>
                        <p><i class="fas fa-user-tag"></i> <strong>Rôle:</strong> <span id="userRole"></span></p>
                    </div>
                    <div class="detail-group">
                        <h3>Statistiques du Compte</h3>
                        <p><i class="fas fa-calendar-alt"></i> <strong>Date d'inscription:</strong> <span id="userCreated"></span></p>
                        <p><i class="fas fa-shopping-cart"></i> <strong>Nombre de commandes:</strong> <span id="userOrders"></span></p>
                        <p><i class="fas fa-ticket-alt"></i> <strong>Tickets ouverts:</strong> <span id="userTickets"></span></p>
                    </div>
                </div>
            </div>
            <div class="user-activity">
                <h3>Activité Récente</h3>
                <div class="activity-timeline" id="userActivity">
                    <!-- Les activités seront ajoutées dynamiquement -->
                </div>
            </div>
        </div>
        <div class="user-modal-footer">
            <button class="btn btn-edit" onclick="editUserFromModal()">Modifier</button>
            <button class="btn btn-secondary" onclick="closeUserModal()">Fermer</button>
        </div>
    </div>
</div>

=======
    </div>
</div>
>>>>>>> Stashed changes
=======
    </div>
</div>
>>>>>>> Stashed changes
=======
    </div>
</div>
>>>>>>> Stashed changes
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des ventes
        new Chart(document.getElementById('salesChart'), {
            type: 'pie',
            data: {
                labels: ['Produit A', 'Produit B', 'Produit C', 'Produit D'],
                datasets: [{
                    data: [30, 25, 25, 20],
                    backgroundColor: [
                        'rgba(200, 155, 60, 0.8)',
                        'rgba(200, 155, 60, 0.6)',
                        'rgba(200, 155, 60, 0.4)',
                        'rgba(200, 155, 60, 0.2)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des Ventes',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: { position: 'bottom' }
                }
            }
        });

        // Graphique des bénéfices
        new Chart(document.getElementById('profitChart'), {
            type: 'pie',
            data: {
                labels: ['Bénéfice net', 'Coûts fixes', 'Coûts variables', 'Taxes'],
                datasets: [{
                    data: [40, 20, 25, 15],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(46, 204, 113, 0.6)',
                        'rgba(46, 204, 113, 0.4)',
                        'rgba(46, 204, 113, 0.2)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des Bénéfices',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: { position: 'bottom' }
                }
            }
        });
    });

    // Fonction de tri du tableau
    function sortTable(columnIndex) {
        const table = document.getElementById('usersTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = table.querySelector('th').classList.contains('asc');

        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
        });

        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        table.querySelector('th').classList.toggle('asc');
    }

    // Fonction de recherche
    document.getElementById('userSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filtre par rôle
    document.getElementById('roleFilter').addEventListener('change', function(e) {
        const role = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const rowRole = row.querySelector('.role-badge').textContent.toLowerCase();
            row.style.display = !role || rowRole === role ? '' : 'none';
        });
    });

    // Chargement des utilisateurs supplémentaires
    document.getElementById('loadMoreUsers')?.addEventListener('click', function() {
        const page = parseInt(this.dataset.page);
        const start = page * <?= $users_per_page ?>;
        const users = <?= json_encode($users_array) ?>;

        if (start < users.length) {
            const tbody = document.querySelector('#usersTable tbody');
            const nextUsers = users.slice(start, start + <?= $users_per_page ?>);

            nextUsers.forEach(user => {
                const tr = document.createElement('tr');
                tr.className = 'new-row';
                tr.innerHTML = `
                    <td>${user.firstname} ${user.lastname}</td>
                    <td>${user.email}</td>
                    <td><span class="role-badge role-${user.role.toLowerCase()}">${user.role}</span></td>
                    <td class="actions-cell">
                        <button class="btn-icon" onclick="viewUser(${user.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon btn-edit" onclick="editUser(${user.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteUser(${user.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            this.dataset.page = page + 1;

            if (start + <?= $users_per_page ?> >= users.length) {
                this.style.display = 'none';
            }
        }
    });

    // Fonctions pour les actions sur les utilisateurs
    function viewUser(id) {
        // Implémentez la logique pour voir les détails de l'utilisateur
        console.log('Voir utilisateur:', id);
    }

    function editUser(id) {
        window.location.href = `edit_user.php?id=${id}`;
    }

    function deleteUser(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            window.location.href = `delete_user.php?id=${id}`;
        }
    }


    // focntion pour viewUser
    let currentUserId = null;

    async function viewUser(id) {
        currentUserId = id;
        const modal = document.getElementById('userModal');

        try {
            // Simuler une requête API pour obtenir les données de l'utilisateur
            // En production, remplacez ceci par une vraie requête AJAX
            const response = await fetch(`get_user_details.php?id=${id}`);
            const userData = await response.json();

            // Mise à jour des informations de l'utilisateur
            document.getElementById('userName').textContent = `${userData.firstname} ${userData.lastname}`;
            document.getElementById('userEmail').textContent = userData.email;
            document.getElementById('userRole').textContent = userData.role;
            document.getElementById('userCreated').textContent = new Date(userData.created_at).toLocaleDateString();
            document.getElementById('userOrders').textContent = userData.orders_count || 0;
            document.getElementById('userTickets').textContent = userData.tickets_count || 0;

            // Affichage des activités récentes
            const activityContainer = document.getElementById('userActivity');
            activityContainer.innerHTML = ''; // Nettoyer les activités précédentes

            if (userData.activities && userData.activities.length > 0) {
                userData.activities.forEach(activity => {
                    const activityElement = document.createElement('div');
                    activityElement.className = 'activity-item';
                    activityElement.innerHTML = `
                    <div class="activity-date">${new Date(activity.date).toLocaleDateString()}</div>
                    <div class="activity-description">${activity.description}</div>
                `;
                    activityContainer.appendChild(activityElement);
                });
            } else {
                activityContainer.innerHTML = '<p>Aucune activité récente</p>';
            }

            modal.style.display = 'block';
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            alert('Erreur lors du chargement des données de l\'utilisateur');
        }
    }

    function closeUserModal() {
        const modal = document.getElementById('userModal');
        modal.style.display = 'none';
        currentUserId = null;
    }

    function editUserFromModal() {
        if (currentUserId) {
            window.location.href = `edit_user.php?id=${currentUserId}`;
        }
    }

    // Fermer la modal en cliquant en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target == modal) {
            closeUserModal();
        }
    }

    // Fermer la modal avec la croix
    document.querySelector('.user-modal-close').onclick = closeUserModal;


</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
      const forms = document.querySelectorAll(".update-stock-form");

      forms.forEach((form) => {
        form.addEventListener("submit", function (e) {
          e.preventDefault(); // Prevent form submission

          const formData = new FormData(form);
          const productId = formData.get("product_id");
          const newStock = formData.get("new_stock");

          fetch("update_stock.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => {
              if (!response.ok) {
                throw new Error("Failed to update stock");
              }
              return response.text();
            })
            .then(() => {
              // Update the stock value in the table
              const stockCell = form.closest("tr").querySelector("td:nth-child(2)");
              stockCell.textContent = newStock;
              alert("Stock updated successfully!");
            })
            .catch((error) => {
              console.error(error);
              alert("Error updating stock.");
            });
        });
      });
    });
</script>

</body>
</html>