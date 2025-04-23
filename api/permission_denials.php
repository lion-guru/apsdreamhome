<?php
// API endpoint to fetch recent permission denials (admin only)
header('Content-Type: application/json');
require_once '../config.php';
session_start();
if (!isset($_SESSION['auser'])) {
    echo json_encode(['error'=>'Not authenticated']);
    exit;
}
$user_id = $_SESSION['auser'];
// Check if user is admin
$admin_check = $conn->query("SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=$user_id AND r.name='Admin'");
if ($admin_check->num_rows == 0) {
    echo json_encode(['error'=>'Not authorized']);
    exit;
}
$res = $conn->query("SELECT al.id, e.name as user, al.details, al.ip_address, al.created_at FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id WHERE al.action='Permission Denied' ORDER BY al.id DESC LIMIT 100");
denials = [];
while($row = $res->fetch_assoc()) $denials[] = $row;
echo json_encode(['permission_denials'=>$denials]);
