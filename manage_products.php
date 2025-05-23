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

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion2.html");
    exit();
}

// gérer les produits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $title = $_POST['title'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];

            $stmt = $conn->prepare("INSERT INTO products (title, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $title, $description, $price, $stock, $category);
            $stmt->execute();
            break;

        case 'update':
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];

            $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
            $stmt->bind_param("ssdisi", $title, $description, $price, $stock, $category, $id);
            $stmt->execute();
            break;

        case 'delete':
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Enregistrer l'activité de suppression
            $admin_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO client_activities (user_id, activity_type, description) VALUES (?, 'delete_product', ?)");
            $description = "Suppression du produit ID: " . $id;
            $stmt->bind_param("is", $admin_id, $description);
            $stmt->execute();

            echo json_encode(['success' => true]);
            exit;
            break;
    }
}


// Récupérer tous les produits
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2rem;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 0.5rem;
        }

        .product-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-edit {
            background-color: #f39c12;
        }

        .btn-delete {
            background-color: var(--accent-color);
        }

        .btn-edit:hover {
            background-color: #d68910;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .product-form {
                margin: 1rem;
                padding: 1rem;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <!-- Copiez votre sidebar du dashboard ici -->
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
        <h1>Gestion des Produits</h1>

        <!-- Formulaire d'ajout de produit -->
        <div class="product-form">
            <h2>Ajouter un nouveau produit</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Prix</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="category" required>
                </div>
                <button type="submit" class="btn">Ajouter le produit</button>
            </form>
        </div>

        <!-- Liste des produits -->
        <div class="product-grid">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <h3><?= htmlspecialchars($product['title']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p>Prix : <?= number_format($product['price'], 2, ',', ' ') ?> €</p>
                    <p>Stock : <?= $product['stock'] ?></p>
                    <p>Catégorie : <?= htmlspecialchars($product['category']) ?></p>
                    <div class="actions">
                        <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>)">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
    function editProduct(id) {
        const productCard = document.querySelector(`[data-product-id="${id}"]`);
        const title = productCard.querySelector('.product-title').textContent;
        const description = productCard.querySelector('.product-description').textContent;
        const price = productCard.querySelector('.product-price').dataset.price;
        const stock = productCard.querySelector('.product-stock').dataset.stock;
        const category = productCard.querySelector('.product-category').dataset.category;

        document.querySelector('form input[name="title"]').value = title;
        document.querySelector('form textarea[name="description"]').value = description;
        document.querySelector('form input[name="price"]').value = price;
        document.querySelector('form input[name="stock"]').value = stock;
        document.querySelector('form input[name="category"]').value = category;
        document.querySelector('form input[name="action"]').value = 'update';
        document.querySelector('form').insertAdjacentHTML('beforeend',
            `<input type="hidden" name="id" value="${id}">`);
        document.querySelector('button[type="submit"]').textContent = 'Modifier le produit';
    }

    function deleteProduct(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
            fetch('manage_products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const productCard = document.querySelector(`[data-product-id="${id}"]`);
                        productCard.remove();
                    }
                });
        }
    }
</script>

</body>
</html>