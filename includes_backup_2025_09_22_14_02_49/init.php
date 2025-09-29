<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path first
define('BASE_PATH', dirname(__DIR__));

// Include required files
require_once BASE_PATH . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/functions.php';

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Create required directories if they don't exist
$required_dirs = array(
    BASE_PATH . '/uploads',
    BASE_PATH . '/uploads/property',
    BASE_PATH . '/uploads/users',
    BASE_PATH . '/logs'
);

foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        try {
            mkdir($dir, 0755, true);
        } catch (Exception $e) {
            error_log("Failed to create directory: " . $dir . " - " . $e->getMessage());
        }
    }
}
?>