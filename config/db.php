<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lost_found_db');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Session configuration
define('APP_NAME', 'Lost & Found Management System');
define('BASE_URL', 'http://localhost/lost-and-found-management-system');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
