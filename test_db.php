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
        'php_extensions' => get_loaded_extensions(),
        'uname' => php_uname(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_path' => __FILE__,
        'current_dir' => __DIR__,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ],
    'ssl_files' => [],
    'connection_attempts' => [],
    'register_test' => [],
    'table_structures' => []
];

// Check which SSL certificate files exist
foreach ($ssl_paths as $path) {
    $debug_info['ssl_files'][$path] = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0,
        'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
        'md5' => file_exists($path) ? md5_file($path) : 'N/A'
    ];
}

// Function to test a connection and optional query
function test_connection($host, $user, $pass, $db, $ssl_option = null, $cert_path = null) {
    $result = [
        'connection' => [
            'success' => false,
            'message' => ''
        ],
        'query_test' => null,
        'table_info' => null
    ];

    try {
        $conn = mysqli_init();

        if (!$conn) {
            throw new Exception("mysqli_init failed");
        }

        // Apply SSL settings if provided
        if ($ssl_option === 'empty') {
            mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        } elseif ($ssl_option === 'cert' && $cert_path) {
            mysqli_ssl_set($conn, NULL, NULL, $cert_path, NULL, NULL);
        }

        // Connect with or without SSL flags
        $flags = ($ssl_option) ? MYSQLI_CLIENT_SSL : 0;
        $connected = mysqli_real_connect($conn, $host, $user, $pass, $db, 3306, $flags);

        if (!$connected) {
            throw new Exception(mysqli_connect_error());
        }

        $result['connection']['success'] = true;
        $result['connection']['message'] = 'Connected successfully';
        $result['connection']['client_info'] = mysqli_get_client_info();
        $result['connection']['host_info'] = mysqli_get_host_info($conn);
        $result['connection']['protocol_version'] = mysqli_get_proto_info($conn);
        $result['connection']['server_info'] = mysqli_get_server_info($conn);
        $result['connection']['server_version'] = mysqli_get_server_version($conn);

        // Test query execution
        try {
            // Check tables in the database
            $tables_result = mysqli_query($conn, "SHOW TABLES");

            if (!$tables_result) {
                throw new Exception("Failed to retrieve tables: " . mysqli_error($conn));
            }

            $tables = [];
            while ($table = mysqli_fetch_array($tables_result)) {
                $tables[] = $table[0];
            }

            $result['table_info']['tables'] = $tables;

            // Check users table structure if it exists
            if (in_array('users', $tables)) {
                $structure_result = mysqli_query($conn, "DESCRIBE users");

                if (!$structure_result) {
                    throw new Exception("Failed to retrieve users table structure: " . mysqli_error($conn));
                }

                $columns = [];
                while ($column = mysqli_fetch_assoc($structure_result)) {
                    $columns[] = $column;
                }

                $result['table_info']['users_structure'] = $columns;

                // Check user count
                $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");

                if (!$count_result) {
                    throw new Exception("Failed to count users: " . mysqli_error($conn));
                }

                $row = mysqli_fetch_assoc($count_result);
                $result['query_test'] = [
                    'success' => true,
                    'query' => "SELECT COUNT(*) as count FROM users",
                    'result' => $row,
                    'message' => 'Query executed successfully'
                ];

                // Test a sample user data
                if ($row['count'] > 0) {
                    $sample_result = mysqli_query($conn, "SELECT id, firstname, lastname, email FROM users LIMIT 1");

                    if (!$sample_result) {
                        throw new Exception("Failed to retrieve sample user: " . mysqli_error($conn));
                    }

                    $sample_user = mysqli_fetch_assoc($sample_result);
                    $result['query_test']['sample_user'] = $sample_user;
                }
            } else {
                $result['query_test'] = [
                    'success' => false,
                    'message' => 'Users table does not exist'
                ];
            }

        } catch (Exception $query_exception) {
            $result['query_test'] = [
                'success' => false,
                'message' => $query_exception->getMessage()
            ];
        }

        // Simulate registration process
        try {
            // Check if we can insert and delete a test user
            $test_email = 'test_' . time() . '@example.com';
            $test_password = password_hash('test_password', PASSWORD_DEFAULT);

            // Start transaction to ensure we can roll back
            mysqli_begin_transaction($conn);

            $insert_sql = "INSERT INTO users (firstname, lastname, email, phone, password) 
                          VALUES ('Test', 'User', ?, '1234567890', ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            if (!$insert_stmt) {
                throw new Exception("Failed to prepare insert statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($insert_stmt, "ss", $test_email, $test_password);

            $insert_success = mysqli_stmt_execute($insert_stmt);

            if (!$insert_success) {
                throw new Exception("Failed to insert test user: " . mysqli_stmt_error($insert_stmt));
            }

            $test_user_id = mysqli_insert_id($conn);

            // Now delete the test user
            $delete_sql = "DELETE FROM users WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);

            if (!$delete_stmt) {
                throw new Exception("Failed to prepare delete statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($delete_stmt, "i", $test_user_id);

            $delete_success = mysqli_stmt_execute($delete_stmt);

            if (!$delete_success) {
                throw new Exception("Failed to delete test user: " . mysqli_stmt_error($delete_stmt));
            }

            // Commit transaction
            mysqli_commit($conn);

            $result['register_test'] = [
                'success' => true,
                'message' => 'Registration simulation successful'
            ];

        } catch (Exception $register_exception) {
            // Roll back transaction
            mysqli_rollback($conn);

            $result['register_test'] = [
                'success' => false,
                'message' => $register_exception->getMessage()
            ];
        }

        mysqli_close($conn);

    } catch (Exception $e) {
        $result['connection']['success'] = false;
        $result['connection']['message'] = $e->getMessage();
    }

    return $result;
}

// Try connecting with different methods
try {
    // Method 1: Connection without SSL (as reference)
    $debug_info['connection_attempts']['without_ssl'] = test_connection($host, $user, $pass, $db);

    // Method 2: Connection with empty SSL settings
    $debug_info['connection_attempts']['empty_ssl'] = test_connection($host, $user, $pass, $db, 'empty');

    // Method 3: Try each SSL certificate file
    $working_ssl_connection = null;

    foreach ($ssl_paths as $path) {
        if (file_exists($path)) {
            $test_result = test_connection($host, $user, $pass, $db, 'cert', $path);
            $debug_info['connection_attempts']['with_cert_' . basename($path)] = $test_result;

            if ($test_result['connection']['success'] && !$working_ssl_connection) {
                $working_ssl_connection = [
                    'path' => $path,
                    'result' => $test_result
                ];
            }
        }
    }

    // Final result
    if ($working_ssl_connection) {
        // We had at least one successful SSL connection
        $response = [
            'success' => true,
            'message' => 'Database connection successful using ' . basename($working_ssl_connection['path']),
            'best_connection' => $working_ssl_connection,
            'debug_info' => $debug_info
        ];
        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
        // Try to identify any successful connection
        $any_success = false;
        foreach ($debug_info['connection_attempts'] as $attempt) {
            if ($attempt['connection']['success']) {
                $any_success = true;
                break;
            }
        }

        if ($any_success) {
            echo json_encode([
                'success' => true,
                'message' => 'At least one connection method works, but no SSL connection with certificate',
                'debug_info' => $debug_info
            ], JSON_PRETTY_PRINT);
        } else {
            throw new Exception("All connection attempts failed. See debug info for details.");
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => $debug_info
    ], JSON_PRETTY_PRINT);
}