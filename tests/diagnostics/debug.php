<?php
/**
 * Debug Script - Check for redirect issues
 * Run this to diagnose redirect loop problems
 */

echo "<h1>Debug Information</h1>";

// Check session
echo "<h2>Session Information</h2>";
session_start();
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Admin Logged In: " . (isset($_SESSION['admin_logged_in']) ? $_SESSION['admin_logged_in'] : 'false') . "</p>";
echo "<p>Admin Role: " . ($_SESSION['admin_role'] ?? 'not set') . "</p>";
echo "<p>Admin Username: " . ($_SESSION['admin_username'] ?? 'not set') . "</p>";

// Check server variables
echo "<h2>Server Information</h2>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Check file paths
echo "<h2>File Path Information</h2>";
echo "<p>Current File: " . __FILE__ . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Config File Exists: " . (file_exists(__DIR__ . '/config.php') ? 'Yes' : 'No') . "</p>";

// Check constants
echo "<h2>Constants</h2>";
echo "<p>BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</p>";
echo "<p>ADMIN_BASE_URL: " . (defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL : 'NOT DEFINED') . "</p>";

// Check redirects
echo "<h2>Redirect Test</h2>";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $admin_role = $_SESSION['admin_role'] ?? 'admin';
    $dashboard_map = [
        'admin' => 'admin_dashboard.php',
        'enhanced' => 'enhanced_dashboard.php',
        'superadmin' => 'superadmin_dashboard.php'
    ];
    $redirect_dashboard = $dashboard_map[$admin_role] ?? 'enhanced_dashboard.php';
    echo "<p>Would redirect to: " . BASE_URL . 'admin/' . $redirect_dashboard . "</p>";
}

echo "<p><a href='enhanced_dashboard.php'>Test Enhanced Dashboard</a></p>";
echo "<p><a href='admin_dashboard.php'>Test Admin Dashboard</a></p>";
echo "<p><a href='index.php'>Go to Login</a></p>";
?>
