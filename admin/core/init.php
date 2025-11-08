<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helper functions
require_once __DIR__ . '/functions.php';

// Set secure headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Include essential files
require_once __DIR__ . '/../../includes/config/db_config.php';
require_once __DIR__ . '/../../includes/classes/SessionManager.php';
require_once __DIR__ . '/../../includes/classes/Authentication.php';

// Initialize database connection
$conn = getDbConnection();
if (!$conn) {
    error_log("Failed to connect to database in admin init");
    die("Database connection failed. Please try again later.");
}

// Constants
define('ADMIN_SESSION_TIMEOUT', 1800); // 30 minutes
define('ADMIN_ROOT_PATH', dirname(__DIR__));
define('SITE_ROOT_PATH', dirname(ADMIN_ROOT_PATH));

// Check admin session
function checkAdminSession() {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_session']) || 
        !isset($_SESSION['admin_session']['is_authenticated']) || 
        $_SESSION['admin_session']['is_authenticated'] !== true) {
        header('Location: ' . '/apsdreamhome/admin/index.php');
        exit();
    }

    // Check session timeout
    if (isset($_SESSION['admin_session']['last_activity']) && 
        (time() - $_SESSION['admin_session']['last_activity'] > ADMIN_SESSION_TIMEOUT)) {
        // Session has expired
        session_unset();
        session_destroy();
        header('Location: ' . '/apsdreamhome/admin/index.php?error=session_expired');
        exit();
    }

    // Update last activity
    $_SESSION['admin_session']['last_activity'] = time();
}

// Function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        error_log("CSRF token verification failed");
        return false;
    }
    return true;
}

// Check if this is an admin page (not login page)
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'index.php' && 
    $current_page !== 'login.php' && 
    $current_page !== 'auto_login.php') {
    checkAdminSession();
}
?>
