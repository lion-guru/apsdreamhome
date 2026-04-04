<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();

    // 1. Add missing columns to lead_deals table
    echo "=== Checking lead_deals table ===\n";
    $columns = $db->query("SHOW COLUMNS FROM lead_deals")->fetchAll();
    $existingCols = array_column($columns, 'Field');

    $neededCols = [
        'stage' => "ADD COLUMN stage VARCHAR(50) DEFAULT 'lead' AFTER deal_value",
        'assigned_to' => "ADD COLUMN assigned_to INT UNSIGNED NULL AFTER stage",
        'property_id' => "ADD COLUMN property_id INT UNSIGNED NULL AFTER assigned_to",
        'notes' => "ADD COLUMN notes TEXT NULL AFTER property_id",
        'expected_close_date' => "ADD COLUMN expected_close_date DATE NULL AFTER deal_value",
        'actual_close_date' => "ADD COLUMN actual_close_date DATE NULL AFTER expected_close_date"
    ];

    foreach ($neededCols as $colName => $sql) {
        if (!in_array($colName, $existingCols)) {
            echo "Adding column: $colName\n";
            $db->query("ALTER TABLE lead_deals $sql");
        } else {
            echo "Column already exists: $colName\n";
        }
    }

    // 2. Create lead_scoring table if not exists
    echo "\n=== Checking lead_scoring table ===\n";
    $tables = $db->query("SHOW TABLES LIKE 'lead_scoring'")->fetchAll();

    if (empty($tables)) {
        echo "Creating lead_scoring table...\n";
        $db->query("CREATE TABLE IF NOT EXISTS lead_scoring (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_id INT UNSIGNED NOT NULL,
            score INT DEFAULT 0,
            criteria VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lead_id (lead_id),
            INDEX idx_score (score)
        )");
        echo "lead_scoring table created!\n";
    } else {
        echo "lead_scoring table already exists\n";
    }

    echo "\n=== ALL DATABASE FIXES COMPLETED ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
