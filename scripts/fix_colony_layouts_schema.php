<?php
/**
 * Migration: Add missing columns to colony_layouts table to match layout-form.php
 * Run: php scripts/fix_colony_layouts_schema.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Fixing colony_layouts table schema...\n\n";

$alterQueries = [
    // Add version column
    "ALTER TABLE `colony_layouts` ADD COLUMN `version` VARCHAR(50) DEFAULT '1.0' AFTER `layout_name`",
    
    // Add layout_type column
    "ALTER TABLE `colony_layouts` ADD COLUMN `layout_type` ENUM('residential','commercial','mixed','industrial') DEFAULT 'residential' AFTER `version`",
    
    // Add road_area_pct column
    "ALTER TABLE `colony_layouts` ADD COLUMN `road_area_pct` DECIMAL(5,2) DEFAULT 15.00 AFTER `layout_type`",
    
    // Add common_area_pct column
    "ALTER TABLE `colony_layouts` ADD COLUMN `common_area_pct` DECIMAL(5,2) DEFAULT 8.00 AFTER `road_area_pct`",
    
    // Add is_current column
    "ALTER TABLE `colony_layouts` ADD COLUMN `is_current` TINYINT(1) DEFAULT 0 AFTER `common_area_pct`",
    
    // Add layout_file_url column
    "ALTER TABLE `colony_layouts` ADD COLUMN `layout_file_url` VARCHAR(500) DEFAULT NULL AFTER `is_current`",
    
    // Add plot_map_json column
    "ALTER TABLE `colony_layouts` ADD COLUMN `plot_map_json` JSON DEFAULT NULL AFTER `layout_file_url`",
    
    // Add notes column
    "ALTER TABLE `colony_layouts` ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `plot_map_json`",
];

foreach ($alterQueries as $query) {
    try {
        $db->query($query);
        echo "✓ Executed: $query\n";
    } catch (\Exception $e) {
        // Check if column already exists
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⊘ Already exists (skipped): " . substr($query, 0, 80) . "...\n";
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nVerifying new schema...\n";
$result = $db->query("DESCRIBE `colony_layouts`");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-25s %-20s %-10s %-10s\n", $row['Field'], $row['Type'], $row['Null'], $row['Default']);
}

echo "\nDone!\n";