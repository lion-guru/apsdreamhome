<?php

// Bootstrap the application
require_once __DIR__ . '/config/bootstrap.php';

// Register the autoloader for consolidated models
$autoloader = App\Core\Autoloader::getInstance();
$autoloader->addNamespace('App\Models', APP_ROOT . '/app/Models');
$autoloader->addNamespace('App\Core', APP_ROOT . '/app/Core');

use App\Core\Database;

echo "=== Debug Database Structure ===\n\n";

// Get database instance
$db = Database::getInstance();

echo "Checking properties table structure...\n";
try {
    $result = $db->query("DESCRIBE properties");
    echo "Properties table columns:\n";
    foreach ($result as $row) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error describing properties table: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";