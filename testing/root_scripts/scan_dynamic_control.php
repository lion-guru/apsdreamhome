<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== SCANNING DATABASE FOR DYNAMIC CONTROL TABLES ===\n\n";
    
    // Get all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total tables: " . count($tables) . "\n\n";
    
    // Look for settings/config related tables
    $settingsTables = [];
    foreach ($tables as $table) {
        if (stripos($table, 'setting') !== false || 
            stripos($table, 'config') !== false || 
            stripos($table, 'option') !== false ||
            stripos($table, 'meta') !== false ||
            stripos($table, 'content') !== false) {
            $settingsTables[] = $table;
        }
    }
    
    echo "Potential Dynamic Control Tables:\n";
    echo "---------------------------------\n";
    foreach ($settingsTables as $table) {
        echo "\n📋 Table: $table\n";
        
        // Get columns
        $columns = $db->query("SHOW COLUMNS FROM $table")->fetchAll();
        echo "   Columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
        
        // Get row count
        $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch();
        echo "   Rows: " . $count['cnt'] . "\n";
        
        // Show sample data if exists
        if ($count['cnt'] > 0) {
            $sample = $db->query("SELECT * FROM $table LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
            echo "   Sample Data:\n";
            foreach ($sample as $row) {
                echo "     - " . json_encode($row) . "\n";
            }
        }
    }
    
    // Check for admin control specific tables
    echo "\n\n=== ADMIN CONTROL TABLES ===\n";
    $adminTables = [];
    foreach ($tables as $table) {
        if (stripos($table, 'admin') !== false || 
            stripos($table, 'user') !== false || 
            stripos($table, 'page') !== false ||
            stripos($table, 'cms') !== false) {
            $adminTables[] = $table;
        }
    }
    
    foreach ($adminTables as $table) {
        echo "📋 $table\n";
        $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch();
        echo "   Rows: " . $count['cnt'] . "\n";
    }
    
    echo "\n=== SCAN COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
