<?php
/**
 * Migration S458: Create mlm_salary_grant_logs table
 * 
 * Creates the audit log table for salary grant actions (activated, paused, resumed, paid).
 * This table was previously created inline in SalaryIncentiveService::activateSalaryGrant()
 * but should be a proper migration for consistency and version control.
 * 
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S458: Create mlm_salary_grant_logs table ===\n";

// Check if table already exists
$tableExists = (bool)$pdo->query(
    "SELECT COUNT(*) FROM information_schema.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'mlm_salary_grant_logs'"
)->fetchColumn();

if ($tableExists) {
    echo "  [*] Table mlm_salary_grant_logs already exists (skipped)\n";
    echo "=== MIGRATION S458 COMPLETE: Skipped (table exists) ===\n";
    exit(0);
}

try {
    $pdo->exec("
    CREATE TABLE `mlm_salary_grant_logs` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `user_id` BIGINT UNSIGNED NOT NULL,
        `grant_id` INT UNSIGNED NOT NULL,
        `action` VARCHAR(30) NOT NULL DEFAULT 'activated',
        `details` TEXT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_grant` (`grant_id`),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_action` (`action`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    echo "  [+] Table mlm_salary_grant_logs created successfully\n";
    echo "=== MIGRATION S458 COMPLETE: Success ===\n";

} catch (\Throwable $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
