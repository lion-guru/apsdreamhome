<?php

/**
 * APS Dream Home - Public Index (Main Entry Point)
 */

// Define APS_ROOT FIRST
define('APS_ROOT', dirname(__DIR__));
define('APS_PUBLIC', __DIR__);

// Load bootstrap (config, session settings, autoloader, BASE_URL, everything)
require_once APS_ROOT . '/config/bootstrap.php';

// Define APS-specific constants
if (!defined('APS_APP')) define('APS_APP', APS_ROOT . '/app');
if (!defined('APS_CONFIG')) define('APS_CONFIG', APS_ROOT . '/config');
if (!defined('APS_STORAGE')) define('APS_STORAGE', APS_ROOT . '/storage');
if (!defined('APS_LOGS')) define('APS_LOGS', APS_ROOT . '/logs');

// Session configuration from bootstrap - session_start() will use these settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', APS_STORAGE . '/sessions');
    session_start();
}

// Remember-me auto-login: a valid persistent cookie restores the session.
// Only active + approved accounts; token rotates on every use inside the service.
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    try {
        require_once APS_ROOT . '/app/Services/Auth/RememberMeService.php';
        $rememberUserId = (new \App\Services\Auth\RememberMeService())->validateToken();
        if ($rememberUserId) {
            $rmDb = \App\Core\Database\Database::getInstance();
            $rmUser = $rmDb->fetchOne(
                "SELECT id, name, email, role FROM users WHERE id = ? AND status = 'active' AND registration_status = 'approved' LIMIT 1",
                [$rememberUserId]
            );
            if ($rmUser) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$rmUser['id'];
                $_SESSION['user_name'] = $rmUser['name'];
                $_SESSION['user_email'] = $rmUser['email'];
                $_SESSION['role'] = $rmUser['role'] ?? 'customer';
                $_SESSION['logged_in'] = true;
                $_SESSION['remembered'] = true;
            }
        }
    } catch (\Throwable $e) { error_log('remember-me auto-login: ' . $e->getMessage()); }
}

// Referral attribution net: persist ?ref= into a 30-day cookie + session so
// browse-first-register-later flows (forms, Google OAuth, smart OTP, claim,
// inquiries) never lose the referrer. Read everywhere via $_COOKIE['aps_ref'].
if (!empty($_GET['ref'])) {
    $refCookie = substr(preg_replace('/[^A-Za-z0-9\-_]/', '', (string)$_GET['ref']), 0, 50);
    if ($refCookie !== '') {
        setcookie('aps_ref', $refCookie, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE['aps_ref'] = $refCookie;
        $_SESSION['aps_ref'] = $refCookie;
    }
}

// Error reporting — gated by env (never expose traces in prod)
error_reporting(E_ALL);
if ((defined('APP_ENV') && APP_ENV === 'production') || getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
} else {
    ini_set('display_errors', (defined('APS_ENV') && APS_ENV === 'development') || getenv('APS_ENV') === 'development' ? 1 : 0);
}
ini_set('log_errors', 1);
ini_set('error_log', APS_LOGS . '/php_error.log');

// Catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("FATAL ERROR: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Check logs/php_error.log for details.</p>";
        if (ini_get('display_errors')) {
            echo "<pre>" . htmlspecialchars($error['message']) . "\nFile: " . $error['file'] . "\nLine: " . $error['line'] . "</pre>";
        }
    }
});

// Create router instance
require_once APS_ROOT . '/routes/router.php';
$router = new Router();

// Include routes
try {
    require_once APS_ROOT . '/routes/web.php';
} catch (\Exception $e) {
    error_log("Routes Error: " . $e->getMessage());
    echo "<h1>Error loading routes: " . htmlspecialchars($e->getMessage()) . "</h1>";
    exit;
}

// Dispatch router
try {
    $router->dispatch();
} catch (\Exception $e) {
    error_log("Router Error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>500 - Server Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    if (ini_get('display_errors')) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}?>