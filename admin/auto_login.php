<?php
require_once __DIR__ . '/core/init.php';

// Set admin session
$_SESSION['admin_session'] = [
    'is_authenticated' => true,
    'username' => 'techguruabhay@gmail.com',
    'role' => 'admin',
    'last_activity' => time()
];

// Generate CSRF token
generateCSRFToken();

// Regenerate session ID for security
session_regenerate_id(true);

// Log the automatic login
error_log("Auto-login performed for admin user: {$_SESSION['admin_session']['username']}");

// Redirect to new dashboard
header('Location: new_dashboard_v2.php');
exit();
?>
