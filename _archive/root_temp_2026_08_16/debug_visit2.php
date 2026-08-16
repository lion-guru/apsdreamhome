<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

// Simulate a request to /admin/visits
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

require_once 'app/Http/Controllers/Admin/VisitController.php';

try {
    $controller = new \App\Http\Controllers\Admin\VisitController();
    
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    // Mock render to capture output
    $controller->render = function($view, $data) {
        echo "View: $view\n";
        return '';
    };
    
    $method->invoke($controller);
    echo "SUCCESS: VisitController::index() executed without errors\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}