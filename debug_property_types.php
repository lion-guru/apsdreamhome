<?php

// Bootstrap the application
require_once __DIR__ . '/config/bootstrap.php';

// Register the autoloader for consolidated models
$autoloader = App\Core\Autoloader::getInstance();
$autoloader->addNamespace('App\Models', APP_ROOT . '/app/Models');
$autoloader->addNamespace('App\Core', APP_ROOT . '/app/Core');

use App\Core\Database;

echo "=== Debug Property Types ===\n\n";

// Get database instance
$db = Database::getInstance();

echo "Checking property_types table structure...\n";
try {
    $result = $db->query("DESCRIBE property_types");
    echo "Property_types table columns:\n";
    foreach ($result as $row) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error describing property_types table: " . $e->getMessage() . "\n";
}

echo "\nChecking sample property types...\n";
try {
    $result = $db->query("SELECT * FROM property_types LIMIT 5");
    echo "Sample property types:\n";
    foreach ($result as $row) {
        echo "- ID: {$row['id']}, Name: {$row['name']}, Slug: {$row['slug']}\n";
    }
} catch (Exception $e) {
    echo "Error getting property types: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";