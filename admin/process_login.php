<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/admin_login_handler.php';

echo 'DEBUG: Entered process_login.php<br>';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha_answer = isset($_POST['captcha_answer']) ? intval($_POST['captcha_answer']) : 0;

    // CAPTCHA temporarily disabled for debugging
    /*
    if (!isset($_SESSION['captcha_answer']) || $captcha_answer !== (int)$_SESSION['captcha_answer']) {
        // Debug info for CAPTCHA troubleshooting
        $expected = isset($_SESSION['captcha_answer']) ? $_SESSION['captcha_answer'] : 'NOT SET';
        $_SESSION['login_error'] = 'Invalid CAPTCHA answer. (You entered: ' . htmlspecialchars($captcha_answer) . ', Expected: ' . htmlspecialchars($expected) . ')';
        echo 'DEBUG: CAPTCHA mismatch, redirecting to index.php<br>';
        header('Location: index.php');
        exit();
    }
    */

    echo 'DEBUG: Before AdminLoginHandler::login<br>';
    // Process login
    $result = AdminLoginHandler::login($username, $password);
    echo 'DEBUG: After AdminLoginHandler::login, status: ' . htmlspecialchars($result['status']) . '<br>';

    if ($result['status'] === 'success') {
        echo 'DEBUG: Login success, redirecting to dashboard.php<br>';
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        echo 'DEBUG: Login failed, redirecting to index.php<br>';
        $_SESSION['login_error'] = $result['message'];
        header('Location: index.php');
        exit();
    }
} else {
    echo 'DEBUG: Not a POST request, redirecting to index.php<br>';
    header('Location: index.php');
    exit();
}
?>
