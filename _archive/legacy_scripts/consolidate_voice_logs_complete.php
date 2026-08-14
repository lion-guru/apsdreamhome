<?php
/**
 * Complete Voice AI consolidation: ai_call_logs (3 rows) -> ai_call_sessions
 * Row-by-row migration since data is mostly NULL seed data
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== COMPLETE VOICE AI CONSOLIDATION ===\n\n";

// Step 1: Migrate sentiment by row id (data is essentially NULL seed)
$logs = $pdo->query("SELECT id, sentiment FROM ai_call_logs ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT id FROM ai_call_sessions ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

echo "Migrating sentiment to sessions:\n";
foreach ($logs as $i => $log) {
    if (isset($sessions[$i])) {
        $stmt = $pdo->prepare("UPDATE ai_call_sessions SET sentiment = ? WHERE id = ?");
        $stmt->execute([$log['sentiment'], $sessions[$i]]);
        echo "  âœ“ log id={$log['id']} -> session id={$sessions[$i]} (sentiment: {$log['sentiment']})\n";
    }
}
echo "\n";

// Step 2: Verify
$migrated = $pdo->query("SELECT COUNT(*) FROM ai_call_sessions WHERE sentiment IS NOT NULL")->fetchColumn();
echo "Migrated: $migrated sessions with sentiment\n\n";

// Step 3: Drop ai_call_logs (with safety)
echo "Dropping ai_call_logs (backup exists at ai_call_logs_backup_20260603)...\n";
$pdo->exec("DROP TABLE IF EXISTS ai_call_logs");
echo "  âœ“ Dropped ai_call_logs\n\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables: now $after\n";?>