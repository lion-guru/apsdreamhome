<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$password_changed = isset($_GET['password_changed']) && $_GET['password_changed'] == 1;

// If password was just changed, set a session message before destroying the session
if ($password_changed) {
    $_SESSION['success_message'] = 'Your password has been changed successfully. Please log in with your new password.';
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with message if password was changed
$redirect = 'index.php' . ($password_changed ? '?password_changed=1' : '');
header('Location: ' . $redirect);
exit();