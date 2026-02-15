<?php
/**
 * Admin Initialization
 * Uses the legacy bootstrap for consistent security and configuration
 */
require_once __DIR__ . '/../../../includes/legacy_bootstrap.php';

// Session timeout and activity tracking
if (isSessionTimedOut()) {
    destroyAuthSession();
    header('Location: /apsdreamhome/admin/login.php?timeout=1');
    exit();
}
updateLastActivity();

// Admin specific functions
require_once __DIR__ . '/functions.php';

// Include global config if available
if (file_exists(__DIR__ . '/../../app/views/layouts/config.php')) {
    include_once __DIR__ . '/../../app/views/layouts/config.php';
}

// Include and initialize Multi-Language Support after DB connection
require_once __DIR__ . '/../../includes/MultiLanguageSupport.php';
$mlSupport = new MultiLanguageSupport($conn);
$mlSupport->setLanguage($_SESSION['admin_lang'] ?? 'en');

// Constants
if (!defined('SECURE_ACCESS')) define('SECURE_ACCESS', true);
if (!defined('BASE_URL')) define('BASE_URL', '/apsdreamhome/');
define('ADMIN_SESSION_TIMEOUT', 1800); // 30 minutes
define('ADMIN_ROOT_PATH', dirname(__DIR__));
define('SITE_ROOT_PATH', dirname(ADMIN_ROOT_PATH));
define('ADMIN_URL', '/apsdreamhome/admin');
define('SITE_URL', '/apsdreamhome');

// Check admin session
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['index.php', 'login.php', 'auto_login.php', 'process_login.php', 'forgot_password.php'];

if (PHP_SAPI !== 'cli' && !in_array($current_page, $public_pages)) {
    if (!isAuthenticated() || !isAdmin()) {
        if (!headers_sent()) {
            $login_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'index.php' : 'login.php';
            header("Location: $login_path");
            exit();
        } else {
            echo "<script>window.location.href='index.php';</script>";
            exit();
        }
    }
}

require_once __DIR__ . '/../../../app/helpers.php';

// Function to generate CSRF token
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return getCsrfToken();
    }
}

// Function to verify CSRF token
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token = null) {
        return validateCsrfToken($token);
    }
}

if (!function_exists('getCsrfField')) {
    function getCsrfField() {
        $token = generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
    }
}

// Check if this is an admin page (not login page)
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'index.php' && 
    $current_page !== 'login.php' && 
    $current_page !== 'auto_login.php') {

}
?>
