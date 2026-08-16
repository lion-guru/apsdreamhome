<?php
// Debug the BASE_URL issue in the actual request flow
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$_SERVER['REQUEST_URI'] = '/admin/visits';
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

// Check constants before controller
echo "Before controller:\n";
echo "BASE_URL defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
if (defined('BASE_URL')) echo "BASE_URL = " . BASE_URL . "\n";

require_once 'app/Http/Controllers/Admin/VisitController.php';

try {
    $controller = new \App\Http\Controllers\Admin\VisitController();
    
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    // Mock render to capture the view inclusion
    $originalRender = [$controller, 'render'];
    $controller->render = function($view, $data) {
        echo "\n--- RENDER CALLED ---\n";
        echo "View: $view\n";
        echo "BASE_URL defined in render: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
        if (defined('BASE_URL')) echo "BASE_URL = " . BASE_URL . "\n";
        
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            echo "Including view: $viewPath\n";
            echo "BASE_URL defined before include: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
            include $viewPath;
            echo "View included successfully\n";
        } else {
            echo "View not found: $viewPath\n";
        }
        echo "--- END RENDER ---\n";
        return '';
    };
    
    $method->invoke($controller);
    echo "\nSUCCESS: VisitController::index() executed without errors\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}