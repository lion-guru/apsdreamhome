<?php
/**
 * Module 3: Money Workflow + Accounting — Migration
 *
 * Creates 15 tables for the full accounting lifecycle:
 *   1.  bank_accounts_master       (Bank accounts with KYC + RERA escrow)
 *   2.  daily_cash_book            (Daily cash book with auto-journal)
 *   3.  petty_cash                 (Petty cash management with running balance)
 *   4.  cheque_register            (Cheque / DD issued & cleared tracking)
 *   5.  bank_reconciliation        (Reconciliation headers)
 *   6.  bank_reconciliation_items  (Individual reconciliation line items)
 *   7.  tds_register               (TDS deductions register, 194IA / 194C / 194J)
 *   8.  gst_transactions           (Output / input GST with ITC tracking)
 *   9.  cheque_bounce_log          (Bounced cheque recovery tracking)
 *   10. demand_letter_template     (Configurable demand letter templates)
 *   11. cash_flow_forecast         (Cash flow projections — inflow / outflow)
 *   12. expense_approvals          (Multi-level expense approval workflow)
 *   13. vendor_payments            (Vendor payment tracking, separate from expenses)
 *   14. tds_certificates_issued    (Form 16A certificate tracking)
 *   15. payment_voucher_log        (Sequential voucher numbering audit log)
 *
 * Run:  php scripts/migrate_module3_money_workflow.php
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "================================================================\n";
echo " Module 3: Money Workflow + Accounting Migration\n";
echo "================================================================\n\n";

$results = ['created' => [], 'existed' => [], 'failed' => []];

function tableExists($db, $name) {
    try {
        $row = $db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$name]
        );
        return !empty($row) && (int)($row['cnt'] ?? 0) > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function execTable($db, $sql, $name, &$results) {
    if (tableExists($db, $name)) {
        echo "  [=] {$name} already exists, skipping.\n";
        $results['existed'][] = $name;
        return;
    }
    try {
        $db->execute($sql);
        echo "  [+] {$name} created.\n";
        $results['created'][] = $name;
    } catch (\Throwable $e) {
        echo "  [!] {$name} FAILED: " . $e->getMessage() . "\n";
        $results['failed'][] = $name;
    }
}

// ============================================================
// 1. bank_accounts_master
// ============================================================
$sql = "CREATE TABLE `bank_accounts_master` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_name` VARCHAR(255) NOT NULL,
  `account_number` VARCHAR(50) NOT NULL,
  `ifsc_code` VARCHAR(15) NOT NULL,
  `bank_name` VARCHAR(255) NOT NULL,
  `branch` VARCHAR(255) DEFAULT NULL,
  `account_type` ENUM('savings','current','escrow','trust','od','fd') NOT NULL DEFAULT 'current',
  `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `is_escrow` TINYINT(1) NOT NULL DEFAULT 0,
  `rera_project_id` INT(11) UNSIGNED DEFAULT NULL,
  `gst_registered` TINYINT(1) NOT NULL DEFAULT 0,
  `signatory_name` VARCHAR(255) DEFAULT NULL,
  `signatory_pan` VARCHAR(20) DEFAULT NULL,
  `cancelled_cheque_path` VARCHAR(500) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bam_active` (`active`),
  KEY `idx_bam_escrow` (`is_escrow`),
  KEY `idx_bam_type` (`account_type`),
  KEY `idx_bam_account_number` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "1. bank_accounts_master\n";
execTable($db, $sql, 'bank_accounts_master', $results);

// ============================================================
// 2. daily_cash_book
// ============================================================
$sql = "CREATE TABLE `daily_cash_book` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_date` DATE NOT NULL,
  `transaction_type` ENUM('receipt','payment','transfer','opening','closing','petty_cash') NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT(11) UNSIGNED DEFAULT NULL,
  `party_name` VARCHAR(255) DEFAULT NULL,
  `party_ledger` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_mode` ENUM('cash','cheque','upi','rtgs','neft','dd') NOT NULL DEFAULT 'cash',
  `narration` TEXT DEFAULT NULL,
  `voucher_number` VARCHAR(50) DEFAULT NULL,
  `bank_account_id` INT(11) UNSIGNED DEFAULT NULL,
  `recorded_by` INT(11) UNSIGNED DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dcb_date` (`transaction_date`),
  KEY `idx_dcb_type` (`transaction_type`),
  KEY `idx_dcb_voucher` (`voucher_number`),
  KEY `idx_dcb_bank` (`bank_account_id`),
  KEY `idx_dcb_ref` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "2. daily_cash_book\n";
execTable($db, $sql, 'daily_cash_book', $results);

// ============================================================
// 3. petty_cash
// ============================================================
$sql = "CREATE TABLE `petty_cash` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_date` DATE NOT NULL,
  `transaction_type` ENUM('topup','expense') NOT NULL,
  `category` ENUM('tea','stationery','travel','fuel','misc','courier','printing','refreshment') NOT NULL DEFAULT 'misc',
  `amount` DECIMAL(10,2) NOT NULL,
  `receipt_number` VARCHAR(50) DEFAULT NULL,
  `narration` TEXT DEFAULT NULL,
  `balance_after` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `custodian_id` INT(11) UNSIGNED DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pc_date` (`transaction_date`),
  KEY `idx_pc_type` (`transaction_type`),
  KEY `idx_pc_category` (`category`),
  KEY `idx_pc_custodian` (`custodian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "3. petty_cash\n";
execTable($db, $sql, 'petty_cash', $results);

// ============================================================
// 4. cheque_register
// ============================================================
$sql = "CREATE TABLE `cheque_register` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bank_account_id` INT(11) UNSIGNED NOT NULL,
  `cheque_number` VARCHAR(50) NOT NULL,
  `cheque_date` DATE NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payee_name` VARCHAR(255) NOT NULL,
  `purpose` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('issued','cleared','bounced','cancelled','stale') NOT NULL DEFAULT 'issued',
  `clearance_date` DATE DEFAULT NULL,
  `bounce_reason` TEXT DEFAULT NULL,
  `deposited_in_bank` INT(11) UNSIGNED DEFAULT NULL,
  `deposit_date` DATE DEFAULT NULL,
  `voucher_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cr_bank` (`bank_account_id`),
  KEY `idx_cr_status` (`status`),
  KEY `idx_cr_cheque_date` (`cheque_date`),
  KEY `idx_cr_cheque_number` (`cheque_number`),
  KEY `idx_cr_clearance` (`clearance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "4. cheque_register\n";
execTable($db, $sql, 'cheque_register', $results);

// ============================================================
// 5. bank_reconciliation
// ============================================================
$sql = "CREATE TABLE `bank_reconciliation` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bank_account_id` INT(11) UNSIGNED NOT NULL,
  `statement_date` DATE NOT NULL,
  `statement_balance` DECIMAL(15,2) NOT NULL,
  `book_balance` DECIMAL(15,2) NOT NULL,
  `difference` DECIMAL(15,2) GENERATED ALWAYS AS (`statement_balance` - `book_balance`) STORED,
  `reconciled_by` INT(11) UNSIGNED DEFAULT NULL,
  `reconciled_at` DATETIME DEFAULT NULL,
  `status` ENUM('draft','in_progress','completed') NOT NULL DEFAULT 'draft',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_br_bank` (`bank_account_id`),
  KEY `idx_br_date` (`statement_date`),
  KEY `idx_br_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "5. bank_reconciliation\n";
execTable($db, $sql, 'bank_reconciliation', $results);

// ============================================================
// 6. bank_reconciliation_items
// ============================================================
$sql = "CREATE TABLE `bank_reconciliation_items` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reconciliation_id` INT(11) UNSIGNED NOT NULL,
  `transaction_type` ENUM('cheque_issued_not_cleared','cheque_deposited_not_cleared','bank_charges','direct_credit','direct_debit','interest_credited','error') NOT NULL,
  `transaction_date` DATE NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  `our_voucher_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `status` ENUM('pending','cleared') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bri_recon` (`reconciliation_id`),
  KEY `idx_bri_type` (`transaction_type`),
  KEY `idx_bri_status` (`status`),
  KEY `idx_bri_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "6. bank_reconciliation_items\n";
execTable($db, $sql, 'bank_reconciliation_items', $results);

// ============================================================
// 7. tds_register
// ============================================================
$sql = "CREATE TABLE `tds_register` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tds_section` ENUM('192','194C','194H','194I','194IA','194IB','194J','194LA','194M','194N','194O','194P','194Q','194R','194S','195','196','197','194BA') NOT NULL,
  `deductor_pan` VARCHAR(20) DEFAULT NULL,
  `deductor_name` VARCHAR(255) DEFAULT NULL,
  `deductee_pan` VARCHAR(20) NOT NULL,
  `deductee_name` VARCHAR(255) NOT NULL,
  `transaction_date` DATE NOT NULL,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `gross_amount` DECIMAL(15,2) NOT NULL,
  `tds_rate` DECIMAL(5,2) NOT NULL,
  `tds_amount` DECIMAL(15,2) NOT NULL,
  `surcharge` DECIMAL(15,2) DEFAULT 0.00,
  `cess` DECIMAL(15,2) DEFAULT 0.00,
  `total_tds` DECIMAL(15,2) GENERATED ALWAYS AS (`tds_amount` + `surcharge` + `cess`) STORED,
  `deposit_challan` VARCHAR(50) DEFAULT NULL,
  `bsr_code` VARCHAR(20) DEFAULT NULL,
  `deposit_date` DATE DEFAULT NULL,
  `return_period` VARCHAR(10) DEFAULT NULL,
  `status` ENUM('pending','deposited','filed','verified') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tds_section` (`tds_section`),
  KEY `idx_tds_deductee_pan` (`deductee_pan`),
  KEY `idx_tds_date` (`transaction_date`),
  KEY `idx_tds_status` (`status`),
  KEY `idx_tds_period` (`return_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "7. tds_register\n";
execTable($db, $sql, 'tds_register', $results);

// ============================================================
// 8. gst_transactions
// ============================================================
$sql = "CREATE TABLE `gst_transactions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_date` DATE NOT NULL,
  `transaction_type` ENUM('output','input') NOT NULL,
  `party_gstin` VARCHAR(20) DEFAULT NULL,
  `party_name` VARCHAR(255) NOT NULL,
  `invoice_number` VARCHAR(50) DEFAULT NULL,
  `invoice_date` DATE DEFAULT NULL,
  `taxable_value` DECIMAL(15,2) NOT NULL,
  `cgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `cess_amount` DECIMAL(15,2) DEFAULT 0.00,
  `total_tax` DECIMAL(15,2) GENERATED ALWAYS AS (`cgst_amount` + `sgst_amount` + `igst_amount` + `cess_amount`) STORED,
  `hsn_sac_code` VARCHAR(20) DEFAULT NULL,
  `gst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `return_period` VARCHAR(10) DEFAULT NULL,
  `gstr1_status` ENUM('pending','uploaded','filed') DEFAULT 'pending',
  `gstr2b_status` ENUM('pending','matched','reconciled','mismatch') DEFAULT 'pending',
  `itc_eligible` TINYINT(1) DEFAULT 0,
  `itc_claimed` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gst_date` (`transaction_date`),
  KEY `idx_gst_type` (`transaction_type`),
  KEY `idx_gst_invoice` (`invoice_number`),
  KEY `idx_gst_period` (`return_period`),
  KEY `idx_gst_gstin` (`party_gstin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "8. gst_transactions\n";
execTable($db, $sql, 'gst_transactions', $results);

// ============================================================
// 9. cheque_bounce_log
// ============================================================
$sql = "CREATE TABLE `cheque_bounce_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cheque_id` BIGINT(20) UNSIGNED NOT NULL,
  `bounce_date` DATE NOT NULL,
  `bank_name` VARCHAR(255) DEFAULT NULL,
  `bounce_reason` TEXT DEFAULT NULL,
  `charges` DECIMAL(10,2) DEFAULT 0.00,
  `recovery_status` ENUM('pending','recovered','written_off','legal') NOT NULL DEFAULT 'pending',
  `recovered_date` DATE DEFAULT NULL,
  `legal_action` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cbl_cheque` (`cheque_id`),
  KEY `idx_cbl_status` (`recovery_status`),
  KEY `idx_cbl_date` (`bounce_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "9. cheque_bounce_log\n";
execTable($db, $sql, 'cheque_bounce_log', $results);

// ============================================================
// 10. demand_letter_template
// ============================================================
$sql = "CREATE TABLE `demand_letter_template` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_name` VARCHAR(255) NOT NULL,
  `template_type` ENUM('booking','allotment','emi','registration','possession','custom') NOT NULL DEFAULT 'custom',
  `subject` VARCHAR(500) NOT NULL,
  `body_html` LONGTEXT DEFAULT NULL,
  `body_text` LONGTEXT DEFAULT NULL,
  `sms_body` VARCHAR(1000) DEFAULT NULL,
  `whatsapp_body` VARCHAR(2000) DEFAULT NULL,
  `variables_json` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dlt_type` (`template_type`),
  KEY `idx_dlt_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "10. demand_letter_template\n";
execTable($db, $sql, 'demand_letter_template', $results);

// ============================================================
// 11. cash_flow_forecast
// ============================================================
$sql = "CREATE TABLE `cash_flow_forecast` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `projection_date` DATE NOT NULL,
  `type` ENUM('inflow','outflow') NOT NULL,
  `category` ENUM('customer_payment','land_acquisition','development','commission','salary','vendor','tax','loan','other') NOT NULL,
  `expected_amount` DECIMAL(15,2) NOT NULL,
  `probability_pct` DECIMAL(5,2) DEFAULT 100.00,
  `expected_date` DATE NOT NULL,
  `actual_amount` DECIMAL(15,2) DEFAULT NULL,
  `actual_date` DATE DEFAULT NULL,
  `source_ref` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cff_date` (`projection_date`),
  KEY `idx_cff_type` (`type`),
  KEY `idx_cff_category` (`category`),
  KEY `idx_cff_expected` (`expected_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "11. cash_flow_forecast\n";
execTable($db, $sql, 'cash_flow_forecast', $results);

// ============================================================
// 12. expense_approvals
// ============================================================
$sql = "CREATE TABLE `expense_approvals` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_id` BIGINT(20) UNSIGNED NOT NULL,
  `expense_table` VARCHAR(50) NOT NULL DEFAULT 'expenses',
  `requested_by` INT(11) UNSIGNED NOT NULL,
  `current_approver` INT(11) UNSIGNED DEFAULT NULL,
  `approver_role` VARCHAR(50) DEFAULT NULL,
  `approval_level` INT(11) NOT NULL DEFAULT 1,
  `status` ENUM('pending','approved','rejected','escalated') NOT NULL DEFAULT 'pending',
  `approved_at` DATETIME DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `next_approver` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ea_expense` (`expense_id`,`expense_table`),
  KEY `idx_ea_status` (`status`),
  KEY `idx_ea_approver` (`current_approver`),
  KEY `idx_ea_level` (`approval_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "12. expense_approvals\n";
execTable($db, $sql, 'expense_approvals', $results);

// ============================================================
// 13. vendor_payments
// ============================================================
$sql = "CREATE TABLE `vendor_payments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` INT(11) UNSIGNED DEFAULT NULL,
  `vendor_name` VARCHAR(255) NOT NULL,
  `vendor_gstin` VARCHAR(20) DEFAULT NULL,
  `bill_id` INT(11) UNSIGNED DEFAULT NULL,
  `bill_number` VARCHAR(50) DEFAULT NULL,
  `bill_date` DATE DEFAULT NULL,
  `gross_amount` DECIMAL(15,2) NOT NULL,
  `tds_section` VARCHAR(20) DEFAULT NULL,
  `tds_amount` DECIMAL(15,2) DEFAULT 0.00,
  `gst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `net_payable` DECIMAL(15,2) NOT NULL,
  `paid_amount` DECIMAL(15,2) DEFAULT 0.00,
  `balance` DECIMAL(15,2) GENERATED ALWAYS AS (`net_payable` - `paid_amount`) STORED,
  `payment_date` DATE DEFAULT NULL,
  `payment_mode` ENUM('cash','cheque','rtgs','neft','upi','dd') DEFAULT 'rtgs',
  `bank_account_id` INT(11) UNSIGNED DEFAULT NULL,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vp_vendor` (`vendor_id`),
  KEY `idx_vp_status` (`status`),
  KEY `idx_vp_bill` (`bill_number`),
  KEY `idx_vp_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "13. vendor_payments\n";
execTable($db, $sql, 'vendor_payments', $results);

// ============================================================
// 14. tds_certificates_issued
// ============================================================
$sql = "CREATE TABLE `tds_certificates_issued` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deductee_user_id` INT(11) UNSIGNED DEFAULT NULL,
  `deductee_name` VARCHAR(255) NOT NULL,
  `deductee_pan` VARCHAR(20) NOT NULL,
  `financial_year` VARCHAR(10) NOT NULL,
  `quarter` ENUM('Q1','Q2','Q3','Q4') NOT NULL,
  `total_tds` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `certificate_number` VARCHAR(50) DEFAULT NULL,
  `certificate_path` VARCHAR(500) DEFAULT NULL,
  `issued_date` DATE DEFAULT NULL,
  `download_count` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tci_deductee` (`deductee_user_id`),
  KEY `idx_tci_pan` (`deductee_pan`),
  KEY `idx_tci_fy` (`financial_year`),
  KEY `idx_tci_quarter` (`quarter`),
  UNIQUE KEY `idx_tci_unique` (`deductee_pan`,`financial_year`,`quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "14. tds_certificates_issued\n";
execTable($db, $sql, 'tds_certificates_issued', $results);

// ============================================================
// 15. payment_voucher_log
// ============================================================
$sql = "CREATE TABLE `payment_voucher_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `voucher_type` ENUM('receipt','payment','journal','contra','credit_note','debit_note') NOT NULL,
  `voucher_number` VARCHAR(50) NOT NULL,
  `voucher_date` DATE NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `narration` VARCHAR(500) DEFAULT NULL,
  `generated_for` VARCHAR(50) DEFAULT NULL,
  `reference_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_pvl_unique` (`voucher_type`,`voucher_number`),
  KEY `idx_pvl_date` (`voucher_date`),
  KEY `idx_pvl_type` (`voucher_type`),
  KEY `idx_pvl_ref` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "15. payment_voucher_log\n";
execTable($db, $sql, 'payment_voucher_log', $results);

// ============================================================
// Summary
// ============================================================
echo "\n================================================================\n";
echo " Migration Summary\n";
echo "================================================================\n";
echo "Tables created:  " . count($results['created']) . "\n";
echo "Tables existed:  " . count($results['existed']) . "\n";
echo "Tables failed:   " . count($results['failed']) . "\n";
echo "Total:           " . (count($results['created']) + count($results['existed'])) . " / 15\n";

if (!empty($results['created'])) {
    echo "\nNew tables:\n";
    foreach ($results['created'] as $t) echo "  - $t\n";
}
if (!empty($results['existed'])) {
    echo "\nAlready existing:\n";
    foreach ($results['existed'] as $t) echo "  - $t\n";
}
if (!empty($results['failed'])) {
    echo "\nFailed tables:\n";
    foreach ($results['failed'] as $t) echo "  - $t\n";
    exit(1);
}

echo "\n[OK] Module 3 migration complete.\n";
