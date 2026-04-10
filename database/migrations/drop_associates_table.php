<?php

/**
 * Migration: Drop Associates Table
 * 
 * This migration drops the associates table since users table already handles all user types
 */

$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Dropping associates table...\n";
    
    $conn->exec("DROP TABLE IF EXISTS `associates`");
    echo "✓ Dropped associates table\n";
    
    echo "\n✓ Associates table dropped successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
