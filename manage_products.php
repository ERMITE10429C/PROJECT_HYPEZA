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

// gérer les produits
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

            // Supprimer l'ancienne image si elle existe
            $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($row['image_url'] && file_exists($row['image_url'])) {
                    unlink($row['image_url']);
                }
            }

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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .product-form {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 3rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 1rem;
            background: #edf2f7;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #e2e8f0;
            border-color: var(--primary-color);
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
        }

        .product-image-container {
            position: relative;
            width: 100%;
            height: 200px;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .product-info h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .product-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 1rem 0;
        }

        .meta-item {
            font-size: 0.9rem;
            color: #64748b;
        }

        .meta-item span {
            font-weight: 600;
            color: var(--text-color);
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-edit,
        .btn-delete {
            flex: 1;
            padding: 0.75rem;
            font-size: 0.9rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<div class="dashboard">
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
                        <div class="file-upload">
                            <input type="file" name="product_image" id="product_image" class="file-upload-input" accept="image/*" required>
                            <label for="product_image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Cliquez ou glissez une image ici</span>
                            </label>
                            <img id="image-preview" class="preview-image" src="#" alt="Aperçu de l'image">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter le produit
                </button>
            </form>
        </div>

        <div class="product-grid">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card" data-product-id="<?= $product['id'] ?>">
                    <div class="product-image-container">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
                        <?php else: ?>
                            <img src="placeholder.jpg" alt="Image par défaut" class="product-image">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                        <div class="product-meta">
                            <div class="meta-item">
                                Prix: <span class="product-price" data-price="<?= $product['price'] ?>"><?= number_format($product['price'], 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="meta-item">
                                Stock: <span class="product-stock" data-stock="<?= $product['stock'] ?>"><?= $product['stock'] ?></span>
                            </div>
                            <div class="meta-item">
                                Catégorie: <span class="product-category" data-category="<?= $product['category'] ?>"><?= htmlspecialchars($product['category']) ?></span>
                            </div>
                        </div>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="actions">
                            <button class="btn-edit" onclick="editProduct(<?= $product['id'] ?>)">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
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
        const title = productCard.querySelector('.product-title').textContent;
        const description = productCard.querySelector('.product-description').textContent;
        const price = productCard.querySelector('.product-price').dataset.price;
        const stock = productCard.querySelector('.product-stock').dataset.stock;
        const category = productCard.querySelector('.product-category').dataset.category;

        form.querySelector('[name="title"]').value = title;
        form.querySelector('[name="description"]').value = description;
        form.querySelector('[name="price"]').value = price;
        form.querySelector('[name="stock"]').value = stock;
        form.querySelector('[name="category"]').value = category;
        form.querySelector('[name="action"]').value = 'update';

        // Ajout de l'ID pour l'édition
        if (!form.querySelector('[name="id"]')) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
        } else {
            form.querySelector('[name="id"]').value = id;
        }

        // Modification du texte du bouton
        form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Modifier le produit';

        // Scroll vers le formulaire
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