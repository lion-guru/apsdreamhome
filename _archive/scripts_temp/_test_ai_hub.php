<?php
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['admin_role'] = 'admin';
$_SESSION['admin_name'] = 'Test Admin';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';

// Try to render AI hub
try {
    $controller = new \App\Http\Controllers\Admin\AiController();
    ob_start();
    $controller->hub();
    $output = ob_get_clean();
    echo "HTTP 200 - Page rendered successfully (" . strlen($output) . " bytes)\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}