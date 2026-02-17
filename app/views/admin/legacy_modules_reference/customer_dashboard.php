<?php
/**
 * Enhanced Customer Dashboard - Modern & Beautiful - Updated with Session Management
 * Professional design with animations and better UX
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

require_once __DIR__ . '/core/init.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: customer_login.php');
    exit();
}

// Get customer info
$customer_id = $_SESSION['customer_id'] ?? 0;
$customer_name = $_SESSION['customer_name'] ?? 'Customer';
