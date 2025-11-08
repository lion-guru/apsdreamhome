<?php
/**
 * Session Diagnostic Script
 * Helps troubleshoot session and authentication issues
 */
session_start();

// Function to safely log diagnostic information
function logDiagnostics($message, $data = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/session_diagnostic.log';
    $maxLogSize = 1024 * 1024; // 1 MB max log size
    
    // Rotate log if it gets too large
    if (file_exists($logFile) && filesize($logFile) > $maxLogSize) {
        rename($logFile, $logFile . '.old');
    }
    
    $timestamp = date('Y-m-d H:i:s');
    
    $logEntry = "[{$timestamp}] {$message}\n";
    foreach ($data as $key => $value) {
        $logEntry .= "  {$key}: " . print_r($value, true) . "\n";
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Collect comprehensive session diagnostics
$sessionDiagnostics = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_status' => session_status(),
    'session_cookie_params' => session_get_cookie_params(),
    'server_request_method' => $_SERVER['REQUEST_METHOD'],
    'remote_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'session_variables' => $_SESSION,
    'cookie_variables' => $_COOKIE
];

// Log the session diagnostics
logDiagnostics('Session Diagnostic Report', $sessionDiagnostics);

// Check authentication status
$isAuthenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Detailed authentication check
if ($isAuthenticated) {
    logDiagnostics('Admin Authentication Status', [
        'username' => $_SESSION['admin_username'] ?? 'UNDEFINED',
        'role' => $_SESSION['admin_role'] ?? 'UNDEFINED'
    ]);
} else {
    logDiagnostics('Authentication Failed', [
        'reason' => 'No valid admin session found'
    ]);
}

// Optional: Provide a simple output for direct browser viewing
header('Content-Type: text/plain');
echo "Session Diagnostic Report\n";
echo "Authentication Status: " . ($isAuthenticated ? 'Authenticated' : 'Not Authenticated') . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
