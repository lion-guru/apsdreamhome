<?php
/**
 * InnoDB Tablespace Import for Restored Databases
 * For each table: DISCARD TABLESPACE → copy .ibd from backup → IMPORT TABLESPACE
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$backupDir = 'C:\\xampp\\mysql\\data_backup_20260915';
$dataDir = 'C:\\xampp\\mysql\\data';

$databases = ['school_management', 'schoolhub_demo', 'schoolhub_greenvalley', 'apsdreamhome_test'];

foreach ($databases as $dbName) {
    echo "\n=== Processing: $dbName ===\n";
    $pdo->exec("USE `$dbName`");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $success = 0;
    $failed = 0;
    $skipped = 0;
    
    foreach ($tables as $table) {
        $ibdBackup = "$backupDir\\$dbName\\$table.ibd";
        $ibdLive = "$dataDir\\$dbName\\$table.ibd";
        
        if (!file_exists($ibdBackup)) {
            // No .ibd in backup — likely a view or empty table
            $skipped++;
            continue;
        }
        
        try {
            // Step 1: Discard the empty tablespace
            $pdo->exec("ALTER TABLE `$table` DISCARD TABLESPACE");
            
            // Step 2: Copy the .ibd file from backup
            copy($ibdBackup, $ibdLive);
            
            // Step 3: Import the tablespace
            $pdo->exec("ALTER TABLE `$table` IMPORT TABLESPACE");
            
            $success++;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // If table is already readable or has issues, just log it
            if (strpos($msg, 'already exists') !== false) {
                $skipped++;
            } else {
                echo "  ❌ $table: $msg\n";
                $failed++;
            }
        }
    }
    
    echo "  Result: ✅ $success imported, ⚠ $skipped skipped, ❌ $failed failed\n";
    
    // Verify: try to read a few tables
    $testTables = array_slice($tables, 0, 3);
    foreach ($testTables as $tt) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$tt`")->fetchColumn();
            echo "  📊 $tt: $count rows (readable)\n";
        } catch (Exception $e) {
            echo "  ⚠ $tt: still not readable\n";
        }
    }
}

echo "\n=== DONE ===\n";
