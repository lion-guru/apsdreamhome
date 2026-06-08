<?php
// Direct test of CFO controller
require_once 'C:\xampp\htdocs\apsdreamhome\app\Core\Database.php';
\App\Core\Database\Database::getInstance();

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['admin_id'] = 1;
$_SESSION['admin_role'] = 'super_admin';
$_SESSION['admin_email'] = 'admin@apsdreamhome.com';
$_SESSION['admin_name'] = 'Admin';

require_once 'C:\xampp\htdocs\apsdreamhome\app\Http\Controllers\Admin\CFODashboardController.php';

$controller = new \App\Http\Controllers\Admin\CFODashboardController();
echo "Controller instantiated\n";

// Mock the render method to capture output
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('index');
$method->setAccessible(true);

echo "Calling index()...\n";
try {
    $method->invoke($controller);
    echo "Method completed\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
