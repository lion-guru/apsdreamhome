<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', ''); // Default XAMPP has no password
define('DB_NAME', 'apsdreamhomefinal');

// Create database connection with error handling
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
    
    // Set SQL mode to strict
    $con->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
    
} catch (Exception $e) {
    // Log error and show generic message
    error_log("Database connection error: " . $e->getMessage());
    die("A database error occurred. Please try again later or contact support.");
}

// Helper function for legacy code compatibility
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        global $con;
        return $con;
    }
}

// Define base URL for the application
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhomefinal/');
}

// Helper for asset URLs (added by Cascade)
if (!function_exists('get_asset_url')) {
    function get_asset_url($file, $type = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $type_path = $type ? "$type/" : '';
        return $base . '/' . $type_path . $file;
    }
}

// Initialize security configurations
if (function_exists('initializeSecurity')) {
    initializeSecurity();
}

// --- RBAC ENFORCEMENT SNIPPET ---
if (!function_exists('require_role')) {
function require_role($role_name) {
    global $con; 
    if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
    $user_id = $_SESSION['auser'];
    $sql = "SELECT COUNT(*) as c FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id=? AND r.name=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('is', $user_id, $role_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['c'] == 0) { echo '<div class=\'alert alert-danger\'>Access denied for role: '.htmlspecialchars($role_name).'</div>'; exit(); }
}
}

// --- ACTION-LEVEL PERMISSION ENFORCEMENT ---
if (!function_exists('require_permission')) {
function require_permission($action) {
    global $con; 
    if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
    $user_id = $_SESSION['auser'];
    $sql = "SELECT COUNT(*) as c FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id=? AND p.action=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('is', $user_id, $action);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['c'] == 0) {
        // Log permission denial
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $details = 'Permission denied for action: ' . $action;
        $stmt2 = $con->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Permission Denied', ?, ?)");
        $stmt2->bind_param('iss', $user_id, $details, $ip);
        $stmt2->execute();
        echo '<div class=\'alert alert-danger\'>Access denied for action: '.htmlspecialchars($action).'</div>';
        exit();
    }
}
}

// Include security and database configurations
require_once __DIR__ . '/includes/security_config.php';
require_once __DIR__ . '/includes/db_config.php';

// Initialize security configurations
if (function_exists('initializeSecurity')) {
    initializeSecurity();
}
?>