<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/admin_login_handler.php';
require_once __DIR__ . '/includes/csrf_protection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('login_error', 'Invalid CSRF token. Please refresh the page and try again.');
        header('Location: index.php');
        exit();
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha_answer = isset($_POST['captcha_answer']) ? intval($_POST['captcha_answer']) : 0;

    // Re-enable CAPTCHA check
    if (!isset($_SESSION['captcha_answer']) || $captcha_answer !== (int)$_SESSION['captcha_answer']) {
        setSessionget_flash('login_error', 'Invalid CAPTCHA answer.');
        header('Location: index.php');
        exit();
    }

    // Process login
    $result = AdminLoginHandler::login($username, $password);

    if ($result['status'] === 'success') {
        // Redirect to appropriate dashboard based on role
        header('Location: ' . ($result['redirect'] ?? 'dashboard.php'));
        exit();
    } else {
        setSessionget_flash('login_error', $result['message']);
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
