<?php
/**
 * Ensure service_config_audit table exists (mirrors commission_plan_audit).
 * Idempotent. Run: php scripts/ensure_service_config_audit.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = App\Core\Database\Database::getInstance()->getConnection();
    $db->exec("
    CREATE TABLE IF NOT EXISTS `service_config_audit` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `service_name` VARCHAR(100) NOT NULL,
        `config_key` VARCHAR(100) NOT NULL,
        `old_value` TEXT NULL,
        `new_value` TEXT NULL,
        `changed_by` BIGINT UNSIGNED NULL,
        `changed_by_name` VARCHAR(100) NULL,
        `ip_address` VARCHAR(45) NULL,
        `notes` VARCHAR(255) NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_service_key` (`service_name`, `config_key`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "service_config_audit ready\nOK\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
