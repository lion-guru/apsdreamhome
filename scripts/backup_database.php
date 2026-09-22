<?php
/**
 * Database Backup Script
 * Creates a full SQL dump of the apsdreamhome database
 */

$host = 'localhost';
$port = '3306';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDir . "/apsdreamhome_backup_{$timestamp}.sql";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database. Starting backup...\n";
    echo "Backup file: $backupFile\n\n";
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables\n";
    
    $sql = "-- APS Dream Home Database Backup\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Database: $dbname\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    foreach ($tables as $table) {
        echo "Backing up table: $table... ";
        
        // Table structure
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createTable['Create Table'] . ";\n\n";
        
        // Table data
        $rows = $conn->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $escaped = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $escaped[] = 'NULL';
                    } else {
                        $escaped[] = "'" . $conn->quote($val) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $escaped) . ')';
            }
            $sql .= implode(",\n", $values) . ";\n\n";
        }
        
        echo "done (" . count($rows) . " rows)\n";
    }
    
    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    
    // Write backup file
    file_put_contents($backupFile, $sql);
    
    $size = filesize($backupFile);
    $sizeMB = round($size / 1024 / 1024, 2);
    
    echo "\n✓ Backup completed successfully!\n";
    echo "File: $backupFile\n";
    echo "Size: {$sizeMB} MB\n";
    
    // Also create a compressed version
    $gzFile = $backupFile . '.gz';
    $gz = gzopen($gzFile, 'wb9');
    gzwrite($gz, $sql);
    gzclose($gz);
    
    $gzSize = filesize($gzFile);
    $gzSizeMB = round($gzSize / 1024 / 1024, 2);
    echo "Compressed: {$gzSizeMB} MB ($gzFile)\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}