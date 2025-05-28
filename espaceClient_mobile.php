<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

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

// Récupérer les infos actuelles de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Récupérer les tickets répondus non notifiés
$stmt = $conn->prepare("SELECT t.*, COUNT(tr.id) as response_count
                       FROM tickets t
                       LEFT JOIN ticket_responses tr ON t.id = tr.ticket_id
                       WHERE t.user_id = ? AND t.has_new_response = 1
                       GROUP BY t.id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Espace Client Mobile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
            --border-radius: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            padding-bottom: 70px; /* Space for bottom nav */
            margin: 0;
        }

        /* Header */
        .mobile-header {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .mobile-header h1 {
            margin: 0;
            font-size: 1.3rem;
        }

        /* Welcome message */
        .welcome-message {
            background: linear-gradient(to right, var(--dark-color), var(--primary-color));
            color: var(--light-color);
            padding: 20px 15px;
            margin: 15px;
            border-radius: var(--border-radius);
        }

        .welcome-message .greeting {
            font-size: 1.1rem;
        }

        .welcome-message .user-name {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
            margin: 5px 0;
        }

        .welcome-message .sub-greeting {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 5px 0 0 0;
        }

        /* Content Sections */
        .section {
            display: none;
            padding: 15px;
        }

        .section.active {
            display: block;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3rem;
            color: var(--dark-color);
        }

        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 15px;
            font-size: 1rem;
            width: 100%;
            margin-top: 5px;
            cursor: pointer;
            touch-action: manipulation;
        }

        /* Address cards */
        .address-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .address-card p {
            margin: 0 0 10px 0;
            line-height: 1.4;
        }

        .badge {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-bottom: 10px;
        }

        .address-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        /* Bottom Navigation */
        .bottom-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: var(--dark-color);
            box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .nav-item {
            flex: 1;
            color: var(--light-color);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 5px;
            font-size: 0.7rem;
        }

        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        /* More menu panel */
        .more-menu-panel {
            position: fixed;
            bottom: -100%;
            left: 0;
            width: 100%;
            background-color: var(--light-color);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.2);
            z-index: 1001;
            transition: bottom 0.3s ease;
        }

        .more-menu-panel.active {
            bottom: 0;
        }

        .more-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .more-menu-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .close-more-menu {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
        }

        .more-menu-items {
            padding: 10px 0;
        }

        .more-menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            text-decoration: none;
            color: var(--dark-color);
        }

        .more-menu-item i {
            width: 30px;
            font-size: 1.2rem;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .more-menu-item.logout {
            color: #dc3545;
        }

        .more-menu-item.logout i {
            color: #dc3545;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 20px 15px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Notifications */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        /* Logout button */
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            width: 100%;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <h1>Espace Client</h1>
    </div>

    <div class="welcome-message">
        <span class="greeting">Bonjour,</span>
        <span class="user-name"><?php echo htmlspecialchars($user['firstname']); ?></span>
        <p class="sub-greeting">Bienvenue dans votre espace personnel</p>
    </div>

    <!-- Notifications Badge -->
    <?php if ($notifications->num_rows > 0): ?>
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Notifications</h2>
            <span style="background:red; color:white; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center;"><?php echo $notifications->num_rows; ?></span>
        </div>
        <a href="#" onclick="showSection('notifications')" class="btn">Voir les notifications</a>
    </div>
    <?php endif; ?>

    <!-- Profile Section -->
    <section id="profile-section" class="section active">
        <div class="card">
            <h2>Mon Profil</h2>
            <form action="update_profile.php" method="POST">
                <!-- Form fields go here -->
            </form>
        </div>
    </section>

    <!-- Orders Section -->
    <section id="orders-section" class="section">
        <div class="card">
            <h2>Mes Commandes</h2>
            <p>Vous n'avez pas encore de commandes.</p>
        </div>
    </section>

    <!-- Favorites Section -->
    <section id="favorites-section" class="section">
        <div class="card">
            <h2>Mes Favoris</h2>
            <p>Vous n'avez pas encore ajouté de favoris.</p>
        </div>
    </section>

    <!-- Addresses Section -->
    <section id="addresses-section" class="section">
        <div class="card">
            <h2>Mes Adresses</h2>
            <button class="btn" onclick="openAddAddressModal()">
                <i class="fas fa-plus"></i> Ajouter une adresse
            </button>

            <!-- Address list goes here -->
        </div>
    </section>

    <!-- Tickets Section -->
    <section id="tickets-section" class="section">
        <div class="card">
            <h2>Mes Tickets</h2>
            <a href="submit_ticket.php" class="btn" style="margin-bottom: 10px;">Soumettre un ticket</a>
            <a href="view_tickets.php" class="btn">Voir mes tickets</a>
        </div>
    </section>

    <!-- Notifications Section -->
    <section id="notifications-section" class="section">
        <div class="card">
            <h2>Notifications</h2>
            <!-- Notifications list goes here -->
        </div>

        <a href="home.php" class="btn" style="background-color: #3c9bc8;">Retour à l'accueil</a>
        <a href="logout.php" class="logout-btn">Se déconnecter</a>
    </section>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="#" class="nav-item" onclick="showSection('profile')">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
        <a href="#" class="nav-item" onclick="showSection('orders')">
            <i class="fas fa-shopping-cart"></i>
            <span>Commandes</span>
        </a>
        <a href="#" class="nav-item" onclick="showSection('favorites')">
            <i class="fas fa-heart"></i>
            <span>Favoris</span>
        </a>
        <a href="#" class="nav-item" onclick="showSection('addresses')">
            <i class="fas fa-map-marker-alt"></i>
            <span>Adresses</span>
        </a>
        <a href="#" class="nav-item more-menu">
            <i class="fas fa-ellipsis-h"></i>
            <span>Plus</span>
        </a>
    </div>

    <!-- More menu (slides up from bottom) -->
    <div class="more-menu-panel">
        <div class="more-menu-header">
            <h3>Plus d'options</h3>
            <button class="close-more-menu">&times;</button>
        </div>
        <div class="more-menu-items">
            <a href="#" class="more-menu-item" onclick="showSection('tickets')">
                <i class="fas fa-ticket-alt"></i>
                <span>Mes tickets</span>
            </a>
            <a href="home_mobile.php" class="more-menu-item">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="logout.php" class="more-menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div id="addAddressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddAddressModal()">&times;</span>
            <h2>Ajouter une adresse</h2>
            <form action="add_address.php" method="POST">
                <!-- Form fields go here -->
            </form>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div id="editAddressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditAddressModal()">&times;</span>
            <h2>Modifier l'adresse</h2>
            <form id="editAddressForm" action="edit_address.php" method="POST">
                <!-- Form fields go here -->
            </form>
        </div>
    </div>

    <script>
        // Show specific section
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionName + '-section').classList.add('active');

            // Update active navigation
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('active');
            });

            // Find and activate the clicked nav item
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }

            // Close more menu if open
            document.querySelector('.more-menu-panel').classList.remove('active');

            // Scroll to top
            window.scrollTo(0, 0);

            // Prevent default link behavior
            if (event) event.preventDefault();
        }

        // Address modal functions
        function openAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'flex';
        }

        function closeAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'none';
        }

        function openEditAddressModal(addressId) {
            fetch(`get_address.php?id=${addressId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate form with address data
                    document.getElementById('editAddressModal').style.display = 'flex';
                });
        }

        function closeEditAddressModal() {
            document.getElementById('editAddressModal').style.display = 'none';
        }

        function deleteAddress(addressId) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cette adresse ?")) {
                fetch(`delete_address.php?id=${addressId}`, { method: 'GET' })
                    .then(response => {
                        if (response.ok) {
                            alert("Adresse supprimée avec succès.");
                            location.reload();
                        } else {
                            alert("Erreur lors de la suppression de l'adresse.");
                        }
                    });
            }
        }

        // DOM ready event handler
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide more menu
            const moreMenuButton = document.querySelector('.more-menu');
            const moreMenuPanel = document.querySelector('.more-menu-panel');
            const closeMoreMenu = document.querySelector('.close-more-menu');

            moreMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                moreMenuPanel.classList.add('active');
            });

            closeMoreMenu.addEventListener('click', function() {
                moreMenuPanel.classList.remove('active');
            });

            // Close more menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!moreMenuPanel.contains(event.target) &&
                    !moreMenuButton.contains(event.target) &&
                    moreMenuPanel.classList.contains('active')) {
                    moreMenuPanel.classList.remove('active');
                }
            });

            // Handle modals
            window.onclick = function(event) {
                const addModal = document.getElementById('addAddressModal');
                const editModal = document.getElementById('editAddressModal');

                if (event.target == addModal) {
                    closeAddAddressModal();
                }

                if (event.target == editModal) {
                    closeEditAddressModal();
                }
            };

            // Set initial active section
            const profileSection = document.getElementById('profile-section');
            if (profileSection) {
                profileSection.classList.add('active');
                document.querySelector('.nav-item:first-child').classList.add('active');
            }

            // Add touch event handling to prevent delay on mobile
            const buttons = document.querySelectorAll('button, .btn, .nav-item');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    // Add active state
                });
                button.addEventListener('touchend', function() {
                    // Remove active state
                });
            });
        });
    </script>
</body>
</html>