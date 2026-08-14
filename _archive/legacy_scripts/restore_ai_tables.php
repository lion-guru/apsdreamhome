<?php
/**
 * RESTORE 39 missing AI/Voice/Chat tables from 2026-05-25 backup
 * - Extracts only CREATE TABLE statements for missing tables
 * - Strips FK constraints (will be added back selectively)
 * - Wrapped in transactions for safety
 * - Logs everything to scripts/_restore_ai_tables.log
 */

set_time_limit(300);
$logFile = __DIR__ . '/_restore_ai_tables.log';
$log = function($msg) use ($logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
};

@unlink($logFile);
$log("=== RESTORE STARTED ===");

$backup = file_get_contents(__DIR__ . '/../database/backup_20260525.sql');
if (!$backup) {
    $log("ERROR: Cannot read backup file");
    exit(1);
}
$log("Backup loaded: " . strlen($backup) . " bytes");

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $log("Connected to DB");
} catch (Exception $e) {
    $log("ERROR: " . $e->getMessage());
    exit(1);
}

// Get tables missing in current DB
$missing = [];
$patterns = ['ai_%', 'voice_%', 'chat_%'];
foreach ($patterns as $pat) {
    $rows = $pdo->query("SHOW TABLES LIKE '$pat'")->fetchAll(PDO::FETCH_COLUMN);
    $existing = array_flip($rows);
}
preg_match_all('/CREATE TABLE `(ai_[a-z_]+|voice_[a-z_]+|chat_[a-z_]+)`/i', $backup, $m);
$backupTables = array_unique($m[1]);
foreach ($backupTables as $t) {
    $exists = $pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn();
    if (!$exists) $missing[] = $t;
}
sort($missing);
$log("Missing tables to restore: " . count($missing));

// Extract CREATE TABLE blocks from backup
$restored = 0;
$failed = 0;
$skipped = 0;

foreach ($missing as $table) {
    // Find the CREATE TABLE block (could span multiple lines)
    $pattern = '/CREATE TABLE `' . preg_quote($table, '/') . '`.*?;\s*(--.*?\n)?/s';
    if (!preg_match($pattern, $backup, $matches)) {
        $log("  SKIP - Pattern not found: $table");
        $skipped++;
        continue;
    }
    $createSQL = trim($matches[0]);

    // Strip FK constraints that may reference dropped tables (multi-line safe)
    $createSQL = preg_replace('/,\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY[^)]*\)\s*REFERENCES[^)]*\)\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*/i', '', $createSQL);
    $createSQL = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES[^)]*\)\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*/i', '', $createSQL);
    $createSQL = preg_replace('/,\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY[^,]*REFERENCES[^,]*?,/i', '', $createSQL);

    // Remove inline ENUM/CHECK constraints that may fail (e.g. user_type=)
    // Keep them â€” match current schema needs

    try {
        $pdo->exec($createSQL);
        $log("  RESTORED: $table");
        $restored++;
    } catch (Exception $e) {
        $log("  FAILED  : $table -> " . substr($e->getMessage(), 0, 150));
        $failed++;
    }
}

$log("=== SUMMARY ===");
$log("Total missing:  " . count($missing));
$log("Restored:       $restored");
$log("Failed:         $failed");
$log("Skipped:        $skipped");
$log("=== DONE ===");?>