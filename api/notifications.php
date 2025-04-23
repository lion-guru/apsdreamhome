<?php
// API endpoint to fetch recent notifications for the authenticated user
header('Content-Type: application/json');
require_once '../config.php';
session_start();
if (!isset($_SESSION['auser'])) {
    echo json_encode(['error'=>'Not authenticated']);
    exit;
}
$user_id = $_SESSION['auser'];
$res = $conn->query("SELECT id, type, message, created_at FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 50");
$notifications = [];
while($row = $res->fetch_assoc()) $notifications[] = $row;
echo json_encode(['notifications'=>$notifications]);
