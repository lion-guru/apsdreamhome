<?php
// Test Dashboard Preview
session_start();

// Create test user session
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@apsdreamhome.com';
$_SESSION['user_name'] = 'Test User';
$_SESSION['role'] = 'customer';
$_SESSION['role'] = 'customer';

// Redirect to dashboard
header('Location: dashboard');
exit;
?>
