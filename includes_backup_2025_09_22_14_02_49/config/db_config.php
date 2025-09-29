<?php
/**
 * Database Configuration
 * Provides database connection settings and functions
 */

// Include the main database connection file
require_once __DIR__ . '/../db_connection.php';

// Default database configuration
$default_db_config = [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'apsdreamhomefinal',
    'DB_CHARSET' => 'utf8mb4'
];

// Set constants if not already defined
foreach ($default_db_config as $key => $value) {
    if (!defined($key)) {
        define($key, $value);
    }
}

// Error reporting for development
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Legacy function for backward compatibility
if (!function_exists('getDbConnectionLegacy')) {
    function getDbConnectionLegacy() {
        try {
            return getDbConnection();
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
}
