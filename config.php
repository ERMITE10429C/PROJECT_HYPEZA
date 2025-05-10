<?php
// Database connection parameters
define('DB_HOST', getenv('DB_HOST') ?: 'hypezaserversql.mysql.database.azure.com');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'users_db');
define('DB_USER', getenv('DB_USER') ?: 'user');
define('DB_PASS', getenv('DB_PASS') ?: 'HPL1710COMPAq'); // Password should be set in environment variables
define('DB_SSL_MODE', 'require'); // Fixed the variable name and assigned proper value

// Establish database connection
try {
    $dsn = sprintf(
        "mysql:host=%s;port=%d;dbname=%s;sslmode=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_SSL_MODE
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Connection successful
    // echo "Database connection established successfully";
} catch(PDOException $e) {
    // Log error instead of displaying it (in production)
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please check the logs for details.");
}
?>