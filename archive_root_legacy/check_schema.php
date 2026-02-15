<?php
/**
 * Database Schema Verification Script
 * Checks if the database schema matches the expected structure
 */

require_once 'config/bootstrap.php';
require_once 'includes/Database.php';

try {
    $db = new \Database();
    echo "=== Database Schema Check ===\n\n";
    
    // Check tables
    $tables = ['properties', 'property_types', 'users'];
    
    foreach ($tables as $table) {
        echo "Checking table: $table\n";
        
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM $table");
            
            if (empty($columns)) {
                echo "  ❌ Table '$table' does not exist\n";
                continue;
            }
            
            echo "  ✓ Table exists with " . count($columns) . " columns:\n";
            
            foreach ($columns as $column) {
                echo "    - {$column['Field']} ({$column['Type']}) ";
                echo $column['Null'] === 'NO' ? "NOT NULL" : "NULL";
                echo $column['Key'] ? " {$column['Key']}" : "";
                echo $column['Default'] !== null ? " DEFAULT {$column['Default']}" : "";
                echo "\n";
            }
            
            // Check indexes
            $indexes = $db->fetchAll("SHOW INDEXES FROM $table");
            if (!empty($indexes)) {
                echo "  Indexes:\n";
                foreach ($indexes as $index) {
                    echo "    - {$index['Key_name']} on {$index['Column_name']}\n";
                }
            }
            
        } catch (Exception $e) {
            echo "  ❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Check foreign key relationships
    echo "=== Foreign Key Relationships ===\n";
    
    $foreign_keys = $db->fetchAll("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if (!empty($foreign_keys)) {
        foreach ($foreign_keys as $fk) {
            echo "✓ {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key relationships found.\n";
    }
    
    echo "\n=== Sample Data Check ===\n";
    
    // Check sample data
    $sample_checks = [
        'properties' => "SELECT COUNT(*) as count FROM properties",
        'property_types' => "SELECT COUNT(*) as count FROM property_types",
        'users' => "SELECT COUNT(*) as count FROM users"
    ];
    
    foreach ($sample_checks as $table => $query) {
        try {
            $result = $db->fetchOne($query);
            echo "✓ $table: " . $result['count'] . " records\n";
        } catch (Exception $e) {
            echo "❌ $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Schema Validation Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}