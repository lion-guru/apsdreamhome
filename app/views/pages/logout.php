<?php
/**
 * APS Dream Homes - Standardized Logout
 * Securely destroys session and redirects to login
 */

require_once __DIR__ . '/init.php';

// Log the logout event if user was logged in
if (isset($_SESSION['uid'])) {
    error_log('User logged out - UID: ' . $_SESSION['uid'] . ' Name: ' . ($_SESSION['name'] ?? 'Unknown'));
} elseif (isset($_SESSION['customer_id'])) {
    error_log('Customer logged out - ID: ' . $_SESSION['customer_id']);
}

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header("Location: login.php?logout=success");
exit;
