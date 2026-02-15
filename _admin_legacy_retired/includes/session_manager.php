<?php
function initAdminSession() {
    $isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
    $cookieSecure = $isLocalhost ? false : true;
    $cookieSameSite = $isLocalhost ? 'Lax' : 'Strict';
    $cookieParams = [
        'cookie_lifetime' => 86400,
        'cookie_secure' => $cookieSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => $cookieSameSite,
        'cookie_path' => '/', // Ensure session cookie is valid for all paths
    ];
    if (session_status() === PHP_SESSION_NONE) {
        session_start($cookieParams);
    }
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    }
    $_SESSION['LAST_ACTIVITY'] = time();

    // Generate CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validateAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    // Session timeout: 30 minutes (1800 seconds)
    $timeout = 1800;
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    return true;
}