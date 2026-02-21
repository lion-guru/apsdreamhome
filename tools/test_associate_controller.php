<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');

echo "Starting test...\n";

// Mock server vars for CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = 'index.php';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once __DIR__ . '/../config/bootstrap.php';
echo "Bootstrap loaded.\n";

try {
    echo "Checking Associate Model...\n";
    if (class_exists('App\Models\Associate')) {
        echo "Class App\Models\Associate exists.\n";
        echo "Instantiating Associate Model...\n";
        $associate = new \App\Models\Associate();
        echo "Associate Model instantiated.\n";
    } else {
        echo "Class App\Models\Associate NOT found.\n";
    }

    echo "Checking AssociateController...\n";
    $className = 'App\Http\Controllers\AssociateController';
    if (class_exists($className)) {
        echo "Class $className exists.\n";
        echo "Attempting instantiation...\n";
        $controller = new $className();
        echo "Controller instantiated successfully.\n";
    } else {
        echo "Class $className not found.\n";
    }
} catch (Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
