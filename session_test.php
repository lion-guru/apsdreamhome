<?php
// Start session with the same settings as customer_dashboard.php
$session_name = 'APS_DREAM_HOME_SESSID';
session_name($session_name);

// Set session cookie parameters
$lifetime = 86400; // 24 hours
$path = '/';
$domain = $_SERVER['HTTP_HOST'];
$secure = isset($_SERVER['HTTPS']);
$httponly = true;
session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

// Start the session
session_start();

// Set a test session variable if not set
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
} else {
    $_SESSION['test_counter']++;
}

// Debug output
header('Content-Type: text/plain');
echo "=== SESSION TEST PAGE ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";
echo "Cookie Data: " . print_r($_COOKIE, true) . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Status: " . session_status() . " (2 = PHP_SESSION_ACTIVE)\n";
echo "Test Counter: " . $_SESSION['test_counter'] . " (should increment on each refresh)\n";

// Check if we can read/write to session
if ($_SESSION['test_counter'] > 1) {
    echo "\n✅ SESSION IS WORKING! The counter is incrementing correctly.";
} else {
    echo "\n❌ SESSION IS NOT WORKING! The counter is not incrementing.";
}

// Add a link to go to customer dashboard
echo "\n\n<a href='customer_dashboard.php'>Go to Customer Dashboard</a>";
?>
