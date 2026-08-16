<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';
require_once 'app/Http/Controllers/Admin/VisitController.php';

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

// Mock the required services
require_once 'app/Services/VisitService.php';

try {
    $controller = new \App\Http\Controllers\Admin\VisitController();
    
    // Set up required properties
    $reflection = new ReflectionClass($controller);
    $layoutProp = $reflection->getProperty('layout');
    $layoutProp->setAccessible(true);
    $layoutProp->setValue($controller, 'layouts/admin');
    
    // Try to call index method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    // Mock the render method
    $renderMethod = $reflection->getMethod('render');
    $renderMethod->setAccessible(true);
    
    // Mock the render to avoid actual rendering
    $controller->render = function($view, $data) {
        echo "Render called for: $view\n";
        print_r($data);
        return '';
    };
    
    $method->invoke($controller);
    echo "VisitController::index() executed successfully\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}