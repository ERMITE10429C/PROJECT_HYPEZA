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
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Copiez le style de votre dashboard existant */
        /* Ajoutez ces styles spécifiques */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <!-- Copiez votre sidebar du dashboard ici -->

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