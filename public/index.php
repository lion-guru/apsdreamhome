<?php
/**
 * APS Dream Home - Entry Point
 */

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Autoload
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/app.php';

// Start session
session_start();

// Load router
require_once ROOT_PATH . '/routes/index.php';
?>