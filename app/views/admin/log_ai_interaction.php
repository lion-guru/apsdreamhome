<?php
/**
 * Logs AI panel interactions and feedback - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// CSRF check for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        error_log("CSRF failure in log_ai_interaction.php from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'guest';
$action = SecurityUtility::sanitizeInput($_POST['action'] ?? 'view', 'string');
$suggestion = SecurityUtility::sanitizeInput($_POST['suggestion'] ?? '', 'string');
$feedback = SecurityUtility::sanitizeInput($_POST['feedback'] ?? '', 'string');
$notes = SecurityUtility::sanitizeInput($_POST['notes'] ?? '', 'string');
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!$suggestion) {
    echo json_encode(['success' => false, 'error' => 'Missing suggestion']);
    exit;
}

// Blocklist check
$blocklist_file = __DIR__ . '/blocked_entities.json';
$blocked = ["ips" => [], "users" => []];
if (file_exists($blocklist_file)) {
    $blocked = json_decode(file_get_contents($blocklist_file), true);
    if (!is_array($blocked)) $blocked = ["ips" => [], "users" => []];
}

if (in_array($ip_address, $blocked['ips']) || in_array($user_id, $blocked['users'])) {
    echo json_encode(['success' => false, 'error' => 'Blocked IP or User']);
    exit;
}

$db = \App\Core\App::database();
$ok = $db->execute(
    "INSERT INTO ai_interactions (user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
    [$user_id, $role, $action, $suggestion, $feedback, $notes, $ip_address, $user_agent]
);

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'DB error']);
}
