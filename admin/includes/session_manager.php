<?php
function initAdminSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400,
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict'
        ]);
    }
    
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
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