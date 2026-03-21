<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = register($name, $email, $password, $phone);
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-plus auth-logo"></i>
            <h2>Create Account</h2>
            <p>Join <?= APP_NAME ?></p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?> <a href="login.php">Login now</a></div>
        <?php endif; ?>
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password *</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                    <button type="button" class="toggle-password" onclick="togglePwd('password')"><i class="fas fa-eye" id="eye-password"></i></button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                <div class="password-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                    <button type="button" class="toggle-password" onclick="togglePwd('confirm_password')"><i class="fas fa-eye" id="eye-confirm_password"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="index.php"><i class="fas fa-home"></i> Back to Home</a></p>
        </div>
    </div>
</div>
<script>
function togglePwd(id) {
    const inp = document.getElementById(id);
    const eye = document.getElementById('eye-' + id);
    if (inp.type === 'password') { inp.type = 'text'; eye.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type = 'password'; eye.classList.replace('fa-eye-slash','fa-eye'); }
}
</script>
</body>
</html>
