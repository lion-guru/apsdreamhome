<?php
/**
 * Migration: Dual-Mode Agent Commission System
 * ─────────────────────────────────────────────
 * Creates:
 *   1. agent_type, commission_type, commission_value columns in associates
 *   2. associate_downline_rates — broker sets custom rate for each downline member
 *   3. salaried_agent_structures — HR manages salary structure for salaried agents
 */

$root   = dirname(__DIR__, 2);
$config = require_once $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1: Add agent_type, commission_type, commission_value to associates
    // ─────────────────────────────────────────────────────────────────────────
    $cols = $pdo->query("SHOW COLUMNS FROM `associates` LIKE 'agent_type'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("
            ALTER TABLE `associates`
            ADD COLUMN `agent_type` ENUM('freelance_broker','salaried') NOT NULL DEFAULT 'freelance_broker'
                COMMENT 'freelance_broker = traditional network broker; salaried = company payroll employee',
            ADD COLUMN `commission_type` ENUM('percentage','per_sqft') NOT NULL DEFAULT 'percentage'
                COMMENT 'How this broker charges: % of plot value OR Rs per SqFt',
            ADD COLUMN `commission_value` DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'The broker own rate — either percent (e.g. 5.00) or Rs/SqFt (e.g. 45.00)',
            ADD COLUMN `is_salary_active` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT '1 = has active salary structure via salaried_agent_structures'
        ");
        echo "✅ associates: added agent_type, commission_type, commission_value, is_salary_active.\n";
    } else {
        echo "⏭  associates: agent_type column already exists, skipping.\n";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2: associate_downline_rates
    // Broker sets a custom commission rate for each of their downline members.
    // Margin = broker's own rate − rate given to downline member.
    // ─────────────────────────────────────────────────────────────────────────
    $pdo->exec("DROP TABLE IF EXISTS `associate_downline_rates`");
    $pdo->exec("
        CREATE TABLE `associate_downline_rates` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `parent_user_id`   INT NOT NULL
                COMMENT 'The broker/associate who is setting the rate for their downline',
            `child_user_id`    INT NOT NULL
                COMMENT 'The downline member receiving this rate',
            `commission_type`  ENUM('percentage','per_sqft') NOT NULL DEFAULT 'percentage',
            `commission_value` DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Rate given to the child. Broker margin = parent_rate - child_rate',
            `effective_from`   DATE NOT NULL,
            `effective_to`     DATE NULL
                COMMENT 'NULL means still active. Closed when rate is changed.',
            `set_by_user_id`   INT NOT NULL COMMENT 'Who created this rate row (parent or admin)',
            `notes`            VARCHAR(500) NULL,
            `tenant_id`        INT NOT NULL DEFAULT 1,
            `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_parent_child_date` (`parent_user_id`, `child_user_id`, `effective_from`),
            INDEX `idx_child_user` (`child_user_id`),
            INDEX `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
    ");
    echo "✅ Table associate_downline_rates created.\n";

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3: salaried_agent_structures
    // HR/Admin sets the salary structure for company-salaried salesmen.
    // ─────────────────────────────────────────────────────────────────────────
    $pdo->exec("DROP TABLE IF EXISTS `salaried_agent_structures`");
    $pdo->exec("
        CREATE TABLE `salaried_agent_structures` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `user_id`          INT NOT NULL COMMENT 'The salaried agent (associates.id)',
            `basic_salary`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `hra`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `ta_da`            DECIMAL(10,2) NOT NULL DEFAULT 0.00
                COMMENT 'Travel / Daily Allowance',
            `other_allowance`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `incentive_type`   ENUM('percentage','flat_per_plot') NOT NULL DEFAULT 'flat_per_plot'
                COMMENT 'percentage = % of plot sale value; flat_per_plot = fixed Rs per plot sold',
            `incentive_value`  DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Either the % (e.g. 1.50 means 1.5%) or the flat Rs amount per plot',
            `tds_applicable`   TINYINT(1) NOT NULL DEFAULT 1
                COMMENT '1 = deduct TDS (Section 192B) on salary',
            `effective_from`   DATE NOT NULL,
            `effective_to`     DATE NULL COMMENT 'NULL = current active structure',
            `set_by_user_id`   INT NOT NULL COMMENT 'HR / Admin user_id who set this',
            `remarks`          VARCHAR(1000) NULL,
            `tenant_id`        INT NOT NULL DEFAULT 1,
            `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_effective_from` (`effective_from`),
            INDEX `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
    ");
    echo "✅ Table salaried_agent_structures created.\n";

    echo "\n✅ All migrations completed successfully.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
