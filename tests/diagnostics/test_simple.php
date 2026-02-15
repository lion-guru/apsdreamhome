<?php
// Simple test script for get_lead_details.php

// Include necessary files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/Database.php';

// Set up server variables
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/api/test_simple.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Set up GET parameters
$_GET = [
    'action' => 'get',
    'id' => 1,
    'include' => 'all'
];

// Also set the global $argv for CLI mode
$GLOBALS['argv'] = [
    'test_simple.php',
    '--id=1'
];
$GLOBALS['argc'] = count($GLOBALS['argv']);

// Start session
session_start();

// Set test user session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_email'] = 'superadmin@dreamhome.com';
$_SESSION['user_name'] = 'Super Admin';
$_SESSION['type'] = 'admin';

// Include the file
ob_start();
include __DIR__ . '/get_lead_details.php';
$output = ob_get_clean();

// Output the result
echo "=== RAW OUTPUT ===\n";
echo $output;

// Try to decode and pretty print JSON
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\n=== PRETTY PRINTED JSON ===\n";
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "\n=== NOT VALID JSON ===\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
}
