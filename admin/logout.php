<?php
/**
 * Admin Logout Handler
 * Handles secure session termination and cleanup
 */

require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/log_admin_action_db.php';

// Initialize session if not already started
initAdminSession();

// Log the logout event before clearing session
if (isset($_SESSION['admin_logged_in'], $_SESSION['admin_name'], $_SESSION['admin_role']) && $_SESSION['admin_logged_in']) {
    log_admin_action_db(
        ($_SESSION['admin_role']==='super_admin'?'superadmin_logout':'admin_logout'),
        'Logout: ' . $_SESSION['admin_name']
    );
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/', '', true, true);
}

// Destroy the session
session_destroy();

// Redirect to login page with a message
header('Location: login.php?msg=logged_out');
exit();