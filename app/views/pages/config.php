<?php
// Simplified Configuration for Development - APS Dream Home
// Disable error display in production
ini_set('display_errors', 1); // Enable for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/config_error.log');
error_reporting(E_ALL);

// Create logs directory if needed
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Simple logging function
if (!function_exists('logSecurityEvent')) {
    function logSecurityEvent($event, $context = []) {
        $logFile = __DIR__ . '/logs/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$event}{$contextStr}\n";
        
        if (is_writable(dirname($logFile)) || file_exists($logFile)) {
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
        error_log($logMessage);
    }
}

// Start secure session configuration (only if session not active)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
}

// Environment-based Database Configuration
function getEnvVar($key, $default = null) {
    // First check environment variables
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    // Fallback to .env file
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, $key . '=') === 0) {
                return trim(substr($line, strlen($key . '=')));
            }
        }
    }
    return $default;
}

// Load database configuration - use defined() to prevent redefinition
if (!defined('DB_HOST')) {
    define('DB_HOST', getEnvVar('DB_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', getEnvVar('DB_USER', 'root'));
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getEnvVar('DB_PASSWORD', ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getEnvVar('DB_NAME', 'apsdreamhome'));
}

// Compatibility
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}

// Create database connection
try {
    $con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($con->connect_error) {
        throw new Exception("Connection failed: " . $con->connect_error);
    }

    // Set charset
    if (!$con->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $con->error);
    }

    // Compatibility alias
    $conn = $con;

    logSecurityEvent('Database Connection Established', [
        'host' => DB_HOST,
        'database' => DB_NAME
    ]);

} catch (Exception $e) {
    logSecurityEvent('Database Connection Failed', [
        'error' => $e->getMessage(),
        'host' => DB_HOST,
        'database' => DB_NAME
    ]);
    // Show generic message without exposing sensitive information
    http_response_code(500);
    die("Database connection error. Please check your configuration.");
}

// Define base URL
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove the script filename if present
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/');
    
    // Construct the base URL
    $base_url = $protocol . $host . $base_path . '/';
    
    // Use environment variable if set, otherwise use the constructed URL
    $base_url = getEnvVar('BASE_URL', $base_url);
    
    // Ensure the URL ends with a slash
    $base_url = rtrim($base_url, '/') . '/';
    
    define('BASE_URL', $base_url);
    
    // For debugging
    if (!defined('ENVIRONMENT') || ENVIRONMENT === 'development') {
        error_log('Base URL set to: ' . BASE_URL);
    }
}

// Simple RBAC functions (simplified for development)
// Include core functions
require_once __DIR__ . '/core/functions.php';
?>