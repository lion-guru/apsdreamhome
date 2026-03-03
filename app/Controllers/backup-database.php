<?php

/**
 * APS Dream Home - Backup Database Script
 * Creates backup of all database tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $backupFile = 'database-backup-' . date('Y-m-d-H-i-s') . '.sql';
    $handle = fopen($backupFile, 'w');
    
    fwrite($handle, "-- APS Dream Home Database Backup\n");
    fwrite($handle, "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n");
    
    foreach ($tables as $table) {
        // Get table structure
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        fwrite($handle, $result['Create Table'] . ";\n\n");
        
        // Get table data
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            fwrite($handle, "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n");
            
            foreach ($rows as $i => $row) {
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return 'NULL';
                    if ($value === '') return "''";
                    return $pdo->quote($value);
                }, $row);
                
                fwrite($handle, "(" . implode(', ', $values) . ")");
                if ($i < count($rows) - 1) {
                    fwrite($handle, ",\n");
                } else {
                    fwrite($handle, ";\n\n");
                }
            }
        }
    }
    
    fclose($handle);
    echo "Database backup created: $backupFile\n";
    
} catch(PDOException $e) {
    echo "Backup failed: " . $e->getMessage() . "\n";
}
?>
