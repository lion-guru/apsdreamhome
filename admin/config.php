<?php
/**
 * Admin Configuration File
 * This file provides database connection and other configurations for the admin panel
 */

// Include main configuration files
require_once(__DIR__ . '/../includes/config/config.php');
// require_once(__DIR__ . '/../includes/functions/asset_helper.php'); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead

// Include db_config to provide getDbConnection()
require_once(__DIR__ . '/../includes/db_config.php');

// Get database connection
$con = getDbConnection();
if (!$con) {
    die('Database connection failed. Please check your configuration and try again.');
}

// Set admin-specific constants
define('ADMIN_BASE_URL', '/apsdreamhomefinal/admin');
define('ADMIN_ASSETS_URL', ADMIN_BASE_URL . '/assets');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin authentication check
function checkAdminAuth() {
    if (!isset($_SESSION['auser'])) {
        header('location:index.php');
        exit();
    }
}
