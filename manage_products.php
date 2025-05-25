<?php
session_start();
// Connexion à la base de données Azure MySQL
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

// Path to SSL certificate
$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';
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

// Créer le dossier uploads s'il n'existe pas
$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fonction pour gérer l'upload d'image
function handleImageUpload() {
    global $upload_dir;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $target_file)) {
                return $target_file;
            }
        }
    }
    return null;
}

// Gestion des produits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $title = $_POST['title'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];
            $image_url = handleImageUpload();

            $stmt = $conn->prepare("INSERT INTO products (title, description, price, stock, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiss", $title, $description, $price, $stock, $category, $image_url);
            $stmt->execute();
            break;

        case 'update':
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];
            $image_url = handleImageUpload();

            if ($image_url) {
                $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, stock = ?, category = ?, image_url = ? WHERE id = ?");
                $stmt->bind_param("ssdissi", $title, $description, $price, $stock, $category, $image_url, $id);
            } else {
                $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
                $stmt->bind_param("ssdisi", $title, $description, $price, $stock, $category, $id);
            }
            $stmt->execute();
            break;

        case 'delete':
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3949ab;
            --accent-color: #f72585;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-color: #2d3436;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .product-form {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .product-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .product-meta {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 1rem;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h1>Gestion des Produits</h1>

    <div class="product-form">
        <h2>Ajouter un nouveau produit</h2>
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Titre du produit</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="electronique">Électronique</option>
                        <option value="vetements">Vêtements</option>
                        <option value="alimentation">Alimentation</option>
                        <option value="maison">Maison</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prix (€)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Stock disponible</label>
                    <input type="number" name="stock" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Photo du produit</label>
                    <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" required>
                    <img id="image-preview" style="display:none; max-width:200px; margin-top:10px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter le produit</button>
        </form>
    </div>

    <div class="product-grid">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="product-card" data-product-id="<?= $product['id'] ?>">
                <img src="<?= htmlspecialchars($product['image_url'] ?: 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['title']) ?></h3>
                    <p class="product-meta">Prix : <?= number_format($product['price'], 2, ',', ' ') ?> €</p>
                    <p class="product-meta">Stock : <?= $product['stock'] ?></p>
                </div>
                <div class="actions">
                    <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>)">Modifier</button>
                    <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Supprimer</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    // Prévisualisation de l'image
    document.getElementById('product_image').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        const file = e.target.files[0];

        if (file) {
            preview.style.display = 'block';
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
            }

            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Fonction d'édition
    function editProduct(id) {
        const productCard = document.querySelector(`[data-product-id="${id}"]`);
        const form = document.getElementById('productForm');
        const title = productCard.querySelector('h3').textContent;
        const description = productCard.querySelector('.product-meta').textContent;
        const price = productCard.querySelector('.product-meta').textContent.split(': ')[1];
        const stock = productCard.querySelector('.product-meta').textContent.split(': ')[1];

        form.querySelector('[name="title"]').value = title;
        form.querySelector('[name="description"]').value = description;
        form.querySelector('[name="price"]').value = price;
        form.querySelector('[name="stock"]').value = stock;
        form.querySelector('[name="action"]').value = 'update';

        if (!form.querySelector('[name="id"]')) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
        } else {
            form.querySelector('[name="id"]').value = id;
        }

        form.scrollIntoView({ behavior: 'smooth' });
    }

    // Fonction de suppression
    function deleteProduct(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const productCard = document.querySelector(`[data-product-id="${id}"]`);
                        productCard.remove();
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la suppression');
                });
        }
    }
</script>
</body>
</html>