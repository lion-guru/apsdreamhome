<?php
/**
 * Check Database Structure
 * This script shows all tables and their structure in the database
 */

// Include database configuration
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connection.php';

echo "🔍 Checking Database Structure...\n\n";

try {
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "❌ No tables found in the database.\n";
        exit;
    }
    
    echo "📊 Found " . count($tables) . " tables in database: " . DB_NAME . "\n\n";
    
    // Display each table structure
    foreach ($tables as $table) {
        echo "📋 Table: $table\n";
        echo str_repeat("-", 80) . "\n";
        
        // Get table structure
        $columns = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        // Display column information
        echo str_pad("Column", 30) . str_pad("Type", 20) . str_pad("Null", 8) . str_pad("Key", 8) . "Default\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($columns as $column) {
            echo str_pad($column['Field'], 30) . 
                 str_pad($column['Type'], 20) . 
                 str_pad($column['Null'], 8) . 
                 str_pad($column['Key'], 8) . 
                 $column['Default'] . "\n";
        }
        
        // Get row count
        $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
        echo "\n📊 Total Rows: $count\n";
        
        // Show first 3 rows as sample
        if ($count > 0) {
            echo "\nSample Data (first 3 rows):\n";
            $sample = $pdo->query("SELECT * FROM `$table` LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($sample)) {
                // Get column headers
                $headers = array_keys($sample[0]);
                
                // Print headers
                foreach ($headers as $header) {
                    echo str_pad(substr($header, 0, 15), 16);
                }
                echo "\n" . str_repeat("-", count($headers) * 16) . "\n";
                
                // Print rows
                foreach ($sample as $row) {
                    foreach ($row as $value) {
                        echo str_pad(substr($value ?? 'NULL', 0, 15), 16);
                    }
                    echo "\n";
                }
            }
        }
        
        echo "\n" . str_repeat("=", 80) . "\n\n";
    }
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

echo "✅ Database check completed!\n";
