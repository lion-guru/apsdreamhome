<?php
/**
 * Cron: Backup Verification
 * Tests backup integrity by:
 *   1. Checking latest backup file exists and is non-empty
 *   2. Verifying SQL dump has valid structure (CREATE TABLE, INSERT statements)
 *   3. Checking backup age (warn if > 24 hours old)
 *   4. Optionally: restore to temp DB and verify table counts
 * 
 * Usage: php scripts/cron_backup_verify.php [--restore-test] [--days=1]
 * Schedule: Daily at 3:00 AM via Task Scheduler
 */
require_once __DIR__ . '/../config/bootstrap.php';

$backupDir = __DIR__ . '/../backups/db';
$restoreTest = in_array('--restore-test', $argv);
$maxAgeDays = 1;
foreach ($argv as $arg) {
    if (strpos($arg, '--days=') === 0) $maxAgeDays = (int)substr($arg, 7);
}

echo "=== Backup Verification ===\n";
echo "Backup dir: $backupDir\n";
echo "Max age: {$maxAgeDays} day(s)\n";
echo "Restore test: " . ($restoreTest ? 'YES' : 'NO') . "\n\n";

$errors = [];
$warnings = [];

// 1. Check backup directory exists
if (!is_dir($backupDir)) {
    $errors[] = "Backup directory does not exist: $backupDir";
    echo "ERROR: $errors[0]\n";
    exit(1);
}

// 2. Find latest backup file
$files = glob($backupDir . '/*.sql');
if (empty($files)) {
    $errors[] = "No backup files found in $backupDir";
    echo "ERROR: $errors[0]\n";
    exit(1);
}

usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
$latest = $files[0];
$latestName = basename($latest);
$latestSize = filesize($latest);
$latestAge = time() - filemtime($latest);
$latestAgeHours = round($latestAge / 3600, 1);

echo "Latest backup: $latestName\n";
echo "Size: " . round($latestSize / 1024 / 1024, 2) . " MB\n";
echo "Age: {$latestAgeHours} hours\n";

// 3. Check backup age
if ($latestAge > ($maxAgeDays * 86400)) {
    $warnings[] = "Latest backup is {$latestAgeHours} hours old (max: " . ($maxAgeDays * 24) . " hours)";
    echo "WARNING: $warnings[0]\n";
}

// 4. Check backup size (should be > 1MB for a real database)
if ($latestSize < 1024 * 1024) {
    $errors[] = "Backup file too small ({$latestSize} bytes) - likely corrupt or empty";
    echo "ERROR: $errors[0]\n";
}

// 5. Verify SQL structure
$content = file_get_contents($latest);

// Handle UTF-16LE encoded files (common for mysqldump on Windows)
if (strlen($content) >= 2 && ord($content[0]) === 0xFF && ord($content[1]) === 0xFE) {
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
}

$hasCreateTable = (stripos($content, 'CREATE TABLE') !== false);
$hasInsert = (stripos($content, 'INSERT INTO') !== false);
$hasDropTable = (stripos($content, 'DROP TABLE') !== false);

echo "\nStructure check:\n";
echo "  CREATE TABLE statements: " . ($hasCreateTable ? 'YES' : 'NO') . "\n";
echo "  INSERT statements: " . ($hasInsert ? 'YES' : 'NO') . "\n";
echo "  DROP TABLE statements: " . ($hasDropTable ? 'YES' : 'NO') . "\n";

if (!$hasCreateTable) $errors[] = "No CREATE TABLE statements found";
if (!$hasInsert) $errors[] = "No INSERT statements found";

// 6. Count tables in backup
preg_match_all('/CREATE TABLE.*?`(\w+)`/s', $content, $matches);
$tableCount = count($matches[1]);
echo "  Tables in backup: $tableCount\n";

// 7. Check for critical tables
$criticalTables = ['users', 'plots', 'leads', 'bookings', 'colonies', 'payments'];
$missingTables = [];
foreach ($criticalTables as $ct) {
    if (!in_array($ct, $matches[1])) {
        $missingTables[] = $ct;
    }
}
if (!empty($missingTables)) {
    $errors[] = "Critical tables missing from backup: " . implode(', ', $missingTables);
    echo "  MISSING critical tables: " . implode(', ', $missingTables) . "\n";
} else {
    echo "  All critical tables present: YES\n";
}

// 8. Optional: restore test to temp DB
if ($restoreTest) {
    echo "\n=== Restore Test ===\n";
    $tempDb = 'aps_backup_test_' . date('YmdHis');
    $mysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';
    
    // Create temp DB
    $r = shell_exec("\"$mysql\" -u root -e \"CREATE DATABASE IF NOT EXISTS $tempDb\" 2>&1");
    echo "Created temp DB: $tempDb\n";
    
    // Import backup
    $r = shell_exec("\"$mysql\" -u root $tempDb < \"$latest\" 2>&1");
    echo "Import result: " . (empty($r) ? 'OK' : $r) . "\n";
    
    // Verify table count
    $r = shell_exec("\"$mysql\" -u root -N -D $tempDb -e \"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$tempDb'\"");
    echo "Tables restored: " . trim($r) . "\n";
    
    // Verify critical table row counts
    foreach ($criticalTables as $ct) {
        $r = shell_exec("\"$mysql\" -u root -N -D $tempDb -e \"SELECT COUNT(*) FROM $ct\"");
        echo "  $ct: " . trim($r) . " rows\n";
    }
    
    // Drop temp DB
    shell_exec("\"$mysql\" -u root -e \"DROP DATABASE $tempDb\" 2>&1");
    echo "Temp DB dropped.\n";
}

// Summary
echo "\n=== SUMMARY ===\n";
if (empty($errors) && empty($warnings)) {
    echo "STATUS: HEALTHY - Backup verified OK\n";
    echo "Backup: $latestName (" . round($latestSize / 1024 / 1024, 2) . " MB, $tableCount tables, {$latestAgeHours}h old)\n";
} elseif (empty($errors)) {
    echo "STATUS: WARNING - " . count($warnings) . " warning(s)\n";
    foreach ($warnings as $w) echo "  - $w\n";
} else {
    echo "STATUS: ERROR - " . count($errors) . " error(s)\n";
    foreach ($errors as $e) echo "  - $e\n";
}

// Log result
$logFile = __DIR__ . '/../storage/logs/backup_verify.log';
$logEntry = date('Y-m-d H:i:s') . " | " . (empty($errors) ? 'OK' : 'ERROR') . " | $latestName | " . round($latestSize/1024/1024,2) . "MB | $tableCount tables | {$latestAgeHours}h old\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);
echo "\nLogged to: $logFile\n";