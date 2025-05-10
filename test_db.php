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

// SSL certificate paths to test
$ssl_paths = [
    __DIR__ . '/ssl/DigiCertGlobalRootCA.crt.pem',
    __DIR__ . '/DigiCertGlobalRootCA.crt.pem',
    __DIR__ . '/DigiCertGlobalRootCA.crt',
    '/home/site/wwwroot/ssl/DigiCertGlobalRootCA.crt.pem',
    '/home/site/wwwroot/DigiCertGlobalRootCA.crt.pem'
];

// Information to collect
$debug_info = [
    'server_info' => [
        'php_version' => PHP_VERSION,
        'uname' => php_uname(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_path' => __FILE__,
        'current_dir' => __DIR__
    ],
    'ssl_files' => [],
    'connection_attempts' => []
];

// Check which SSL certificate files exist
foreach ($ssl_paths as $path) {
    $debug_info['ssl_files'][$path] = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0,
        'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A'
    ];
}

// Try connecting with different methods
try {
    // Method 1: Connection without SSL (as reference)
    try {
        $conn = mysqli_init();
        mysqli_real_connect($conn, $host, $user, $pass, $db, 3306);
        $debug_info['connection_attempts']['without_ssl'] = [
            'success' => true,
            'message' => 'Connected without SSL'
        ];
        mysqli_close($conn);
    } catch (Exception $e) {
        $debug_info['connection_attempts']['without_ssl'] = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }

    // Method 2: Connection with empty SSL settings (current method)
    try {
        $conn = mysqli_init();
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        $connected = mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

        if (!$connected) {
            throw new Exception(mysqli_connect_error());
        }

        // Test a simple query
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
        $row = mysqli_fetch_assoc($result);

        $debug_info['connection_attempts']['empty_ssl'] = [
            'success' => true,
            'message' => 'Connected with empty SSL parameters',
            'user_count' => $row['count']
        ];
        mysqli_close($conn);
    } catch (Exception $e) {
        $debug_info['connection_attempts']['empty_ssl'] = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }

    // Method 3: Try each SSL certificate file
    foreach ($ssl_paths as $path) {
        if (file_exists($path)) {
            try {
                $conn = mysqli_init();
                mysqli_ssl_set($conn, NULL, NULL, $path, NULL, NULL);
                $connected = mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, MYSQLI_CLIENT_SSL);

                if (!$connected) {
                    throw new Exception(mysqli_connect_error());
                }

                // Test a simple query
                $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
                $row = mysqli_fetch_assoc($result);

                $debug_info['connection_attempts']['with_cert_' . basename($path)] = [
                    'success' => true,
                    'cert_path' => $path,
                    'message' => 'Connected with SSL certificate',
                    'user_count' => $row['count']
                ];

                // For the first successful SSL connection, set it as our main result
                if (!isset($main_result)) {
                    $main_result = [
                        'success' => true,
                        'message' => 'Database connection successful using ' . basename($path),
                        'user_count' => $row['count']
                    ];
                }

                mysqli_close($conn);
            } catch (Exception $e) {
                $debug_info['connection_attempts']['with_cert_' . basename($path)] = [
                    'success' => false,
                    'cert_path' => $path,
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    // Final result
    if (isset($main_result)) {
        // We had at least one successful SSL connection
        $response = array_merge($main_result, ['debug_info' => $debug_info]);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
        // No connection method worked
        throw new Exception("All connection attempts failed. See debug info for details.");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => $debug_info
    ], JSON_PRETTY_PRINT);
}