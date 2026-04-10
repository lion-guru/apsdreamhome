<?php

/**
 * Migration: Drop Conflicting Associate Tables
 * 
 * This migration drops the associate tables that conflict with the existing MLM system tables.
 */

// Database configuration
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Dropping conflicting associate tables...\n";
    
    // Drop the tables I created (they conflict with existing MLM system)
    $tables = [
        'associate_activities',
        'associate_commissions',
        'associate_properties',
        'associate_leads'
    ];
    
    foreach ($tables as $table) {
        try {
            $conn->exec("DROP TABLE IF EXISTS `$table`");
            echo "✓ Dropped $table\n";
        } catch (PDOException $e) {
            echo "✗ Error dropping $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✓ Conflicting tables dropped successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
