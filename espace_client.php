<?php
session_start();

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
$stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? AND is_notified = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
        }

        body {

            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 2rem;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar-title {
            color: var(--primary-color);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(200,155,60,0.2);
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .nav-link {
            color: var(--light-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(200,155,60,0.2);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .nav-link:hover i {
            transform: scale(1.2);
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .nav-link:last-child {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1rem;
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
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

        .welcome-message .user-name {
            font-size: 2rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .welcome-message .sub-greeting {
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        .nav-link[href="logout.php"] {
            margin-top:280px;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .nav-link[href="logout.php"]:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .address-card {
            background: #fff;
            border: 1px solid rgba(200, 155, 60, 0.2);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .address-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .address-card p {
            margin: 0;
            line-height: 1.6;
            color: #333;
            font-size: 1rem;
        }

        .badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgb(200, 155, 60);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .address-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-small:hover {
            opacity: 0.9;
        }

        .btn-small {
            background-color: rgb(200, 155, 60);
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        #addresses {
            display: grid;
            gap: 1.5rem;
        }

        #addresses h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        #addresses .btn {
            margin-bottom: 1.5rem;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2 class="sidebar-title">Espace Client</h2>
        <nav>
            <a href="#profile" class="nav-link"><i class="fas fa-user"></i> Mon Profil</a>
            <a href="#orders" class="nav-link"><i class="fas fa-shopping-cart"></i> Mes Commandes</a>
            <a href="#favorites" class="nav-link"><i class="fas fa-heart"></i> Mes Favoris</a>
            <a href="#addresses" class="nav-link"><i class="fas fa-map-marker-alt"></i> Mes Adresses</a>
            <a href="#tickets" class="nav-link"><i class="fas fa-ticket-alt"></i> Mes tickets</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="welcome-message">
            <span class="greeting">Bonjour,</span>
            <span class="user-name"><?php echo htmlspecialchars($user['firstname']); ?></span>
            <p class="sub-greeting">Bienvenue dans votre espace personnel</p>
        </div>

        <div class="card" id="profile">
            <h2>Modifier mes informations</h2>
            <form action="update_profile.php" method="POST">
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe (laisser vide si inchangé)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn">Mettre à jour</button>
            </form>
        </div>

        <div class="card" id="orders">
            <h2>Mes dernières commandes</h2>

        </div>

        <div class="card" id="favorites">
            <h2>Mes produits favoris</h2>
            <!-- À implémenter : Affichage des favoris -->
        </div>

        <div class="card" id="addresses">
            <h2>Mes adresses</h2>
            <button class="btn" onclick="openAddAddressModal()">
                <i class="fas fa-plus"></i> Ajouter une adresse
            </button>

            <div class="address-grid">
                <?php
                $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $addresses = $stmt->get_result();

                if ($addresses->num_rows > 0):
                    while ($address = $addresses->fetch_assoc()):
                        ?>
                        <div class="address-card">
                            <p>
                                <strong><?php echo htmlspecialchars($address['address_line1']); ?></strong><br>
                                <?php if(!empty($address['address_line2'])): ?>
                                    <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($address['postal_code']); ?> <?php echo htmlspecialchars($address['city']); ?><br>
                                <?php echo htmlspecialchars($address['country']); ?>
                            </p>
                            <?php if($address['is_default']): ?>
                                <span class="badge">Adresse par défaut</span>
                            <?php endif; ?>
                            <div class="address-actions">
                                <button onclick="openEditAddressModal(<?php echo $address['id']; ?>)" class="btn-small">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button onclick="deleteAddress(<?php echo $address['id']; ?>)" class="btn-small btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    <?php
                    endwhile;
                else:
                    ?>
                    <p>Aucune adresse enregistrée.</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Modale pour ajouter une adresse -->
        <div id="addAddressModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeAddAddressModal()">&times;</span>
                <h2>Ajouter une nouvelle adresse</h2>
                <form action="add_address.php" method="POST">
                    <div class="form-group">
                        <label for="address_line1">Adresse Ligne 1</label>
                        <input type="text" id="address_line1" name="address_line1" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address_line2">Adresse Ligne 2 (optionnel)</label>
                        <input type="text" id="address_line2" name="address_line2" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city">Ville</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Code Postal</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Pays</label>
                        <input type="text" id="country" name="country" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="is_default">Définir comme adresse par défaut</label>
                        <input type="checkbox" id="is_default" name="is_default">
                    </div>
                    <button type="submit" class="btn">Ajouter l'adresse</button>
                </form>
            </div>
        </div>

        <div id="editAddressModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditAddressModal()">&times;</span>
                <h2>Modifier l'adresse</h2>
                <form id="editAddressForm" action="edit_address.php" method="POST">
                    <input type="hidden" id="edit_address_id" name="address_id">
                    <div class="form-group">
                        <label for="edit_address_line1">Adresse Ligne 1</label>
                        <input type="text" id="edit_address_line1" name="address_line1" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_address_line2">Adresse Ligne 2 (optionnel)</label>
                        <input type="text" id="edit_address_line2" name="address_line2" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_city">Ville</label>
                        <input type="text" id="edit_city" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_postal_code">Code Postal</label>
                        <input type="text" id="edit_postal_code" name="postal_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_country">Pays</label>
                        <input type="text" id="edit_country" name="country" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_default">Définir comme adresse par défaut</label>
                        <input type="checkbox" id="edit_is_default" name="is_default">
                    </div>
                    <button type="submit" class="btn">Mettre à jour</button>
                </form>
            </div>
        </div>

        <div class="card" id="tickets">
            <h2>Gestion des Tickets</h2>
            <div>
                <a href="submit_ticket.php" class="btn">Soumettre un ticket</a>
                <a href="view_tickets.php" class="btn">Voir mes tickets</a>
            </div>
        </div>
        <?php if ($notifications->num_rows > 0): ?>
            <div class="card" id="notifications">
                <h2>Notifications</h2>
                <form method="POST" action="mark_notifications.php">
                    <ul>
                        <?php while ($notification = $notifications->fetch_assoc()): ?>
                            <li>
                                <input type="checkbox" name="notification_ids[]" value="<?= $notification['id'] ?>">
                                Votre ticket <strong><?= htmlspecialchars($notification['title']) ?></strong> a été répondu.
                                <a href="view_ticket.php?id=<?= $notification['id'] ?>">Voir les détails</a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <button type="submit" class="btn">Marquer comme lu</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function openAddAddressModal() {
        document.getElementById('addAddressModal').style.display = 'flex';
    }

    function closeAddAddressModal() {
        document.getElementById('addAddressModal').style.display = 'none';
    }

    // Fermer la modale si on clique en dehors
    window.onclick = function(event) {
        let modal = document.getElementById('addAddressModal');
        if (event.target == modal) {
            closeAddAddressModal();
        }
    }

    function openEditAddressModal(addressId) {
        // Récupérer les données de l'adresse via une requête AJAX
        fetch(`get_address.php?id=${addressId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_address_id').value = data.id;
                document.getElementById('edit_address_line1').value = data.address_line1;
                document.getElementById('edit_address_line2').value = data.address_line2 || '';
                document.getElementById('edit_city').value = data.city;
                document.getElementById('edit_postal_code').value = data.postal_code;
                document.getElementById('edit_country').value = data.country;
                document.getElementById('edit_is_default').checked = data.is_default == 1;
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
                        location.reload(); // Recharge la page pour mettre à jour la liste
                    } else {
                        alert("Erreur lors de la suppression de l'adresse.");
                    }
                });
        }
    }
</script>


</body>
</html>
