<?php
// Enhanced database security configuration

// Logging function
function log_db_event($message, $level = 'info') {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/database_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] [{$level}] {$message}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Secure database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhomefinal');

// Create a secure connection function
if (!function_exists('get_db_connection')) {
    function get_db_connection() {
        static $conn = null;
        if ($conn === null) {
            try {
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                $conn->set_charset("utf8mb4");
                // Enable SSL if possible
                if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
                    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
                }
                log_db_event("Database connection established successfully");
            } catch (Exception $e) {
                log_db_event("Database connection error: " . $e->getMessage(), 'error');
                die("A system error occurred. Our team has been notified.");
            }
        }
        return $conn;
    }
}

// Connection health check
function check_db_connection() {
    try {
        $conn = get_db_connection();
        $result = $conn->query("SELECT 1");
        if ($result) {
            log_db_event("Database health check passed");
            return true;
        } else {
            log_db_event("Database health check failed", 'warning');
            return false;
        }
    } catch (Exception $e) {
        log_db_event("Database health check error: " . $e->getMessage(), 'error');
        return false;
    }
}

// Automatic connection validation
register_shutdown_function('check_db_connection');
