<?php
// insert_outerwear_products.php
require_once 'db_connection.php';

$outerwear_products = [
    [
        'title' => 'Cashmere Wool Coat',
        'description' => 'Luxurious cashmere wool coat with a timeless silhouette. Features a double-breasted design and premium Italian fabric.',
        'price' => 1295.00,
        'stock' => 10,
        'category' => 'coats',
        'image_url' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a',
        'collection' => 'outerwear'
    ],
    [
        'title' => 'Classic Leather Jacket',
        'description' => 'A premium leather jacket crafted from buttery-soft lambskin leather. Features silver hardware and a tailored fit.',
        'price' => 895.00,
        'stock' => 15,
        'category' => 'jackets',
        'image_url' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5',
        'collection' => 'outerwear'
    ],
    [
        'title' => 'Tailored Velvet Blazer',
        'description' => 'A luxurious velvet blazer with a tailored silhouette. Perfect for elevating both casual and formal outfits.',
        'price' => 645.00,
        'stock' => 20,
        'category' => 'blazers',
        'image_url' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254',
        'collection' => 'outerwear'
    ],
    [
        'title' => 'Classic Trench Coat',
        'description' => 'A timeless trench coat made from water-resistant gabardine. Features classic details including epaulettes and a belted waist.',
        'price' => 795.00,
        'stock' => 12,
        'category' => 'trench',
        'image_url' => 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3',
        'collection' => 'outerwear'
    ]
];

try {
    $stmt = $conn->prepare("INSERT INTO products (title, description, price, stock, category, image_url, collection) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($outerwear_products as $product) {
        $stmt->bind_param("ssdiiss",
            $product['title'],
            $product['description'],
            $product['price'],
            $product['stock'],
            $product['category'],
            $product['image_url'],
            $product['collection']
        );
        $stmt->execute();
    }

    echo "Produits Outerwear ajoutés avec succès!";
} catch (Exception $e) {
    echo "Erreur lors de l'ajout des produits: " . $e->getMessage();
}

$conn->close();
?>