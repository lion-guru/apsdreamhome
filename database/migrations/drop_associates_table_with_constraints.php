<?php

/**
 * Migration: Drop Associates Table with Constraints
 * 
 * This migration drops the associates table and removes any foreign key constraints first
 */

$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking for foreign key constraints on associates table...\n";
    
    // Find all foreign key constraints referencing associates table
    $stmt = $conn->query("SELECT TABLE_NAME, CONSTRAINT_NAME 
                          FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE REFERENCED_TABLE_NAME = 'associates' 
                          AND TABLE_SCHEMA = 'apsdreamhome'");
    
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($constraints)) {
        echo "Found " . count($constraints) . " foreign key constraint(s):\n";
        foreach ($constraints as $constraint) {
            echo "  - {$constraint['TABLE_NAME']}.{$constraint['CONSTRAINT_NAME']}\n";
        }
        
        echo "\nDropping foreign key constraints...\n";
        foreach ($constraints as $constraint) {
            $tableName = $constraint['TABLE_NAME'];
            $constraintName = $constraint['CONSTRAINT_NAME'];
            
            $conn->exec("ALTER TABLE `$tableName` DROP FOREIGN KEY `$constraintName`");
            echo "✓ Dropped constraint $constraintName from $tableName\n";
        }
    } else {
        echo "No foreign key constraints found.\n";
    }
    
    echo "\nDropping associates table...\n";
    $conn->exec("DROP TABLE IF EXISTS `associates`");
    echo "✓ Dropped associates table\n";
    
    echo "\n✓ Associates table and its constraints dropped successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
