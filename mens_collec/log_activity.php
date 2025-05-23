<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Non autorisé');
}

function generateReferenceNumber() {
    return 'ACT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'];
    $productId = $_POST['product_id'];

    // Récupérer le prix actuel du produit
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $reference = generateReferenceNumber();

    // Insérer l'activité
    $stmt = $conn->prepare("INSERT INTO client_activities (user_id, reference_number, activity_type, description, total_amount, statut) VALUES (?, ?, ?, ?, ?, 'completed')");

    switch ($action) {
        case 'view':
            $type = 'view';
            $description = 'Consultation du produit';
            $amount = 0;
            break;
        case 'cart_add':
            $type = 'cart';
            $description = 'Ajout au panier';
            $amount = $product['price'];
            break;
    }

    $stmt->bind_param("isssd", $userId, $reference, $type, $description, $amount);
    $stmt->execute();

    $activityId = $conn->insert_id;

    // Enregistrer le produit lié
    $stmt = $conn->prepare("INSERT INTO client_activity_products (activity_id, product_id, quantity, price_at_time, subtotal) VALUES (?, ?, 1, ?, ?)");
    $stmt->bind_param("iidd", $activityId, $productId, $product['price'], $product['price']);
    $stmt->execute();

    echo json_encode(['success' => true]);
}
?>