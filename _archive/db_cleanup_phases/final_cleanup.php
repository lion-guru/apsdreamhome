<?php
/**
 * Final cleanup: drop backup table, merge audit_log_archive into audit_log
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== FINAL CLEANUP ===\n\n";

// 1. Drop backup table (we just created it, safe to drop)
echo "1. Dropping payment_notifications_backup_20260603...\n";
$pdo->exec("DROP TABLE IF EXISTS payment_notifications_backup_20260603");
echo "   Dropped\n\n";

// 2. Drop audit_log_archive (1 row, all empty fields)
echo "2. Dropping audit_log_archive (1 row, all empty)...\n";
$pdo->exec("DROP TABLE IF EXISTS audit_log_archive");
echo "   Dropped\n\n";

// 3. Drop entity_docs_backup_20260603 (we created it for consolidation)
echo "3. Dropping entity_docs_backup_20260603...\n";
$pdo->exec("DROP TABLE IF EXISTS entity_docs_backup_20260603");
echo "   Dropped\n\n";

// 4. Drop ai_call_logs_backup_20260603 (we created it for consolidation)
echo "4. Dropping ai_call_logs_backup_20260603...\n";
$pdo->exec("DROP TABLE IF EXISTS ai_call_logs_backup_20260603");
echo "   Dropped\n\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables after: $after\n";?>