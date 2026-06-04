<?php
/**
 * Migration: Add two_factor_backup_codes_log table
 * Tracks which backup codes have been used by each user (for reuse prevention)
 */
require_once __DIR__ . '/../../app/Core/Database.php';
require_once __DIR__ . '/../../database/connection.php';

$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();

$sql = "CREATE TABLE IF NOT EXISTS two_factor_backup_codes_log (
    user_id INT UNSIGNED PRIMARY KEY,
    used_codes JSON NOT NULL,
    last_used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_last_used (last_used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "[OK] two_factor_backup_codes_log table created/exists\n";

    $count = $pdo->query("SELECT COUNT(*) FROM two_factor_backup_codes_log")->fetchColumn();
    echo "    Rows: $count\n";
} catch (\Throwable $e) {
    echo "[ERR] " . $e->getMessage() . "\n";
    exit(1);
}
