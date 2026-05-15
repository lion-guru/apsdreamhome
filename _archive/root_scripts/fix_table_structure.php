<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== CHECKING TABLE STRUCTURE ===\n";
    $columns = $db->query("SHOW COLUMNS FROM site_settings")->fetchAll();
    
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Add setting_value if missing
    $colNames = array_column($columns, 'Field');
    
    if (!in_array('setting_value', $colNames)) {
        echo "\nAdding setting_value column...\n";
        $db->query("ALTER TABLE site_settings ADD COLUMN setting_value TEXT AFTER setting_key");
        echo "✅ Added setting_value\n";
    }
    
    if (!in_array('category', $colNames)) {
        echo "\nAdding category column...\n";
        $db->query("ALTER TABLE site_settings ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER setting_value");
        echo "✅ Added category\n";
    }
    
    if (!in_array('updated_at', $colNames)) {
        echo "\nAdding updated_at column...\n";
        $db->query("ALTER TABLE site_settings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✅ Added updated_at\n";
    }
    
    echo "\n✅ Table structure fixed!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
