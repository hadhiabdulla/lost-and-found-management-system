<?php
/**
 * Admin Password Reset Utility
 * Run this file once in your browser to reset the admin password
 * Then DELETE this file for security
 */

require_once 'config/db.php';

// New password for admin
$new_password = 'admin123';
$admin_email = 'admin@lostandfound.com';

// Hash the password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
$stmt->bind_param('ss', $hashed_password, $admin_email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h2>✅ Admin password has been reset successfully!</h2>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<p><strong>New Password:</strong> $new_password</p>";
        echo "<hr>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANT: DELETE this file (reset-admin-password.php) immediately for security!</strong></p>";
        echo "<p><a href='login.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    } else {
        echo "<h2>❌ No admin user found with email: $admin_email</h2>";
        echo "<p>The admin user might not exist in the database yet.</p>";
        echo "<p>Please run the database/schema.sql file first to create the admin user.</p>";
    }
} else {
    echo "<h2>❌ Error updating password</h2>";
    echo "<p>" . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
?>
