<?php
// Log Super Admin/Admin actions to the database audit log
function log_admin_action_db($action, $details = '') {
    if (!isset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role'])) {
        return false; // Not authenticated
    }
    $admin_id = $_SESSION['admin_id'];
    $username = $_SESSION['admin_name'];
    $role = $_SESSION['admin_role'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $con = function_exists('getDbConnection') ? getDbConnection() : null;
    if (!$con) return false;
    $stmt = $con->prepare("INSERT INTO admin_activity_log (admin_id, username, role, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $admin_id, $username, $role, $action, $details, $ip, $ua);
    $success = $stmt->execute();
    $stmt->close();
    $con->close();
    return $success;
}
