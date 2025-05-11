<?php
// Azure Database connection with SSL
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
$mysqli = mysqli_init();
mysqli_ssl_set($mysqli, NULL, NULL, $ssl_cert, NULL, NULL);

if (!mysqli_real_connect($mysqli, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}

// Base site URL
$baseUrl = "https://www.hypeza.com/";

// Static URLs (you can add more as needed)
$staticUrls = [
    "index.php",
    "products.php",
    "category.php?id=shoes",  // or loop all categories if you want
    "about.php",
    "contact.php",
    "faq.php",
    "connexion2.html",
    "register.php",
    "forgot-password.php"
];

// Start XML
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

// Add static URLs
foreach ($staticUrls as $url) {
    $node = $xml->addChild('url');
    $node->addChild('loc', $baseUrl . $url);
}

// Add product URLs from database
$result = $mysqli->query("SELECT id FROM products");

while ($row = $result->fetch_assoc()) {
    $productUrl = "product.php?id=" . $row['id'];
    $node = $xml->addChild('url');
    $node->addChild('loc', $baseUrl . $productUrl);
}

// Save the file
file_put_contents('sitemap.xml', $xml->asXML());

echo "✅ Sitemap generated successfully!";
?>