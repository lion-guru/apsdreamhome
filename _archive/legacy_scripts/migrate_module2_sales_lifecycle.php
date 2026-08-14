<?php
/**
 * MODULE 2 MIGRATION: Customer Sales + Allotment + Registry
 * Run: php scripts/migrate_module2_sales_lifecycle.php
 *
 * Creates 10 tables:
 *  1. plot_bookings                    â€” central sales lifecycle (Inquiry â†’ Possession)
 *  2. booking_payment_schedules        â€” installment plan
 *  3. booking_demand_letters           â€” RERA demand notices
 *  4. booking_documents                â€” uploaded docs (KYC, agreements, etc.)
 *  5. booking_status_history           â€” audit trail of stage changes
 *  6. booking_payment_receipts         â€” receipts (separate from generic payments)
 *  7. booking_refunds                  â€” refund/cancellation TDS
 *  8. booking_transfers                â€” name transfer / co-owner add
 *  9. booking_commissions              â€” associate/agent/broker commission split
 * 10. rera_compliance_log              â€” RERA 70% escrow, quarterly filing audit
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Pre-flight: drop a stale plot_bookings table from a prior attempt that
// has the wrong column type (int(11) instead of bigint(20) unsigned). FK
// references to it would fail with errno 150. The 3 rows in that table
// are test data with no foreign dependencies, so safe to drop.
$stale = $pdo->query("SHOW TABLES LIKE 'plot_bookings'")->fetch();
if ($stale) {
    $col = $pdo->query("SHOW COLUMNS FROM plot_bookings WHERE Field='id'")->fetch(PDO::FETCH_ASSOC);
    if ($col && strpos($col['Type'], 'bigint') === false) {
        $count = $pdo->query("SELECT COUNT(*) c FROM plot_bookings")->fetch(PDO::FETCH_ASSOC)['c'];
        echo "Dropping stale plot_bookings (id is {$col['Type']}, {$count} rows of test data)...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("DROP TABLE plot_bookings");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "  âœ“ dropped\n\n";
    }
}

$queries = [

    // 1. plot_bookings (central lifecycle)
    "CREATE TABLE IF NOT EXISTS `plot_bookings` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `booking_number` varchar(50) NOT NULL,
        `plot_id` int(11) NOT NULL,
        `colony_id` int(11) NOT NULL,
        `customer_id` bigint(20) unsigned NOT NULL,
        `co_owners_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: list of co-buyers with share %' CHECK (json_valid(`co_owners_json`)),
        `source_inquiry_id` int(11) DEFAULT NULL,
        `associate_id` bigint(20) unsigned DEFAULT NULL,
        `agent_id` bigint(20) unsigned DEFAULT NULL,
        `referral_user_id` bigint(20) unsigned DEFAULT NULL,
        `booking_date` date NOT NULL,
        `agreement_date` date DEFAULT NULL,
        `expected_possession_date` date DEFAULT NULL,
        `actual_possession_date` date DEFAULT NULL,
        `total_value` decimal(15,2) NOT NULL,
        `discount_amount` decimal(15,2) DEFAULT 0.00,
        `discount_reason` varchar(255) DEFAULT NULL,
        `net_agreement_value` decimal(15,2) NOT NULL COMMENT 'total_value - discount',
        `stamp_duty_rate` decimal(5,2) DEFAULT 5.00 COMMENT '% of net value (state-dependent)',
        `stamp_duty_amount` decimal(15,2) DEFAULT 0.00,
        `registration_fee_rate` decimal(5,2) DEFAULT 1.00,
        `registration_fee_amount` decimal(15,2) DEFAULT 0.00,
        `maintenance_charges` decimal(15,2) DEFAULT 0.00,
        `corpus_fund` decimal(15,2) DEFAULT 0.00,
        `gst_on_agreement` decimal(15,2) DEFAULT 0.00 COMMENT '12% on under-construction',
        `total_payable` decimal(15,2) NOT NULL COMMENT 'sum customer must pay',
        `paid_amount` decimal(15,2) DEFAULT 0.00,
        `balance_amount` decimal(15,2) GENERATED ALWAYS AS (`total_payable` - `paid_amount`) STORED,
        `payment_plan_type` enum('one_time','emi_construction','emi_time','custom') DEFAULT 'one_time',
        `emi_tenure_months` int(11) DEFAULT 0,
        `emi_rate_annual` decimal(5,2) DEFAULT 0.00,
        `current_status` enum('booked','allotted','agreement_signed','registered','possession_handover','completed','cancelled','transferred') NOT NULL DEFAULT 'booked',
        `previous_status` enum('booked','allotted','agreement_signed','registered','possession_handover','completed','cancelled','transferred') DEFAULT NULL,
        `allotment_letter_no` varchar(100) DEFAULT NULL,
        `allotment_letter_date` date DEFAULT NULL,
        `sale_agreement_no` varchar(100) DEFAULT NULL,
        `registry_number` varchar(100) DEFAULT NULL,
        `registry_date` date DEFAULT NULL,
        `sub_registrar_office` varchar(255) DEFAULT NULL,
        `mutation_number` varchar(100) DEFAULT NULL,
        `mutation_date` date DEFAULT NULL,
        `possession_letter_no` varchar(100) DEFAULT NULL,
        `handover_date` date DEFAULT NULL,
        `defect_liability_end_date` date DEFAULT NULL,
        `tds_section_194ia_applicable` tinyint(1) DEFAULT 1 COMMENT '1% TDS on transfer of immovable property > â‚¹50L',
        `tds_amount_collected` decimal(15,2) DEFAULT 0.00,
        `rera_70pct_escrow_required` tinyint(1) DEFAULT 1,
        `rera_project_id` varchar(100) DEFAULT NULL,
        `cancellation_reason` text DEFAULT NULL,
        `cancellation_date` date DEFAULT NULL,
        `cancellation_refund_amount` decimal(15,2) DEFAULT 0.00,
        `cancellation_forfeit_amount` decimal(15,2) DEFAULT 0.00,
        `notes` text DEFAULT NULL,
        `internal_notes` text DEFAULT NULL,
        `created_by` bigint(20) unsigned DEFAULT NULL,
        `updated_by` bigint(20) unsigned DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_booking_number` (`booking_number`),
        KEY `idx_pb_plot` (`plot_id`),
        KEY `idx_pb_colony` (`colony_id`),
        KEY `idx_pb_customer` (`customer_id`),
        KEY `idx_pb_associate` (`associate_id`),
        KEY `idx_pb_agent` (`agent_id`),
        KEY `idx_pb_status` (`current_status`),
        KEY `idx_pb_booking_date` (`booking_date`),
        KEY `idx_pb_registry_date` (`registry_date`),
        KEY `idx_pb_status_date` (`current_status`,`booking_date`),
        KEY `idx_pb_customer_status` (`customer_id`,`current_status`),
        KEY `idx_pb_inquiry` (`source_inquiry_id`),
        CONSTRAINT `fk_pb_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
        CONSTRAINT `fk_pb_colony` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`),
        CONSTRAINT `fk_pb_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
        CONSTRAINT `fk_pb_associate` FOREIGN KEY (`associate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_pb_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. booking_payment_schedules
    "CREATE TABLE IF NOT EXISTS `booking_payment_schedules` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `installment_no` int(11) NOT NULL,
        `milestone_type` enum('booking','agreement','foundation','ground_floor','first_floor','second_floor','slab','plaster','flooring','possession','registry','emi_monthly','other') NOT NULL,
        `milestone_label` varchar(100) DEFAULT NULL,
        `scheduled_date` date NOT NULL,
        `scheduled_amount` decimal(15,2) NOT NULL,
        `paid_amount` decimal(15,2) DEFAULT 0.00,
        `outstanding_amount` decimal(15,2) GENERATED ALWAYS AS (`scheduled_amount` - `paid_amount`) STORED,
        `status` enum('upcoming','due','overdue','paid','partially_paid','waived') NOT NULL DEFAULT 'upcoming',
        `due_days_calculated` int(11) DEFAULT 0,
        `penalty_amount` decimal(15,2) DEFAULT 0.00,
        `paid_date` date DEFAULT NULL,
        `payment_id` bigint(20) unsigned DEFAULT NULL,
        `linked_demand_letter_id` bigint(20) unsigned DEFAULT NULL,
        `auto_generated` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_booking_installment` (`plot_booking_id`,`installment_no`),
        KEY `idx_bps_status_date` (`status`,`scheduled_date`),
        KEY `idx_bps_booking` (`plot_booking_id`),
        KEY `idx_bps_milestone` (`milestone_type`),
        KEY `idx_bps_due` (`scheduled_date`,`status`),
        CONSTRAINT `fk_bps_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. booking_demand_letters
    "CREATE TABLE IF NOT EXISTS `booking_demand_letters` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `demand_number` varchar(50) NOT NULL,
        `milestone_type` enum('booking','agreement','foundation','ground_floor','first_floor','second_floor','slab','plaster','flooring','possession','registry','emi_monthly','other') NOT NULL,
        `milestone_label` varchar(100) DEFAULT NULL,
        `amount_demanded` decimal(15,2) NOT NULL,
        `due_date` date NOT NULL,
        `letter_date` date NOT NULL,
        `letter_text` longtext DEFAULT NULL,
        `status` enum('draft','sent','paid','partially_paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
        `sent_at` datetime DEFAULT NULL,
        `sent_via` enum('email','sms','whatsapp','hand_delivery','courier','registered_post') DEFAULT NULL,
        `sent_to_address` text DEFAULT NULL,
        `customer_acknowledged_at` datetime DEFAULT NULL,
        `customer_signature_path` varchar(500) DEFAULT NULL,
        `total_received` decimal(15,2) DEFAULT 0.00,
        `balance_due` decimal(15,2) GENERATED ALWAYS AS (`amount_demanded` - `total_received`) STORED,
        `generated_by` bigint(20) unsigned DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_demand_number` (`demand_number`),
        KEY `idx_bdl_booking` (`plot_booking_id`),
        KEY `idx_bdl_status` (`status`),
        KEY `idx_bdl_due_date` (`due_date`),
        KEY `idx_bdl_milestone` (`milestone_type`),
        KEY `idx_bdl_booking_status` (`plot_booking_id`,`status`),
        CONSTRAINT `fk_bdl_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. booking_documents
    "CREATE TABLE IF NOT EXISTS `booking_documents` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `doc_type` enum('booking_form','allotment_letter','sale_agreement','agreement_ annexure','rera_registration','rera_qr_agreement','building_plan','layout_approval','occupancy_certificate','completion_certificate','title_clearance','encumbrance_certificate','mutation_application','registry_deed','tds_certificate_194ia','tds_certificate_194ib','noc_society','noc_municipality','tax_receipt','maintenance_receipt','possession_letter','handoover_letter','noc_bank','noc_lawyer','noc_mca','noc_builder','customer_pan','customer_aadhaar','customer_photo','customer_address_proof','customer_cheque','nri_documents','rera_form_b','rera_form_ba','rera_form_c','rera_form_d','rera_quarterly_return','rera_audit_report','rera_70pct_declaration','agreement_cover_letter','other') NOT NULL,
        `doc_subtype` varchar(100) DEFAULT NULL,
        `doc_title` varchar(255) DEFAULT NULL,
        `doc_number` varchar(100) DEFAULT NULL,
        `doc_date` date DEFAULT NULL,
        `file_path` varchar(500) NOT NULL,
        `file_name` varchar(255) DEFAULT NULL,
        `file_size_bytes` int(11) DEFAULT NULL,
        `file_mime_type` varchar(100) DEFAULT NULL,
        `file_hash_sha256` varchar(64) DEFAULT NULL,
        `uploaded_by` bigint(20) unsigned DEFAULT NULL,
        `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `verified_by` bigint(20) unsigned DEFAULT NULL,
        `verified_at` datetime DEFAULT NULL,
        `verification_status` enum('pending','verified','rejected','expired','superseded') NOT NULL DEFAULT 'pending',
        `verification_remarks` text DEFAULT NULL,
        `is_mandatory` tinyint(1) DEFAULT 0,
        `is_customer_uploaded` tinyint(1) DEFAULT 0,
        `is_public` tinyint(1) DEFAULT 0,
        `expiry_date` date DEFAULT NULL,
        `linked_payment_id` bigint(20) unsigned DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bdoc_booking` (`plot_booking_id`),
        KEY `idx_bdoc_type` (`doc_type`),
        KEY `idx_bdoc_status` (`verification_status`),
        KEY `idx_bdoc_booking_type` (`plot_booking_id`,`doc_type`),
        KEY `idx_bdoc_mandatory` (`is_mandatory`,`verification_status`),
        KEY `idx_bdoc_hash` (`file_hash_sha256`),
        CONSTRAINT `fk_bdoc_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 5. booking_status_history
    "CREATE TABLE IF NOT EXISTS `booking_status_history` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `from_status` enum('booked','allotted','agreement_signed','registered','possession_handover','completed','cancelled','transferred') DEFAULT NULL,
        `to_status` enum('booked','allotted','agreement_signed','registered','possession_handover','completed','cancelled','transferred') NOT NULL,
        `action_type` enum('initial','stage_change','allotment_issued','agreement_signed','registry_done','possession_given','cancellation','transfer','reopen','system') NOT NULL DEFAULT 'stage_change',
        `reason` text DEFAULT NULL,
        `metadata_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON of supporting data' CHECK (json_valid(`metadata_json`)),
        `effective_date` date NOT NULL,
        `changed_by` bigint(20) unsigned DEFAULT NULL,
        `changed_by_role` enum('admin','manager','employee','agent','associate','system','customer') DEFAULT 'admin',
        `approval_required` tinyint(1) DEFAULT 0,
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `approved_at` datetime DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bsh_booking` (`plot_booking_id`),
        KEY `idx_bsh_to_status` (`to_status`),
        KEY `idx_bsh_effective` (`effective_date`),
        KEY `idx_bsh_booking_date` (`plot_booking_id`,`effective_date`),
        KEY `idx_bsh_action` (`action_type`),
        KEY `idx_bsh_changed_by` (`changed_by`),
        CONSTRAINT `fk_bsh_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 6. booking_payment_receipts
    "CREATE TABLE IF NOT EXISTS `booking_payment_receipts` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `receipt_number` varchar(50) NOT NULL,
        `receipt_date` date NOT NULL,
        `amount_received` decimal(15,2) NOT NULL,
        `tds_deducted` decimal(15,2) DEFAULT 0.00,
        `net_received` decimal(15,2) GENERATED ALWAYS AS (`amount_received` - `tds_deducted`) STORED,
        `payment_mode` enum('cash','cheque','dd','rtgs','neft','imps','upi','card','netbanking','wallet','bank_transfer') NOT NULL DEFAULT 'cash',
        `cheque_number` varchar(50) DEFAULT NULL,
        `cheque_date` date DEFAULT NULL,
        `bank_name` varchar(255) DEFAULT NULL,
        `branch_name` varchar(255) DEFAULT NULL,
        `transaction_ref` varchar(100) DEFAULT NULL,
        `drawn_on` varchar(255) DEFAULT NULL,
        `realization_date` date DEFAULT NULL,
        `bounce_reason` text DEFAULT NULL,
        `bounce_date` date DEFAULT NULL,
        `status` enum('draft','received','realized','bounced','cancelled','reversed') NOT NULL DEFAULT 'received',
        `milestone_type` enum('booking','agreement','foundation','ground_floor','first_floor','second_floor','slab','plaster','flooring','possession','registry','emi_monthly','other') DEFAULT 'other',
        `towards_milestone` varchar(255) DEFAULT NULL,
        `linked_demand_letter_id` bigint(20) unsigned DEFAULT NULL,
        `linked_payment_id` bigint(20) unsigned DEFAULT NULL,
        `received_by` bigint(20) unsigned DEFAULT NULL,
        `received_from` varchar(255) DEFAULT NULL,
        `pan_of_payer` varchar(20) DEFAULT NULL,
        `aadhaar_of_payer` varchar(20) DEFAULT NULL,
        `is_tds_applicable` tinyint(1) DEFAULT 0,
        `tds_section` varchar(20) DEFAULT NULL,
        `tds_certificate_no` varchar(100) DEFAULT NULL,
        `tds_quarter` varchar(10) DEFAULT NULL COMMENT 'e.g. Q1-2026',
        `narration` text DEFAULT NULL,
        `pdf_path` varchar(500) DEFAULT NULL,
        `sent_to_customer_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_receipt_number` (`receipt_number`),
        KEY `idx_bpr_booking` (`plot_booking_id`),
        KEY `idx_bpr_status` (`status`),
        KEY `idx_bpr_receipt_date` (`receipt_date`),
        KEY `idx_bpr_payment_mode` (`payment_mode`),
        KEY `idx_bpr_realization` (`realization_date`),
        KEY `idx_bpr_booking_date` (`plot_booking_id`,`receipt_date`),
        KEY `idx_bpr_tds_quarter` (`tds_quarter`),
        CONSTRAINT `fk_bpr_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 7. booking_refunds
    "CREATE TABLE IF NOT EXISTS `booking_refunds` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `refund_number` varchar(50) NOT NULL,
        `refund_date` date NOT NULL,
        `reason` enum('cancellation','partial_cancellation','double_payment','customer_request','legal_dispute','bank_reversal','overcharge','other') NOT NULL,
        `reason_description` text DEFAULT NULL,
        `gross_refund_amount` decimal(15,2) NOT NULL,
        `forfeit_amount` decimal(15,2) DEFAULT 0.00,
        `tds_on_forfeit` decimal(15,2) DEFAULT 0.00 COMMENT 'TDS 30% u/s 194C on cancellation/forfeit',
        `net_refund_amount` decimal(15,2) GENERATED ALWAYS AS (`gross_refund_amount` - `forfeit_amount` - `tds_on_forfeit`) STORED,
        `refund_mode` enum('cash','cheque','rtgs','neft','upi','bank_transfer') DEFAULT 'rtgs',
        `bank_account_id` bigint(20) unsigned DEFAULT NULL,
        `payee_name` varchar(255) NOT NULL,
        `payee_bank_account` varchar(50) DEFAULT NULL,
        `payee_ifsc` varchar(20) DEFAULT NULL,
        `transaction_ref` varchar(100) DEFAULT NULL,
        `transaction_date` date DEFAULT NULL,
        `status` enum('pending','approved','processing','completed','failed','rejected') NOT NULL DEFAULT 'pending',
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `approved_at` datetime DEFAULT NULL,
        `processed_by` bigint(20) unsigned DEFAULT NULL,
        `processed_at` datetime DEFAULT NULL,
        `failure_reason` text DEFAULT NULL,
        `linked_receipt_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON of receipt IDs being refunded' CHECK (json_valid(`linked_receipt_ids`)),
        `tds_challan_number` varchar(100) DEFAULT NULL,
        `tcs_applicable` tinyint(1) DEFAULT 0,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_refund_number` (`refund_number`),
        KEY `idx_br_booking` (`plot_booking_id`),
        KEY `idx_br_status` (`status`),
        KEY `idx_br_refund_date` (`refund_date`),
        KEY `idx_br_reason` (`reason`),
        KEY `idx_br_booking_status` (`plot_booking_id`,`status`),
        CONSTRAINT `fk_br_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 8. booking_transfers
    "CREATE TABLE IF NOT EXISTS `booking_transfers` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `transfer_type` enum('full_transfer','co_owner_addition','co_owner_removal','name_correction','nominee_change') NOT NULL,
        `transfer_number` varchar(50) NOT NULL,
        `transfer_date` date NOT NULL,
        `effective_date` date NOT NULL,
        `from_customer_id` bigint(20) unsigned DEFAULT NULL,
        `to_customer_id` bigint(20) unsigned DEFAULT NULL,
        `from_name` varchar(255) DEFAULT NULL,
        `to_name` varchar(255) NOT NULL,
        `to_pan` varchar(20) DEFAULT NULL,
        `to_aadhaar` varchar(20) DEFAULT NULL,
        `to_phone` varchar(20) DEFAULT NULL,
        `to_email` varchar(255) DEFAULT NULL,
        `to_address` text DEFAULT NULL,
        `transfer_reason` text DEFAULT NULL,
        `transfer_fee` decimal(15,2) DEFAULT 0.00,
        `tds_on_transfer` decimal(15,2) DEFAULT 0.00 COMMENT '1% u/s 194IA if value > 50L',
        `consideration_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Sale consideration for full transfer',
        `share_percentage` decimal(5,2) DEFAULT 100.00,
        `consent_letter_path` varchar(500) DEFAULT NULL,
        `noc_from_bank_path` varchar(500) DEFAULT NULL,
        `noc_from_builder_path` varchar(500) DEFAULT NULL,
        `new_agreement_path` varchar(500) DEFAULT NULL,
        `status` enum('initiated','documents_pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'initiated',
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `approved_at` datetime DEFAULT NULL,
        `rejected_reason` text DEFAULT NULL,
        `completed_at` datetime DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_transfer_number` (`transfer_number`),
        KEY `idx_bt_booking` (`plot_booking_id`),
        KEY `idx_bt_type` (`transfer_type`),
        KEY `idx_bt_status` (`status`),
        KEY `idx_bt_to_customer` (`to_customer_id`),
        KEY `idx_bt_effective` (`effective_date`),
        CONSTRAINT `fk_bt_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 9. booking_commissions
    "CREATE TABLE IF NOT EXISTS `booking_commissions` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned NOT NULL,
        `beneficiary_user_id` bigint(20) unsigned NOT NULL,
        `beneficiary_role` enum('associate','agent','broker','employee','referrer') NOT NULL,
        `commission_basis` decimal(15,2) NOT NULL COMMENT 'Net agreement value or specific milestone',
        `commission_pct` decimal(5,2) NOT NULL,
        `commission_amount` decimal(15,2) NOT NULL,
        `tds_section` enum('194H','194C','194J','194IBA','none') DEFAULT '194H' COMMENT '194H=brokerage 5%, 194C=contractor 1-2%, 194IBA=online gaming',
        `tds_rate` decimal(5,2) DEFAULT 5.00,
        `tds_amount` decimal(15,2) DEFAULT 0.00,
        `net_payable` decimal(15,2) GENERATED ALWAYS AS (`commission_amount` - `tds_amount`) STORED,
        `trigger_type` enum('on_booking','on_agreement','on_first_payment','on_50pct_payment','on_possession','on_full_payment','manual') NOT NULL DEFAULT 'on_booking',
        `trigger_condition_met_at` datetime DEFAULT NULL,
        `payout_status` enum('pending','eligible','approved','paid','reversed','on_hold') NOT NULL DEFAULT 'pending',
        `eligible_at` datetime DEFAULT NULL,
        `approved_by` bigint(20) unsigned DEFAULT NULL,
        `approved_at` datetime DEFAULT NULL,
        `paid_at` datetime DEFAULT NULL,
        `paid_via` enum('cash','cheque','rtgs','neft','upi','wallet') DEFAULT NULL,
        `transaction_ref` varchar(100) DEFAULT NULL,
        `reversal_reason` text DEFAULT NULL,
        `reversed_at` datetime DEFAULT NULL,
        `tds_certificate_path` varchar(500) DEFAULT NULL,
        `mlm_level` int(11) DEFAULT 1 COMMENT 'MLM depth (1=direct, 2=level-2, 3=level-3)',
        `linked_payment_id` bigint(20) unsigned DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_bc_booking` (`plot_booking_id`),
        KEY `idx_bc_beneficiary` (`beneficiary_user_id`),
        KEY `idx_bc_role` (`beneficiary_role`),
        KEY `idx_bc_status` (`payout_status`),
        KEY `idx_bc_trigger` (`trigger_type`),
        KEY `idx_bc_booking_beneficiary` (`plot_booking_id`,`beneficiary_user_id`),
        KEY `idx_bc_eligible` (`eligible_at`,`payout_status`),
        CONSTRAINT `fk_bc_booking` FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 10. rera_compliance_log
    "CREATE TABLE IF NOT EXISTS `rera_compliance_log` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `plot_booking_id` bigint(20) unsigned DEFAULT NULL,
        `colony_id` int(11) DEFAULT NULL,
        `compliance_type` enum('70pct_escrow_deposit','quarterly_return_filing','rera_registration','project_extension','registration_cancellation','change_in_promoter','deficit_in_escrow','form_b_ba','form_c_d','structural_defect_report','consumer_complaint','project_completion','occupancy_certificate','rera_inspection','audit_report_filing','annual_filing','corpus_fund_deposit','tds_quarterly_challan','other') NOT NULL,
        `compliance_number` varchar(100) DEFAULT NULL,
        `compliance_date` date NOT NULL,
        `due_date` date DEFAULT NULL,
        `filed_at` datetime DEFAULT NULL,
        `filing_reference` varchar(255) DEFAULT NULL,
        `status` enum('pending','filed','overdue','approved','rejected','remediation_required','cancelled') NOT NULL DEFAULT 'pending',
        `amount_involved` decimal(15,2) DEFAULT 0.00,
        `penalty_amount` decimal(15,2) DEFAULT 0.00,
        `documents_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON list of doc paths' CHECK (json_valid(`documents_json`)),
        `uploaded_documents` text DEFAULT NULL,
        `quarter` varchar(10) DEFAULT NULL COMMENT 'e.g. Q1-2026',
        `financial_year` varchar(10) DEFAULT NULL COMMENT 'e.g. FY2025-26',
        `responsible_user_id` bigint(20) unsigned DEFAULT NULL,
        `responsible_department` varchar(100) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `next_review_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_rcl_booking` (`plot_booking_id`),
        KEY `idx_rcl_colony` (`colony_id`),
        KEY `idx_rcl_type` (`compliance_type`),
        KEY `idx_rcl_status` (`status`),
        KEY `idx_rcl_due` (`due_date`,`status`),
        KEY `idx_rcl_quarter` (`quarter`,`status`),
        KEY `idx_rcl_fy` (`financial_year`,`status`),
        KEY `idx_rcl_filing_date` (`compliance_date`,`filed_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
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
        echo "âœ“ [{$n}/10] Created table: {$tableName}\n";
        $created++;
    } catch (Exception $e) {
        echo "âœ— [{$n}/10] Failed: {$tableName} - " . $e->getMessage() . "\n";
        $errors[] = $tableName;
    }
}

echo "\n----------------------------------------\n";
echo "Summary: {$created}/10 tables created.\n";
if (!empty($errors)) {
    echo "Failed: " . implode(', ', $errors) . "\n";
    exit(1);
}

// Verify by listing tables
echo "\nVerifying tables exist:\n";
$verify = ['plot_bookings','booking_payment_schedules','booking_demand_letters','booking_documents','booking_status_history','booking_payment_receipts','booking_refunds','booking_transfers','booking_commissions','rera_compliance_log'];
$placeholders = implode(',', array_fill(0, count($verify), '?'));
$stmt = $pdo->prepare("SELECT TABLE_NAME, TABLE_ROWS, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)");
$stmt->execute(array_merge([$config['database']], $verify));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    printf("  %-35s engine=%s rows=%s\n", $r['TABLE_NAME'], $r['ENGINE'], $r['TABLE_ROWS']);
}

echo "\nâœ“ Module 2 migration complete!\n";?>