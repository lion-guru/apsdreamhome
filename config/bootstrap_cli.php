<?php
// CLI bootstrap - no headers, minimal setup
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . '/app');
define('CONFIG_PATH', dirname(__DIR__) . '/config');
define('STORAGE_PATH', dirname(__DIR__) . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';

// Minimal config
define('APP_NAME', 'APSDreamHome');
define('APP_VERSION', '1.0.0');
define('ENVIRONMENT', 'development');
define('APP_ENV', 'development');

ini_set('display_errors', 1);
error_reporting(E_ALL);

