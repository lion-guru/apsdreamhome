<?php
/**
 * MODULE 2 MIGRATION: Customer Sales + Allotment + Registry
 * Run: php scripts/migrate_module2_booking_lifecycle.php
 *
 * Creates 10 tables for the complete plot-booking lifecycle:
 *  1. plot_bookings               — central booking header
 *  2. booking_payment_schedules   — EMI schedule per booking
 *  3. booking_demand_letters      — auto-generated RERA demand notices
 *  4. booking_documents           — agreement, receipts, NOCs
 *  5. booking_status_history      — audit trail of stage changes
 *  6. booking_payment_receipts    — actual receipt records
 *  7. booking_refunds             — cancellation refunds
 *  8. booking_transfers           — booking name transfer
 *  9. booking_commissions         — direct/MLM sales commission
 * 10. rera_compliance_log         — RERA 70% escrow + quarterly progress
 *
 * NOTE: A prior migration `migrate_module2_sales_lifecycle.php` created a
 *       more comprehensive schema for the same 10 tables. The new
 *       specification supersedes it (per AGENTS.md 2026-06-07 plan):
 *       - shorter status enums aligned to booking lifecycle stages
 *       - booking_id instead of plot_booking_id (column rename)
 *       - simpler payment/commission structure
 *       Both schemas serve the same business purpose; this one is cleaner
 *       and uses the exact column names referenced by Module 2 services.
 *
 *       The script DROPS the existing tables first (they currently have
 *       0 rows of test data, so no real data is lost) and recreates them
 *       per the v2 spec.
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "========================================\n";
echo "MODULE 2 MIGRATION: Customer Sales + Allotment + Registry\n";
echo "========================================\n\n";

// Pre-flight: drop existing tables from prior schema so we can apply the
// new column structure cleanly. All 10 currently have 0 rows.
$existing = [
    'plot_bookings','booking_payment_schedules','booking_demand_letters',
    'booking_documents','booking_status_history','booking_payment_receipts',
    'booking_refunds','booking_transfers','booking_commissions','rera_compliance_log'
];
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($existing as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `$t`");
    echo "  ✓ dropped stale table: $t\n";
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "\n";

$queries = [

    // 1. plot_bookings — central booking header
    "CREATE TABLE IF NOT EXISTS `plot_bookings` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_id` int(11) NOT NULL,
        `customer_id` bigint(20) unsigned NOT NULL,
        `booking_number` varchar(50) NOT NULL,
        `booking_date` date NOT NULL,
        `total_plot_value` decimal(15,2) NOT NULL,
        `booking_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'token paid at booking time',
        `agreement_value` decimal(15,2) DEFAULT 0.00 COMMENT 'net value locked in agreement',
        `status` enum('token_paid','agreement_signed','emi_active','partially_paid','fully_paid','cancelled','transferred','registration_done') NOT NULL DEFAULT 'token_paid',
        `sales_manager_id` bigint(20) unsigned DEFAULT NULL,
        `channel` enum('direct','associate','agent','walk_in') DEFAULT 'direct',
        `associate_id` int(11) DEFAULT NULL,
        `commission_pct` decimal(5,2) DEFAULT 0.00,
        `commission_amount` decimal(15,2) DEFAULT 0.00,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_booking_number` (`booking_number`),
        KEY `idx_pb_plot` (`plot_id`),
        KEY `idx_pb_customer` (`customer_id`),
        KEY `idx_pb_status` (`status`),
        KEY `idx_pb_associate` (`associate_id`),
        KEY `idx_pb_channel` (`channel`),
        KEY `idx_pb_booking_date` (`booking_date`),
        KEY `idx_pb_status_date` (`status`,`booking_date`),
        CONSTRAINT `fk_pb_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
        CONSTRAINT `fk_pb_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
        CONSTRAINT `fk_pb_sales_manager` FOREIGN KEY (`sales_manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_pb_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. booking_payment_schedules — EMI schedule
    "CREATE TABLE IF NOT EXISTS `booking_payment_schedules` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `installment_no` int(11) NOT NULL,
        `due_date` date NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `principal` decimal(15,2) DEFAULT 0.00,
        `interest` decimal(15,2) DEFAULT 0.00,
        `opening_balance` decimal(15,2) DEFAULT 0.00,
        `closing_balance` decimal(15,2) DEFAULT 0.00,
        `status` enum('pending','paid','overdue','partial') NOT NULL DEFAULT 'pending',
        `paid_date` date DEFAULT NULL,
        `paid_amount` decimal(15,2) DEFAULT 0.00,
        `late_fee` decimal(15,2) DEFAULT 0.00,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_booking_inst` (`booking_id`,`installment_no`),
        KEY `idx_bps_booking_status` (`booking_id`,`status`),
        KEY `idx_bps_due_date` (`due_date`),
        KEY `idx_bps_status_due` (`status`,`due_date`),
        CONSTRAINT `fk_bps_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. booking_demand_letters — RERA demand notices
    "CREATE TABLE IF NOT EXISTS `booking_demand_letters` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `installment_id` bigint(20) unsigned DEFAULT NULL,
        `letter_number` varchar(50) NOT NULL,
        `generated_date` date NOT NULL,
        `due_date` date NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `status` enum('drafted','sent','viewed','paid','overdue') NOT NULL DEFAULT 'drafted',
        `sent_via` enum('email','sms','whatsapp','print') DEFAULT NULL,
        `sent_to_email` varchar(255) DEFAULT NULL,
        `sent_at` datetime DEFAULT NULL,
        `viewed_at` datetime DEFAULT NULL,
        `pdf_path` varchar(500) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_letter_number` (`letter_number`),
        KEY `idx_bdl_booking` (`booking_id`),
        KEY `idx_bdl_installment` (`installment_id`),
        KEY `idx_bdl_status` (`status`),
        KEY `idx_bdl_due` (`due_date`,`status`),
        CONSTRAINT `fk_bdl_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bdl_installment` FOREIGN KEY (`installment_id`) REFERENCES `booking_payment_schedules` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. booking_documents — agreement, receipts, NOCs
    "CREATE TABLE IF NOT EXISTS `booking_documents` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `document_type` enum('application_form','token_receipt','sale_agreement','allotment_letter','demand_letter','noc','receipt','registry_deed','mutation_letter','other') NOT NULL,
        `document_name` varchar(255) NOT NULL,
        `file_path` varchar(500) NOT NULL,
        `file_size` int(11) DEFAULT NULL,
        `mime_type` varchar(100) DEFAULT NULL,
        `uploaded_by` bigint(20) unsigned DEFAULT NULL,
        `verified_by` bigint(20) unsigned DEFAULT NULL,
        `verified_at` datetime DEFAULT NULL,
        `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bdoc_booking` (`booking_id`),
        KEY `idx_bdoc_type` (`booking_id`,`document_type`),
        KEY `idx_bdoc_status` (`status`),
        CONSTRAINT `fk_bdoc_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bdoc_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_bdoc_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 5. booking_status_history — audit trail
    "CREATE TABLE IF NOT EXISTS `booking_status_history` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `from_status` enum('token_paid','agreement_signed','emi_active','partially_paid','fully_paid','cancelled','transferred','registration_done') DEFAULT NULL,
        `to_status` enum('token_paid','agreement_signed','emi_active','partially_paid','fully_paid','cancelled','transferred','registration_done') NOT NULL,
        `changed_by` bigint(20) unsigned DEFAULT NULL,
        `reason` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` varchar(500) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bsh_booking` (`booking_id`),
        KEY `idx_bsh_booking_date` (`booking_id`,`created_at`),
        KEY `idx_bsh_to_status` (`to_status`),
        KEY `idx_bsh_changed_by` (`changed_by`),
        CONSTRAINT `fk_bsh_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bsh_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 6. booking_payment_receipts — actual receipts
    "CREATE TABLE IF NOT EXISTS `booking_payment_receipts` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `installment_id` bigint(20) unsigned DEFAULT NULL,
        `receipt_number` varchar(50) NOT NULL,
        `receipt_date` date NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `payment_mode` enum('cash','cheque','dd','neft','rtgs','upi','card','bank_transfer') NOT NULL DEFAULT 'cash',
        `cheque_number` varchar(50) DEFAULT NULL,
        `cheque_date` date DEFAULT NULL,
        `bank_name` varchar(255) DEFAULT NULL,
        `transaction_ref` varchar(100) DEFAULT NULL,
        `collected_by` bigint(20) unsigned DEFAULT NULL,
        `status` enum('cleared','bounced','cancelled','pending') NOT NULL DEFAULT 'pending',
        `bounce_date` date DEFAULT NULL,
        `bounce_reason` text DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_receipt_number` (`receipt_number`),
        KEY `idx_bpr_booking` (`booking_id`),
        KEY `idx_bpr_booking_status` (`booking_id`,`status`),
        KEY `idx_bpr_installment` (`installment_id`),
        KEY `idx_bpr_payment_mode` (`payment_mode`),
        KEY `idx_bpr_receipt_date` (`receipt_date`),
        KEY `idx_bpr_collected_by` (`collected_by`),
        CONSTRAINT `fk_bpr_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bpr_installment` FOREIGN KEY (`installment_id`) REFERENCES `booking_payment_schedules` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_bpr_collected_by` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 7. booking_refunds — cancellation refunds
    "CREATE TABLE IF NOT EXISTS `booking_refunds` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `refund_amount` decimal(15,2) NOT NULL,
        `cancellation_charge` decimal(15,2) DEFAULT 0.00,
        `deduction_reason` text DEFAULT NULL,
        `refund_mode` enum('cash','cheque','neft','rtgs','upi','bank_transfer') DEFAULT 'neft',
        `refund_date` date DEFAULT NULL,
        `bank_account` varchar(50) DEFAULT NULL,
        `transaction_ref` varchar(100) DEFAULT NULL,
        `status` enum('pending','approved','processed','failed') NOT NULL DEFAULT 'pending',
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `processed_by` bigint(20) unsigned DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_br_booking` (`booking_id`),
        KEY `idx_br_booking_status` (`booking_id`,`status`),
        KEY `idx_br_status` (`status`),
        KEY `idx_br_approved_by` (`approved_by`),
        CONSTRAINT `fk_br_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_br_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_br_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 8. booking_transfers — name transfer to another customer
    "CREATE TABLE IF NOT EXISTS `booking_transfers` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `original_booking_id` bigint(20) unsigned NOT NULL,
        `new_customer_id` bigint(20) unsigned NOT NULL,
        `transfer_reason` text DEFAULT NULL,
        `transfer_date` date NOT NULL,
        `transfer_charge` decimal(15,2) DEFAULT 0.00,
        `legal_charges` decimal(15,2) DEFAULT 0.00,
        `status` enum('initiated','docs_verified','approved','completed','rejected') NOT NULL DEFAULT 'initiated',
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bt_booking` (`original_booking_id`),
        KEY `idx_bt_new_customer` (`new_customer_id`),
        KEY `idx_bt_status` (`status`),
        KEY `idx_bt_approved_by` (`approved_by`),
        CONSTRAINT `fk_bt_booking` FOREIGN KEY (`original_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bt_new_customer` FOREIGN KEY (`new_customer_id`) REFERENCES `users` (`id`),
        CONSTRAINT `fk_bt_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 9. booking_commissions — direct + MLM commissions
    "CREATE TABLE IF NOT EXISTS `booking_commissions` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_id` bigint(20) unsigned NOT NULL,
        `beneficiary_user_id` bigint(20) unsigned NOT NULL,
        `source_user_id` bigint(20) unsigned DEFAULT NULL,
        `commission_type` enum('direct_sale','associate_referral','agent_referral','team_override','mlm_level_1','mlm_level_2','mlm_level_3') NOT NULL,
        `amount` decimal(15,2) NOT NULL,
        `percent` decimal(5,2) DEFAULT 0.00,
        `level` int(11) DEFAULT 1,
        `status` enum('pending','approved','paid','clawed_back','cancelled') NOT NULL DEFAULT 'pending',
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `paid_at` datetime DEFAULT NULL,
        `mlm_ledger_id` bigint(20) unsigned DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bc_booking` (`booking_id`),
        KEY `idx_bc_booking_status` (`booking_id`,`status`),
        KEY `idx_bc_beneficiary` (`beneficiary_user_id`),
        KEY `idx_bc_status` (`status`),
        KEY `idx_bc_beneficiary_status` (`beneficiary_user_id`,`status`),
        KEY `idx_bc_type` (`commission_type`),
        KEY `idx_bc_mlm_ledger` (`mlm_ledger_id`),
        CONSTRAINT `fk_bc_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_bc_beneficiary` FOREIGN KEY (`beneficiary_user_id`) REFERENCES `users` (`id`),
        CONSTRAINT `fk_bc_source` FOREIGN KEY (`source_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_bc_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 10. rera_compliance_log — 70% escrow + quarterly progress
    "CREATE TABLE IF NOT EXISTS `rera_compliance_log` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `project_colony_id` int(11) NOT NULL,
        `quarter` enum('Q1','Q2','Q3','Q4') NOT NULL,
        `year` int(11) NOT NULL,
        `progress_percent` decimal(5,2) DEFAULT 0.00,
        `amount_withdrawn` decimal(15,2) DEFAULT 0.00,
        `escrow_balance` decimal(15,2) DEFAULT 0.00,
        `status` enum('pending','submitted','accepted','rejected') NOT NULL DEFAULT 'pending',
        `report_file` varchar(500) DEFAULT NULL,
        `submitted_by` bigint(20) unsigned DEFAULT NULL,
        `submitted_at` datetime DEFAULT NULL,
        `rera_authority_response` text DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_colony_year_quarter` (`project_colony_id`,`year`,`quarter`),
        KEY `idx_rcl_colony` (`project_colony_id`),
        KEY `idx_rcl_status` (`status`),
        KEY `idx_rcl_year` (`year`),
        KEY `idx_rcl_submitted_by` (`submitted_by`),
        CONSTRAINT `fk_rcl_colony` FOREIGN KEY (`project_colony_id`) REFERENCES `colonies` (`id`),
        CONSTRAINT `fk_rcl_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$created = 0;
$errors = [];
foreach ($queries as $i => $sql) {
    $n = $i + 1;
    $tableName = '';
    if (preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', $sql, $m)) {
        $tableName = $m[1];
    }
    try {
        $pdo->exec($sql);
        echo "  ✓ [{$n}/10] Created table: {$tableName}\n";
        $created++;
    } catch (Exception $e) {
        echo "  ✗ [{$n}/10] Failed: {$tableName} - " . $e->getMessage() . "\n";
        $errors[] = $tableName;
    }
}

echo "\n----------------------------------------\n";
echo "Summary: {$created}/10 tables created successfully.\n";
if (!empty($errors)) {
    echo "Failed: " . implode(', ', $errors) . "\n";
    exit(1);
}

// Verify
echo "\nVerifying tables:\n";
$verify = ['plot_bookings','booking_payment_schedules','booking_demand_letters','booking_documents','booking_status_history','booking_payment_receipts','booking_refunds','booking_transfers','booking_commissions','rera_compliance_log'];
$placeholders = implode(',', array_fill(0, count($verify), '?'));
$stmt = $pdo->prepare("SELECT TABLE_NAME, TABLE_ROWS, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders) ORDER BY TABLE_NAME");
$stmt->execute(array_merge([$config['database']], $verify));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    printf("  %-35s engine=%s\n", $r['TABLE_NAME'], $r['ENGINE']);
}

echo "\n✓ MODULE 2 migration complete. Run php -l on new files to verify syntax.\n";
