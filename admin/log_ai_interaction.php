<?php
// Logs AI panel interactions and feedback for continual learning
session_start();
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';
$action = $_POST['action'] ?? 'view';
$suggestion = $_POST['suggestion'] ?? '';
$feedback = $_POST['feedback'] ?? '';
$notes = $_POST['notes'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!$user_id || !$suggestion) {
    echo json_encode(['success'=>false,'error'=>'Missing user or suggestion']);
    exit;
}

// Blocklist check
$blocklist_file = __DIR__ . '/blocked_entities.json';
$blocked = ["ips"=>[],"users"=>[]];
if (file_exists($blocklist_file)) {
    $blocked = json_decode(file_get_contents($blocklist_file), true);
    if (!is_array($blocked)) $blocked = ["ips"=>[],"users"=>[]];
}
if (in_array($ip_address, $blocked['ips']) || in_array($user_id, $blocked['users'])) {
    echo json_encode(['success'=>false,'error'=>'Blocked IP or User']);
    exit;
}

$stmt = $con->prepare("INSERT INTO ai_interactions (user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssss", $user_id, $role, $action, $suggestion, $feedback, $notes, $ip_address, $user_agent);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'DB error']);
}
