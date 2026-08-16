<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$_SERVER['REQUEST_URI'] = '/admin/api-keys';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
$_POST = [];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Admin';
$_SESSION['admin_role'] = 'admin';
$_SESSION['csrf_token'] = 'test';

require_once 'app/Http/Controllers/Admin/ApiKeyController.php';

try {
    $controller = new \App\Http\Controllers\Admin\ApiKeyController();
    
    // Mock render
    $controller->render = function($view, $data) {
        echo "View: $view\n";
        return '';
    };
    
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    $method->invoke($controller);
    echo "SUCCESS: ApiKeyController::index() executed without errors\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}