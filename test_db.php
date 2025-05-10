<?php
// Create this file as test_db.php in your project
header('Content-Type: application/json');

// Show all PHP errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = "hypezaserversql.mysql.database.azure.com";
$user = "user";
$pass = "HPL1710COMPAq";
$db = "users_db";

try {
    // Create connection with SSL options for Azure
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    $connected = mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

    // Check connection
    if (!$connected) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Test a simple query
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $row = mysqli_fetch_assoc($result);

    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'user_count' => $row['count']
    ]);

    mysqli_close($conn);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


