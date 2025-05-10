<?php
// Create this file as debug.php in your project
header('Content-Type: application/json');

// Show all PHP errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo json_encode([
    'post_data' => $_POST,
    'session_data' => $_SESSION ?? 'No session',
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
    ]
]);