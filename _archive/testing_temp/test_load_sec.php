<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';

echo "Bootstrap loaded.\n";

try {
    $exists = class_exists('App\Services\SecurityService');
    echo "Class exists: " . ($exists ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
