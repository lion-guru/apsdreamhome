<?php
// Include security and database configurations
require_once __DIR__ . '/includes/security_config.php';
require_once __DIR__ . '/includes/db_config.php';

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
initializeSecurity();

// Get database connection
// $con = getDbConnection();
// if (!$con) {
//     die('Database connection failed. Please check your configuration and try again.');
// }

// --- RBAC ENFORCEMENT SNIPPET ---
function require_role($role_name) {
    global $conn;
    if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
    $user_id = $_SESSION['auser'];
    $sql = "SELECT COUNT(*) as c FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id=? AND r.name=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $role_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['c'] == 0) { echo '<div class=\'alert alert-danger\'>Access denied for role: '.htmlspecialchars($role_name).'</div>'; exit(); }
}

// --- ACTION-LEVEL PERMISSION ENFORCEMENT ---
function require_permission($action) {
    global $conn;
    if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
    $user_id = $_SESSION['auser'];
    $sql = "SELECT COUNT(*) as c FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id=? AND p.action=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $action);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['c'] == 0) {
        // Log permission denial
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $details = 'Permission denied for action: ' . $action;
        $stmt2 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Permission Denied', ?, ?)");
        $stmt2->bind_param('iss', $user_id, $details, $ip);
        $stmt2->execute();
        echo '<div class=\'alert alert-danger\'>Access denied for action: '.htmlspecialchars($action).'</div>';
        exit();
    }
}

$host = 'localhost';
$db = 'apsdreamhomefinal';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die('Connection failed: ' . $conn->connect_error); }