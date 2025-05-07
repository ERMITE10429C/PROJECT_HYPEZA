<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "Aminezh-263@", "users_db");
if ($conn->connect_error) {
    echo json_encode(["error" => "Erreur de connexion"]);
    exit();
}

// Compter les utilisateurs
$res_users = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$users = $res_users->fetch_assoc()['total_users'];

// Compter les produits achetés (somme des quantités dans cart_items)
$res_orders = $conn->query("SELECT SUM(quantity) AS total_products FROM cart_items");
$products = $res_orders->fetch_assoc()['total_products'] ?? 0;

echo json_encode([
    "total_users" => $users,
    "total_products" => $products
]);
