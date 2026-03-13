<?php
/**
 * APS Dream Home - Unified Base System
 * Complete unified system for all project functionality
 * Version: 2.0.0
 * Created: March 6, 2026
 */

// === SYSTEM INITIALIZATION ===

// Root path detection
define('APS_ROOT', dirname(dirname(dirname(__DIR__)))); // Go up 3 levels from app/Core/Bootstrap to root
define('APS_VERSION', '2.0.0');
define('APS_CREATED', '2026-03-06');

// Environment setup
$environment = $_ENV['APP_ENV'] ?? 'development';
if (!defined('APS_ENV')) define('APS_ENV', $environment);

// Core paths
if (!defined('APS_APP')) define('APS_APP', APS_ROOT . '/app');
if (!defined('APP_PATH')) define('APP_PATH', APS_APP); // Alias for legacy support
if (!defined('APS_PUBLIC')) define('APS_PUBLIC', APS_ROOT . '/public');
if (!defined('APS_CONFIG')) define('APS_CONFIG', APS_ROOT . '/config');
if (!defined('APS_STORAGE')) define('APS_STORAGE', APS_ROOT . '/storage');
if (!defined('APS_VENDOR')) define('APS_VENDOR', APS_ROOT . '/vendor');
if (!defined('APS_ASSETS')) define('APS_ASSETS', APS_PUBLIC . '/assets');

// URL configuration
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/apsdreamhome/public';
if (!defined('BASE_URL')) define('BASE_URL', $baseUrl);
if (!defined('APS_URL')) define('APS_URL', $baseUrl);

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
if (!defined('DB_DATABASE')) define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'apsdreamhome');
if (!defined('DB_USERNAME')) define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Session configuration
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600);
if (!defined('SESSION_PATH')) define('SESSION_PATH', '/');

// Error reporting
if (APS_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// === AUTOLOADER ===

if (file_exists(APS_VENDOR . '/autoload.php')) {
    require_once APS_VENDOR . '/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = APS_APP . '/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

// === CORE FUNCTIONS ===

/**
 * Log system events
 */
function aps_log($message, $level = 'info') {
    $logDir = APS_ROOT . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/aps_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Get configuration value
 */
function aps_config($key, $default = null) {
    $configFile = APS_CONFIG . '/unified.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
        return $config[$key] ?? $default;
    }
    return $default;
}
