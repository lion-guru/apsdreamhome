<?php
// Debug the BASE_URL issue
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

// Simulate the exact environment
$_SERVER['REQUEST_URI'] = '/admin/visits';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
$_POST = [];

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';
$_SESSION['admin_role'] = 'admin';
$_SESSION['csrf_token'] = 'test';

// Check if BASE_URL is defined before including view
echo "Before including view:\n";
echo "BASE_URL defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
if (defined('BASE_URL')) {
    echo "BASE_URL = " . BASE_URL . "\n";
}

// Now include the view directly
echo "\nIncluding view...\n";
$viewPath = 'C:\xampp\htdocs\apsdreamhome\app\views\admin\visits\index.php';
$page_title = 'Test';
$stats = ['total' => 6, 'today' => 0, 'upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
$visits = [];
$slots = [];

try {
    include 'C:\xampp\htdocs\apsdreamhome\app\views\admin\visits\index.php';
    echo "\nView included successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}