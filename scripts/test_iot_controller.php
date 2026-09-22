<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Http\Controllers\Admin\IoTController;

$controller = new IoTController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('index');
$method->setAccessible(true);

// We need to mock the session and request
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'super_admin';

try {
    $result = $method->invoke($controller);
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}