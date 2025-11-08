<?php
/**
 * Customer Logout Script
 * Handles secure customer logout
 */

require_once 'includes/session_manager.php';

// Log the logout event
if (isset($_SESSION['customer_id'])) {
    error_log('Customer logged out - ID: ' . $_SESSION['customer_id']);
}

// Call the logout function
customerLogout();

// If somehow we're still here, redirect to login page
header('Location: customer_login.php');
exit();
