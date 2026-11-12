<?php
/**
 * Migration: Create mlm_salary_grants table
 *
 * NOTE (S467): this migration used to run
 *
 *     DROP TABLE IF EXISTS `mlm_salary_grants`;
 *     CREATE TABLE `mlm_salary_grants` (... the OLD shape ...);
 *
 * which is destructive in two ways:
 *   1. it DROPs a table that holds LIVE salary grants (and the FK to
 *      `users` makes the DROP fail with errno 150 anyway, while the
 *      CREATE that follows would still have replaced the table's shape);
 *   2. it recreates the table with a LEGACY shape - without
 *      maintenance_mode, consecutive_inactive_months, paused_reason,
 *      last_activity_verified_at or tenant_id - so running it on a database
 *      that already has the table SILENTLY DOWNGRADES it.  That is exactly
 *      what happened in the CI job: the schema-scan gate reported
 *      "COLUMN mlm_salary_grants.tenant_id" as a NEW mismatch and failed.
 *
 * The migration is now additive and idempotent: CREATE TABLE IF NOT EXISTS
 * with the CURRENT shape, then ADD COLUMN IF NOT EXISTS for anything an
 * older table might still be missing.
 */
$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `mlm_salary_grants` (
            `id`                        INT AUTO_INCREMENT PRIMARY KEY,
            `tenant_id`                 INT UNSIGNED NOT NULL DEFAULT 1,
            `user_id`                   BIGINT(20) UNSIGNED NOT NULL,
            `tier_index`                INT NOT NULL,
            `volume_threshold`          DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            `monthly_amount`            DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            `months_total`              INT NOT NULL DEFAULT 0,
            `months_paid`               INT DEFAULT 0,
            `status`                    ENUM('active','completed','cancelled','paused') DEFAULT 'active',
            `maintenance_mode`          ENUM('sales_or_visit','strict_sales','gps_visit') DEFAULT 'sales_or_visit',
            `consecutive_inactive_months` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `paused_reason`             VARCHAR(255) NULL,
            `last_activity_verified_at` TIMESTAMP NULL,
            `activated_at`              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uq_user_tier` (`user_id`, `tier_index`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
    ");
    echo "+ Table mlm_salary_grants present.\n";

    // bring an older table up to the current shape, one column at a time
    $columns = [
        'tenant_id'                  => "INT UNSIGNED NOT NULL DEFAULT 1",
        'maintenance_mode'           => "ENUM('sales_or_visit','strict_sales','gps_visit') DEFAULT 'sales_or_visit'",
        'consecutive_inactive_months' => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
        'paused_reason'              => "VARCHAR(255) NULL",
        'last_activity_verified_at'  => "TIMESTAMP NULL",
        'activated_at'               => "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        'months_paid'                => "INT DEFAULT 0",
    ];
    $has = function (string $col) use ($pdo): bool {
        $s = $pdo->prepare("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mlm_salary_grants' AND COLUMN_NAME = ?
        ");
        $s->execute([$col]);
        return (int) $s->fetchColumn() > 0;
    };
    foreach ($columns as $col => $definition) {
        if ($has($col)) {
            continue;
        }
        try {
            $pdo->exec("ALTER TABLE `mlm_salary_grants` ADD COLUMN `{$col}` {$definition}");
            echo "+ mlm_salary_grants.{$col} added\n";
        } catch (\Throwable $e) {
            echo "! mlm_salary_grants.{$col} FAILED: " . $e->getMessage() . "\n";
        }
    }

    // the 'paused' status was added in S453; make sure it is a member
    try {
        $s = $pdo->query("SHOW COLUMNS FROM `mlm_salary_grants` LIKE 'status'");
        $row = $s ? $s->fetch(PDO::FETCH_ASSOC) : null;
        if ($row && strpos((string)$row['Type'], "'paused'") === false) {
            $pdo->exec("ALTER TABLE `mlm_salary_grants` MODIFY COLUMN `status` ENUM('active','completed','cancelled','paused') DEFAULT 'active'");
            echo "+ mlm_salary_grants.status now includes 'paused'\n";
        }
    } catch (\Throwable $e) {
        echo "! mlm_salary_grants.status FAILED: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "! Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
