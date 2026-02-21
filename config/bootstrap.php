<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants only if not already defined
if (!defined('APP_NAME')) define('APP_NAME', 'APSDreamHome');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');

// Define application constants only if not already defined
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_ROOT . '/config');
if (!defined('APP_PATH')) define('APP_PATH', APP_ROOT . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', APP_ROOT . '/public');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', APP_ROOT . '/storage');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/views');
if (!defined('CORE_PATH')) define('CORE_PATH', APP_PATH . '/core');
if (!defined('CONTROLLER_PATH')) define('CONTROLLER_PATH', APP_PATH . '/controllers');
if (!defined('MODEL_PATH')) define('MODEL_PATH', APP_PATH . '/models');
if (!defined('HELPER_PATH')) define('HELPER_PATH', APP_PATH . '/helpers');
if (!defined('ROUTE_PATH')) define('ROUTE_PATH', APP_PATH . '/routes');
if (!defined('SERVICE_PATH')) define('SERVICE_PATH', APP_PATH . '/services');
if (!defined('MIDDLEWARE_PATH')) define('MIDDLEWARE_PATH', APP_PATH . '/middleware');
if (!defined('CACHE_PATH')) define('CACHE_PATH', STORAGE_PATH . '/cache');
if (!defined('LOG_PATH')) define('LOG_PATH', STORAGE_PATH . '/logs');
if (!defined('SESSION_PATH')) define('SESSION_PATH', STORAGE_PATH . '/sessions');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);
    // Remove /public if it exists in the path
    if (substr($script, -7) === '/public') {
        $script = substr($script, 0, -7);
    }
    // Remove /index.php if it exists
    $script = str_replace('/index.php', '', $script);

    define('BASE_URL', rtrim("$protocol://$host$script", '/') . '/');
}

// Environment detection
$environment = getenv('APP_ENV') ?: 'development';
if (!defined('APP_ENV')) {
    define('APP_ENV', $environment);
}

// Initialize global configuration array
global $config;
$config = [];

// Load environment-specific configuration
if (file_exists(CONFIG_PATH . '/environments/' . APP_ENV . '.php')) {
    $envConfig = require CONFIG_PATH . '/environments/' . APP_ENV . '.php';
    if (!is_array($envConfig)) {
        error_log("Environment config returned non-array: " . gettype($envConfig));
        $envConfig = [];
    }
    $config = array_merge($config, $envConfig);
}

// Load database configuration
if (file_exists(CONFIG_PATH . '/database.php')) {
    $dbConfig = require CONFIG_PATH . '/database.php';
    if (!is_array($dbConfig)) {
        error_log("Database config returned non-array: " . gettype($dbConfig));
        $dbConfig = [];
    }
    $config = array_merge($config, $dbConfig);
}

// Load application configuration
if (file_exists(CONFIG_PATH . '/application.php')) {
    $appConfig = require CONFIG_PATH . '/application.php';
    if (!is_array($appConfig)) {
        error_log("Application config returned non-array: " . gettype($appConfig));
        $appConfig = [];
    }
    $config = array_merge($config, $appConfig);
}

// Load security configuration
if (file_exists(CONFIG_PATH . '/security.php')) {
    $securityConfig = require CONFIG_PATH . '/security.php';
    if (is_array($securityConfig)) {
        $config = array_merge($config, $securityConfig);
    }
}

// Include core system files
require_once CORE_PATH . '/Autoloader.php';
// SessionManager is now autoloaded
// require_once CORE_PATH . '/SessionManager.php';
if (file_exists(CORE_PATH . '/ErrorHandler.php')) {
    require_once CORE_PATH . '/ErrorHandler.php';
}
if (file_exists(CORE_PATH . '/SystemIntegration.php')) {
    require_once CORE_PATH . '/SystemIntegration.php';
}
// Database is autoloaded
// require_once CORE_PATH . '/Database.php';

// Include global helper functions
if (file_exists(APP_PATH . '/helpers.php')) {
    require_once APP_PATH . '/helpers.php';
}
