<?php
define('APP_ROOT', dirname(__DIR__, 2));
define('BASE_PATH', dirname(__DIR__, 2));

require_once APP_ROOT . '/app/core/App.php';

use App\Http\Controllers\Analytics\AdminReportsController;

try {
    $controller = new AdminReportsController();
    echo "AdminReportsController loaded and instantiated successfully.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
