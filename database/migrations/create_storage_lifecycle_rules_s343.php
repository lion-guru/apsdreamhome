<?php
/**
 * Migration: Create storage_lifecycle_rules table + seed defaults (Gap 25)
 * Session: 343
 * Idempotent: Safe to re-run, prints SKIP if table/data exists
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SHOW TABLES LIKE 'storage_lifecycle_rules'")->fetchColumn();
if ($exists) {
    echo "SKIP storage_lifecycle_rules - already exists\n";
} else {
    $pdo->exec("CREATE TABLE storage_lifecycle_rules (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        provider VARCHAR(50) DEFAULT 'aws_s3',
        prefix VARCHAR(255) NOT NULL DEFAULT '',
        action ENUM('delete', 'archive', 'warn') NOT NULL DEFAULT 'delete',
        days INT UNSIGNED NOT NULL DEFAULT 90,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        last_run DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant_active (tenant_id, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED storage_lifecycle_rules\n";
}

// Seed default rules
$defaults = [
    ['aws_s3', 'backups/', 'delete', 365],
    ['aws_s3', 'temp/', 'delete', 30],
    ['aws_s3', 'logs/', 'delete', 90],
];
foreach ($defaults as $d) {
    $chk = $pdo->query("SELECT id FROM storage_lifecycle_rules WHERE provider = '{$d[0]}' AND prefix = '{$d[1]}'")->fetchColumn();
    if (!$chk) {
        $pdo->exec("INSERT INTO storage_lifecycle_rules (tenant_id, provider, prefix, action, days, is_active) VALUES (1, '{$d[0]}', '{$d[1]}', '{$d[2]}', {$d[3]}, 1)");
        echo "SEEDED lifecycle rule: {$d[1]} {$d[2]} after {$d[3]}d\n";
    } else {
        echo "SKIP lifecycle rule: {$d[1]} (exists)\n";
    }
}

echo "Done.\n";