<?php
/**
 * Migration: Create missing salary tables
 * Creates: salary_payments, employee_salary_structure, salary_contracts, salary_history (if missing)
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

// Check and create employee_salary_structure table FIRST (referenced by salary_payments)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'employee_salary_structure'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `employee_salary_structure` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `employee_id` bigint(20) unsigned DEFAULT NULL,
            `basic_salary` decimal(15,2) NOT NULL,
            `hra` decimal(15,2) DEFAULT 0.00,
            `da` decimal(15,2) DEFAULT 0.00,
            `ta` decimal(15,2) DEFAULT 0.00,
            `medical_allowance` decimal(15,2) DEFAULT 0.00,
            `special_allowance` decimal(15,2) DEFAULT 0.00,
            `other_allowance` decimal(15,2) DEFAULT 0.00,
            `pf_deduction` decimal(15,2) DEFAULT 0.00,
            `esi_deduction` decimal(15,2) DEFAULT 0.00,
            `professional_tax` decimal(15,2) DEFAULT 0.00,
            `tds_deduction` decimal(15,2) DEFAULT 0.00,
            `other_deduction` decimal(15,2) DEFAULT 0.00,
            `gross_salary` decimal(15,2) NOT NULL,
            `net_salary` decimal(15,2) NOT NULL,
            `effective_from` date NOT NULL,
            `effective_to` date DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `approved_by` bigint(20) unsigned DEFAULT NULL,
            `approved_at` timestamp NULL DEFAULT NULL,
            `created_by` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `employee_id` (`employee_id`),
            KEY `approved_by` (`approved_by`),
            KEY `created_by` (`created_by`),
            KEY `idx_employee_salary_structure_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created employee_salary_structure table\n";
    } else {
        echo "⚠️  employee_salary_structure table already exists\n";
    }

// Check and create payroll_periods table FIRST (referenced by salary_payments)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'payroll_periods'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `payroll_periods` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `period_month` tinyint(3) unsigned NOT NULL,
            `period_year` smallint(5) unsigned NOT NULL,
            `status` enum('open','locked','closed') NOT NULL DEFAULT 'open',
            `locked_by` int(11) DEFAULT NULL,
            `locked_at` datetime DEFAULT NULL,
            `closed_by` int(11) DEFAULT NULL,
            `closed_at` datetime DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_tenant_period` (`tenant_id`,`period_month`,`period_year`),
            KEY `idx_status` (`status`),
            KEY `idx_period` (`period_year`,`period_month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created payroll_periods table\n";
    } else {
        echo "⚠️  payroll_periods table already exists\n";
    }

    // Check and create salary_payments table (after employee_salary_structure AND payroll_periods)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'salary_payments'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `salary_payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `employee_id` bigint(20) unsigned DEFAULT NULL,
            `associate_id` int(11) DEFAULT NULL,
            `salary_structure_id` int(11) DEFAULT NULL,
            `payment_month` int(11) NOT NULL,
            `payment_year` int(11) NOT NULL,
            `payroll_period_id` int(11) DEFAULT NULL,
            `payment_date` date NOT NULL,
            `basic_amount` decimal(15,2) DEFAULT NULL,
            `allowance_amount` decimal(15,2) DEFAULT NULL,
            `gross_amount` decimal(15,2) DEFAULT NULL,
            `deduction_amount` decimal(15,2) DEFAULT NULL,
            `net_amount` decimal(15,2) DEFAULT NULL,
            `payment_method` enum('bank_transfer','cash','cheque') DEFAULT 'bank_transfer',
            `payment_type` enum('regular','bonus','arrears','fnf_settlement','advance_recovery') DEFAULT 'regular',
            `transaction_id` varchar(100) DEFAULT NULL,
            `bank_reference` varchar(100) DEFAULT NULL,
            `payment_status` enum('pending','processed','paid','failed','cancelled') DEFAULT 'pending',
            `payment_processed_by` bigint(20) unsigned DEFAULT NULL,
            `payment_processed_at` timestamp NULL DEFAULT NULL,
            `remarks` text DEFAULT NULL,
            `created_by` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `employee_id` (`employee_id`),
            KEY `salary_structure_id` (`salary_structure_id`),
            KEY `payment_processed_by` (`payment_processed_by`),
            KEY `created_by` (`created_by`),
            KEY `idx_salary_payments_transaction_id` (`transaction_id`),
            KEY `idx_salary_payments_tenant_id` (`tenant_id`),
            KEY `idx_associate_id` (`associate_id`),
            KEY `idx_payroll_period` (`payroll_period_id`),
            CONSTRAINT `fk_salary_payments_salary_structure_id` FOREIGN KEY (`salary_structure_id`) REFERENCES `salary_structures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created salary_payments table\n";
    } else {
        echo "⚠️  salary_payments table already exists\n";
    }

    // Check and create salary_contracts table
    $tableExists = $pdo->query("SHOW TABLES LIKE 'salary_contracts'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `salary_contracts` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `employee_id` bigint(20) unsigned NOT NULL,
            `contract_number` varchar(50) NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date DEFAULT NULL,
            `ctc` decimal(12,2) NOT NULL,
            `basic_salary` decimal(12,2) NOT NULL,
            `terms` text DEFAULT NULL,
            `status` enum('draft','active','expired','terminated') DEFAULT 'draft',
            `signed_date` date DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `contract_number` (`contract_number`),
            KEY `idx_employee` (`employee_id`),
            KEY `idx_status` (`status`),
            KEY `idx_salary_contracts_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created salary_contracts table\n";
    } else {
        echo "⚠️  salary_contracts table already exists\n";
    }

    // Check and create salary_history table
    $tableExists = $pdo->query("SHOW TABLES LIKE 'salary_history'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `salary_history` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `employee_id` bigint(20) unsigned NOT NULL,
            `change_type` enum('increment','promotion','role_change','revision','adjustment') NOT NULL,
            `old_salary` decimal(12,2) DEFAULT NULL,
            `new_salary` decimal(12,2) NOT NULL,
            `effective_date` date NOT NULL,
            `reason` text DEFAULT NULL,
            `approved_by` bigint(20) unsigned DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_employee` (`employee_id`),
            KEY `idx_type` (`change_type`),
            KEY `idx_salary_history_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created salary_history table\n";
    } else {
        echo "⚠️  salary_history table already exists\n";
    }

    // Check and create salary_tracker table
    $tableExists = $pdo->query("SHOW TABLES LIKE 'salary_tracker'")->fetchColumn();
    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `salary_tracker` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `user_id` bigint(20) unsigned NOT NULL,
            `target_volume` decimal(15,2) NOT NULL,
            `achieved_in_days` int(11) DEFAULT NULL,
            `achieved_date` date DEFAULT NULL,
            `monthly_payout` decimal(15,2) NOT NULL,
            `duration_months` int(11) NOT NULL,
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `status` enum('active','completed','cancelled') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_salary_user` (`user_id`),
            KEY `idx_salary_status` (`status`),
            KEY `idx_salary_tracker_tenant_id` (`tenant_id`),
            CONSTRAINT `fk_salary_tracker_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);
        echo "✅ Created salary_tracker table\n";
    } else {
        echo "⚠️  salary_tracker table already exists\n";
    }

    // NOTE: payroll_periods is created by the block above; no duplicate needed.

    echo "\n✅ All salary tables created successfully.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

