<?php
require_once __DIR__ . '/../config/bootstrap.php';
$service = new App\Services\RetroactiveRecalculationService();
try {
    $result = $service->getRequests('', 1, 20);
    print_r($result);
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . PHP_EOL;
    echo 'Line: ' . $e->getLine() . PHP_EOL;
}