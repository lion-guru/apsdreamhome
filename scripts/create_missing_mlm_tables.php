<?php
/**
 * Create missing MLM tables required by HybridCommissionEngine
 * Run once: php scripts/create_missing_mlm_tables.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$tables = [
    // MLM salary grants
    "CREATE TABLE IF NOT EXISTS mlm_salary_grants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tier_index INT NOT NULL DEFAULT 0,
        volume_threshold DECIMAL(12,2) NOT NULL DEFAULT 0,
        monthly_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        months_total INT NOT NULL DEFAULT 0,
        months_paid INT NOT NULL DEFAULT 0,
        status ENUM('active','completed','cancelled') DEFAULT 'active',
        activated_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $sql) {
    try {
        $db->query($sql);
        if (preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', $sql, $m)) {
            echo "Created: {$m[1]}\n";
            $created++;
        }
    } catch (\Throwable $e) {
        if (preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', $sql, $m)) {
            echo "ERROR creating {$m[1]}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nTotal tables created: $created\n";
