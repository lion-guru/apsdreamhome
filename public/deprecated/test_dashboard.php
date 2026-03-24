<?php
// Test Dashboard Preview
session_start();

// Create test user session
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@apsdreamhome.com';
$_SESSION['user_name'] = 'Test User';
$_SESSION['user_role'] = 'customer';
$_SESSION['user_type'] = 'customer';

// Redirect to dashboard
header('Location: dashboard');
exit;
?>
