<?php
// API endpoint to send SMS notifications (admin only)
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
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['to']) || !isset($data['message'])) {
    echo json_encode(['error'=>'Missing parameters']);
    exit;
}
// TODO: Integrate with actual SMS gateway API
// Placeholder: Simulate SMS sending
$sms_sent = true; // set to false if integration fails
// $sms_sent = send_sms($data['to'], $data['message']);
echo json_encode(['sent'=>$sms_sent]);
