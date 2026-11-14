<?php
// Database configuration
// No real credentials are stored in this repository.
// Copy config/local.example.php to config/local.php (it is gitignored) and fill in
// your own local values, or set the DB_HOST, DB_USER, DB_PASS and DB_NAME
// environment variables. See README for setup.

$localConfig = __DIR__ . '/local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'lost_found_db');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Session configuration
if (!defined('APP_NAME')) define('APP_NAME', 'Lost & Found Management System');
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/lost-and-found-management-system');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
