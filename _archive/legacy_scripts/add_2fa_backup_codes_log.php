<?php
/**
 * Migration: Add two_factor_backup_codes_log table
 * Tracks which backup codes have been used by each user (for reuse prevention)
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$sql = "CREATE TABLE IF NOT EXISTS two_factor_backup_codes_log (
    user_id INT UNSIGNED PRIMARY KEY,
    used_codes JSON NOT NULL,
    last_used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_last_used (last_used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($sql);
    echo "[OK] two_factor_backup_codes_log table created/exists\n";

    $count = $db->query("SELECT COUNT(*) FROM two_factor_backup_codes_log")->fetchColumn();
    echo "    Rows: $count\n";
} catch (\Throwable $e) {
    echo "[ERR] " . $e->getMessage() . "\n";
    exit(1);
}?>