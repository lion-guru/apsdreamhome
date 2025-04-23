<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/login_header.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/admin_login_handler.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

initAdminSession();

if (!isset($_SESSION['captcha_num1_admin'])) {
    $_SESSION['captcha_num1_admin'] = rand(1, 10);
    $_SESSION['captcha_num2_admin'] = rand(1, 10);
}
$captcha_question_admin = $_SESSION['captcha_num1_admin'] . ' + ' . $_SESSION['captcha_num2_admin'];

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$csrf_token = CSRFProtection::generateToken('admin_login');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AdminLoginHandler::handleLogin();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .login-container { background: white; padding: 2.5rem; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 420px; margin: 40px auto; }
        .login-header { text-align: center; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Admin Login</h2>
        </div>
        <?php 
        if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
            echo "<div class='alert alert-warning text-center'>Your session has expired due to inactivity. Please log in again.</div>";
        }
        if (isset($_SESSION['login_error'])) { echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>'; unset($_SESSION['login_error']); } ?>
        <form method="post" action="login.php" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_COOKIE['username'] ?? ''); ?>">
                <div class="invalid-feedback">Please enter your username.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <div class="mb-3">
                <label for="captcha_admin" class="form-label">What is <?php echo $captcha_question_admin; ?>?</label>
                <input type="number" class="form-control" name="captcha_answer" id="captcha_admin" required>
                <div class="invalid-feedback">Please solve the captcha.</div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <div class="mt-3 text-center">
                <a href="register.php">Register New Admin</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>