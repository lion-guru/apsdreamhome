<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/app/core/App.php';

try {
    echo "Bootstrapping app...\n";
    $app = \App\Core\App::getInstance();
    echo "App bootstrapped successfully.\n";

    echo "Testing database connection...\n";
    $db = \App\Core\App::database();
    if ($db) {
        echo "Database connection successful.\n";
    } else {
        echo "Database connection failed.\n";
    }
} catch (Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
