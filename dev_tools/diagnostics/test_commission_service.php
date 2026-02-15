<?php
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/app/core/App.php';

use App\Services\CommissionService;

try {
    $service = new CommissionService();
    echo "CommissionService loaded successfully!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
