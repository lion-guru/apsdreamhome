<?php
/**
 * Customer Logout Script - APS Dream Home
 * Handles secure customer logout
 */

session_start();

// Log the logout event
if (isset($_SESSION['user_id'])) {
    error_log('Customer logged out - ID: ' . $_SESSION['user_id'] . ', Email: ' . $_SESSION['user_email']);
}

// Clear all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page with success message
header('Location: index.php?logout=success');
exit();
