<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "=== Checking service_interests table ===\n\n";

try {
    $result = $db->fetchAll("DESCRIBE service_interests");
    echo "Columns:\n";
    foreach ($result as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n\nChecking leads table...\n";
    $leads = $db->fetchAll("DESCRIBE leads");
    echo "Leads columns:\n";
    foreach ($leads as $col) {
        echo "  - {$col['Field']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}