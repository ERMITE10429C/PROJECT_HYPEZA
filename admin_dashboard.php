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

// Create connection with SSL options for Azure
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

// Check connection
if (mysqli_connect_errno()) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

$users = $conn->query("SELECT * FROM users");
$purchases = $conn->query("SELECT * FROM purchases ORDER BY id DESC");
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
                <p><?php echo $purchases->num_rows; ?></p>
            </div>
            <div class="stat-card" id="stats">
                <h3>Chiffre d'affaires</h3>
                <p><?php
                    $total = $conn->query("SELECT SUM(total) as total FROM purchases")->fetch_assoc();
                    echo number_format($total['total'], 2, ',', ' ') . ' €';
                    ?></p>
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
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit">Modifier</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
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
    </div>
</div>
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
</script>
</body>
</html>