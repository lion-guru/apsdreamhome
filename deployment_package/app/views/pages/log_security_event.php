<?php
/**
 * Security Event Logger
 * Handles logging of security-related events from the application
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] ?? '*');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

if (empty($data['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Event is required']);
    exit();
}

// Log the event to a file
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$log_file = $log_dir . '/security_events.log';
$timestamp = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

$log_data = [
    'timestamp' => $timestamp,
    'ip' => $ip,
    'user_agent' => $user_agent,
    'referrer' => $referrer,
    'event' => $data['event'],
    'context' => $data['context'] ?? [],
    'csrf_token' => $data['csrf_token'] ?? null
];

$log_line = json_encode($log_data) . PHP_EOL;

// Write to log file
file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success']);
