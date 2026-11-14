<?php
/**
 * Admin Password Reset Utility
 *
 * For security, this script has NO default password stored in it.
 * Set the new password through the ADMIN_RESET_PASSWORD environment variable
 * (or fill it in your gitignored config/local.php as ADMIN_RESET_PASSWORD),
 * run this file once in your browser, then DELETE this file.
 */

require_once 'config/db.php';
/**
 * Admin Password Reset Utility
 *
 * For security, this script has NO default password stored in it.
 * Set the new password through the ADMIN_RESET_PASSWORD environment variable
 * (or fill it in your gitignored config/local.php as ADMIN_RESET_PASSWORD),
 * run this file once in your browser, then DELETE this file.
 */

require_once 'config/db.php';



// New password for admin - must be supplied at runtime, never committed
$new_password = getenv('ADMIN_RESET_PASSWORD');
if (defined('ADMIN_RESET_PASSWORD')) {
    $new_password = ADMIN_RESET_PASSWORD;
}

if (!$new_password) {
    http_response_code(400);
    die('No reset password set. Set the ADMIN_RESET_PASSWORD environment variable (or define it in config/local.php) before running this script. Never hardcode a password here.');
}

if (strlen($new_password) < 8) {
    http_response_code(400);
    die('The new password must be at least 8 characters long.');
}

$admin_email = 'admin@lostandfound.com';

// Hash the password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
$stmt->bind_param('ss', $hashed_password, $admin_email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h2>Admin password has been reset successfully.</h2>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<hr>";
        echo "<p style='color: red;'><strong>IMPORTANT: DELETE this file (reset-admin-password.php) immediately for security.</strong></p>";
        echo "<p><a href='login.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    } else {
        echo "<h2>No admin user found with email: $admin_email</h2>";
        echo "<p>The admin user might not exist in the database yet.</p>";
        echo "<p>Create an admin account first (see README - register, then promote the user role in the database).</p>";
    }
} else {
    echo "<h2>Error updating password</h2>";
    echo "<p>" . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
?>
