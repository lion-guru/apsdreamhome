<?php
/**
 * Track a migration: mark a script as applied
 * Usage: php scripts/track_migration.php <name> <category> [description] [rows_affected]
 */
if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

if ($argc < 3) {
    die("Usage: php track_migration.php <name> <category> [description] [rows_affected]\n");
}

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$name = $argv[1];
$category = $argv[2];
$description = $argv[3] ?? '';
$rows = isset($argv[4]) ? (int)$argv[4] : null;

$start = microtime(true);
$sql = "INSERT INTO _migrations (script_name, category, description, rows_affected, execution_time_ms) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE applied_at = NOW(), rows_affected = VALUES(rows_affected)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $category, $description, $rows, 0]);
$elapsed = (int)((microtime(true) - $start) * 1000);

echo "âœ“ Tracked: $name (category: $category" . ($rows ? ", rows: $rows" : "") . ")\n";?>