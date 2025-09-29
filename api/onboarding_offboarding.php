<?php
// API endpoint to fetch recent onboarding and offboarding events (admin only)
header('Content-Type: application/json');
require_once '../config.php';
session_start();
if (!isset($_SESSION['auser'])) {
    echo json_encode(['error'=>'Not authenticated']);
    exit;
}
$user_id = $_SESSION['auser'];
// Check if user is admin
$admin_check = $conn->prepare("SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=? AND r.name='Admin'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($admin_check->num_rows == 0) {
    echo json_encode(['error'=>'Not authorized']);
    exit;
}
$res = $conn->query("SELECT al.id, e.name as user, al.action, al.details, al.ip_address, al.created_at FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id WHERE al.action IN ('Onboarding','Offboarding') ORDER BY al.id DESC LIMIT 100");
$events = [];
while($row = $res->fetch_assoc()) $events[] = $row;
echo json_encode(['onboarding_offboarding'=>$events]);
