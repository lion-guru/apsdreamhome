<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers (added from login.php)
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Include necessary files
require_once __DIR__ . '/../includes/config/db_config.php';
require_once __DIR__ . '/includes/csrf_protection.php';

// Generate CSRF token
$csrf_token = CSRFProtection::generateToken();

// Check for login error
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Generate simple CAPTCHA
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_num1_admin'] = $num1;
$_SESSION['captcha_num2_admin'] = $num2;
$_SESSION['captcha_answer'] = $num1 + $num2;
$captcha_question = "$num1 + $num2 = ?";

// Check if already logged in
if (isset($_SESSION['admin_session']['is_authenticated']) && $_SESSION['admin_session']['is_authenticated'] === true) {
    header('Location: dashboard.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 50px auto; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            box-sizing: border-box; 
        }
        .error { 
            color: red; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
<div class="login-container">
        <div class="login-header" style="display:flex;flex-direction:column;align-items:center;gap:0.4rem;margin-bottom:1.2rem;">
        <div class="panel-title" style="font-size:1.45rem;font-weight:700;color:#0d6efd;letter-spacing:1px;">APS Dream Homes</div>
        <div style="font-size:1.05rem;color:#444;font-weight:500;">Admin Panel Login</div>
        <div class="panel-desc" style="font-size:0.98rem;color:#666;">Welcome! Only authorized personnel may proceed.</div>
    </div>
    <?php if (!empty($login_error)): ?>
    <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>
    <form action="process_login.php" method="post" autocomplete="off" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
        </div>
        <div class="mb-3">
            <label for="captcha_answer" class="form-label">Security Question: <?php echo $captcha_question; ?></label>
            <input type="number" class="form-control" id="captcha_answer" name="captcha_answer" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="mt-3 text-center">
        <a href="../index.php">Back to Home</a>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>