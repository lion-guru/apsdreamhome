<?php
$root = dirname(__DIR__);
if (session_status() === PHP_SESSION_NONE) session_start();
define('APS_ROOT', $root);
define('APS_PUBLIC', $root . '/public');
require_once $root . '/config/bootstrap.php';

$ref = new ReflectionClass('App\Services\AdminMenuService');
echo "hasMethod('applyCustomUserPermissions'): " . var_export($ref->hasMethod('applyCustomUserPermissions'), true) . "\n";
echo "hasMethod('getCustomUserPermissions'): " . var_export($ref->hasMethod('getCustomUserPermissions'), true) . "\n";
echo "hasMethod('getMenuItems'): " . var_export($ref->hasMethod('getMenuItems'), true) . "\n";

// Try calling it
$svc = new \App\Services\AdminMenuService();
$method = new ReflectionMethod($svc, 'applyCustomUserPermissions');
echo "Method visibility: " . $method->getModifiers() . "\n";
echo "Method class: " . $method->getDeclaringClass()->getName() . "\n";

// Try via direct call
$_SESSION['admin_role'] = 'telecaller';
$_SESSION['admin_id'] = 69;

echo "\nDirect call test:\n";
try {
    $result = $svc->getMenuItems('telecaller', 69);
    echo "getMenuItems returned: " . count($result) . " items\n";
} catch (\Throwable $e) {
    echo get_class($e) . ": " . $e->getMessage() . "\n";
    echo "at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
