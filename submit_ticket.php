<?php
session_start();

// Database connection parameters
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db   = "users_db";

// Determine correct SSL certificate
$ssl_cert_1 = __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem';
$ssl_cert_2 = __DIR__ . '/DigiCertGlobalRootCA.crt.pem';
$ssl_cert   = file_exists($ssl_cert_1) ? $ssl_cert_1 : $ssl_cert_2;

// Create connection with SSL
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
if (!mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
    die("Database connection error: " . mysqli_connect_error());
}

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion2.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title       = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $image_path  = null;

    // Handle file upload if image is provided
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
            die("Error: Upload directory creation failed.");
        }
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $temp_path  = $_FILES['image']['tmp_name'];

        // Validate file type is an image using getimagesize
        $image_info = getimagesize($temp_path);
        if ($image_info === false) {
            die("Error: Uploaded file is not a valid image.");
        }

        $image_path = $target_dir . $image_name;
        if (!move_uploaded_file($temp_path, $image_path)) {
            die("Error: Image upload failed.");
        }
    }

    // Insert ticket into the database
    $status = 'En attente';
    $stmt   = $conn->prepare("INSERT INTO tickets (title, description, image_path, status, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $title, $description, $image_path, $status, $user_id);
    if (!$stmt->execute()) {
        die("Error: Could not submit ticket. " . $stmt->error);
    }

    header("Location: view_tickets.php?status=submitted");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soumettre un Ticket</title>
    <style>
        :root {
            --primary-color: rgb(200,155,60);
            --dark-color: #222;
            --light-color: #fff;
            --background-color: #f5f5f5;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--dark-color);
        }
        .form-group input,
        .form-group textarea {
            width: 95%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input[type="file"] {
            padding: 5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--light-color);
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #d4a349;
        }
        .btn-back {
            background-color: #6c757d;
            margin-top: 20px;
            display: inline-block;
            text-align: center;
        }
        .btn-back:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Soumettre un Ticket</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Titre</label>
            <input type="text" id="title" name="title" placeholder="Entrez le titre du ticket" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" placeholder="Décrivez votre problème ou demande" required></textarea>
        </div>
        <div class="form-group">
            <label for="image">Ajouter une image (optionnel)</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn">Soumettre</button>
    </form>
    <a href="view_tickets.php" class="btn btn-back">Retour à mes tickets</a>
</div>
</body>
</html>