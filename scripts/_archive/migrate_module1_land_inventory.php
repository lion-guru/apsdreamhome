<?php
/**
 * Module 1: Land Acquisition + Plot Inventory — Migration
 *
 * Creates 10 tables for the full land acquisition lifecycle:
 *   1. land_brokers
 *   2. land_leads
 *   3. land_documents
 *   4. land_site_visits
 *   5. land_legal_opinions
 *   6. land_acquisitions
 *   7. land_acquisition_payments
 *   8. colony_development_costs
 *   9. colony_layouts
 *  10. plot_status_history
 *
 * Run:  php scripts/migrate_module1_land_inventory.php
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "================================================================\n";
echo " Module 1: Land Acquisition + Plot Inventory Migration\n";
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
// 1. land_brokers
// ============================================================
$sql = "CREATE TABLE `land_brokers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `broker_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `pan_number` VARCHAR(20) DEFAULT NULL,
  `aadhaar_number` VARCHAR(20) DEFAULT NULL,
  `rera_number` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `commission_percentage` DECIMAL(5,2) DEFAULT 2.00,
  `bank_account` VARCHAR(50) DEFAULT NULL,
  `ifsc` VARCHAR(20) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lb_active` (`active`),
  KEY `idx_lb_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "1. land_brokers\n";
execTable($db, $sql, 'land_brokers', $results);

// ============================================================
// 2. land_leads
// ============================================================
$sql = "CREATE TABLE `land_leads` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_source` ENUM('broker','scout','direct','referral','web','phone') NOT NULL DEFAULT 'direct',
  `broker_id` INT(11) UNSIGNED DEFAULT NULL,
  `land_owner_name` VARCHAR(255) NOT NULL,
  `owner_phone` VARCHAR(20) DEFAULT NULL,
  `owner_email` VARCHAR(255) DEFAULT NULL,
  `village` VARCHAR(255) DEFAULT NULL,
  `tehsil` VARCHAR(255) DEFAULT NULL,
  `district` VARCHAR(255) DEFAULT NULL,
  `state` VARCHAR(255) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `gps_lat` DECIMAL(10,7) DEFAULT NULL,
  `gps_lng` DECIMAL(10,7) DEFAULT NULL,
  `survey_number` VARCHAR(100) DEFAULT NULL,
  `area_acres` DECIMAL(10,2) DEFAULT NULL,
  `area_sqft` DECIMAL(12,2) DEFAULT NULL,
  `expected_price` DECIMAL(15,2) DEFAULT NULL,
  `status` ENUM('new','screening','visit_done','dd','negotiation','legal','sale_agreement','registered','rejected','dropped') NOT NULL DEFAULT 'new',
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ll_status` (`status`),
  KEY `idx_ll_broker` (`broker_id`),
  KEY `idx_ll_assigned` (`assigned_to`),
  KEY `idx_ll_district` (`district`),
  KEY `idx_ll_source` (`lead_source`),
  KEY `idx_ll_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "2. land_leads\n";
execTable($db, $sql, 'land_leads', $results);

// ============================================================
// 3. land_documents
// ============================================================
$sql = "CREATE TABLE `land_documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `land_lead_id` INT(11) UNSIGNED DEFAULT NULL,
  `land_deal_id` INT(11) UNSIGNED DEFAULT NULL,
  `doc_type` ENUM('mother_deed','chain_of_title','ec_30yr','patta','chitta','fmb','a_register','property_tax','kist_receipt','succession_cert','noc_co_owners','layout_plan','conversion_order','power_of_attorney','sale_agreement','registered_deed','mutation_application','other') NOT NULL,
  `doc_number` VARCHAR(100) DEFAULT NULL,
  `doc_date` DATE DEFAULT NULL,
  `uploaded_by` INT(11) UNSIGNED DEFAULT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `verification_status` ENUM('pending','verified','missing','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` INT(11) UNSIGNED DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ld_lead` (`land_lead_id`),
  KEY `idx_ld_deal` (`land_deal_id`),
  KEY `idx_ld_type` (`doc_type`),
  KEY `idx_ld_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "3. land_documents\n";
execTable($db, $sql, 'land_documents', $results);

// ============================================================
// 4. land_site_visits
// ============================================================
$sql = "CREATE TABLE `land_site_visits` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `land_lead_id` INT(11) UNSIGNED NOT NULL,
  `visited_by` INT(11) UNSIGNED DEFAULT NULL,
  `visit_date` DATETIME NOT NULL,
  `gps_lat` DECIMAL(10,7) DEFAULT NULL,
  `gps_lng` DECIMAL(10,7) DEFAULT NULL,
  `weather` VARCHAR(100) DEFAULT NULL,
  `observations` TEXT DEFAULT NULL,
  `encroachment_found` TINYINT(1) NOT NULL DEFAULT 0,
  `encroachment_details` TEXT DEFAULT NULL,
  `photos_json` TEXT DEFAULT NULL,
  `risk_rating` ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lsv_lead` (`land_lead_id`),
  KEY `idx_lsv_visit_date` (`visit_date`),
  KEY `idx_lsv_visited_by` (`visited_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "4. land_site_visits\n";
execTable($db, $sql, 'land_site_visits', $results);

// ============================================================
// 5. land_legal_opinions
// ============================================================
$sql = "CREATE TABLE `land_legal_opinions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `land_lead_id` INT(11) UNSIGNED NOT NULL,
  `advocate_name` VARCHAR(255) NOT NULL,
  `opinion_date` DATE NOT NULL,
  `status` ENUM('clear','conditional','not_clear') NOT NULL DEFAULT 'conditional',
  `title_verified_chain` TINYINT(1) NOT NULL DEFAULT 0,
  `encumbrance_review` TINYINT(1) NOT NULL DEFAULT 0,
  `boundary_match` TINYINT(1) NOT NULL DEFAULT 0,
  `co_owners_identified` TINYINT(1) NOT NULL DEFAULT 0,
  `encroachment_risk` VARCHAR(50) DEFAULT NULL,
  `government_acquisition_check` TINYINT(1) NOT NULL DEFAULT 0,
  `rera_implications` TEXT DEFAULT NULL,
  `opinion_document_path` VARCHAR(500) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_llo_lead` (`land_lead_id`),
  KEY `idx_llo_status` (`status`),
  KEY `idx_llo_date` (`opinion_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "5. land_legal_opinions\n";
execTable($db, $sql, 'land_legal_opinions', $results);

// ============================================================
// 6. land_deals (renamed from land_acquisitions to avoid conflict with
//    legacy farmer-table `land_acquisitions`)
// ============================================================
$sql = "CREATE TABLE `land_deals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `land_lead_id` INT(11) UNSIGNED NOT NULL,
  `colony_id` INT(11) UNSIGNED DEFAULT NULL,
  `total_area_sqft` DECIMAL(12,2) DEFAULT NULL,
  `acquired_area_sqft` DECIMAL(12,2) DEFAULT NULL,
  `total_consideration` DECIMAL(15,2) DEFAULT NULL,
  `advance_paid` DECIMAL(15,2) DEFAULT 0.00,
  `balance_amount` DECIMAL(15,2) DEFAULT 0.00,
  `sale_agreement_date` DATE DEFAULT NULL,
  `sale_agreement_number` VARCHAR(100) DEFAULT NULL,
  `registration_date` DATE DEFAULT NULL,
  `registration_number` VARCHAR(100) DEFAULT NULL,
  `sub_registrar_office` VARCHAR(255) DEFAULT NULL,
  `stamp_duty_amount` DECIMAL(15,2) DEFAULT 0.00,
  `registration_fee` DECIMAL(15,2) DEFAULT 0.00,
  `mutation_status` ENUM('not_started','applied','in_progress','completed','rejected') NOT NULL DEFAULT 'not_started',
  `mutation_number` VARCHAR(100) DEFAULT NULL,
  `mutation_date` DATE DEFAULT NULL,
  `status` ENUM('in_progress','registered','mutated','closed','cancelled') NOT NULL DEFAULT 'in_progress',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_lead` (`land_lead_id`),
  KEY `idx_la_colony` (`colony_id`),
  KEY `idx_la_status` (`status`),
  KEY `idx_la_reg_date` (`registration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "6. land_acquisitions\n";
execTable($db, $sql, 'land_deals', $results);

// ============================================================
// 7. land_deal_payments (renamed from land_acquisition_payments)
// ============================================================
$sql = "CREATE TABLE `land_deal_payments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `land_deal_id` INT(11) UNSIGNED NOT NULL,
  `payment_type` ENUM('advance','balance','stamp_duty','registration_fee','mutation_fee','broker_commission','legal_fee','other') NOT NULL,
  `payee_name` VARCHAR(255) NOT NULL,
  `payee_pan` VARCHAR(20) DEFAULT NULL,
  `payee_bank_account` VARCHAR(50) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_mode` ENUM('cash','cheque','rtgs','neft','upi','dd') NOT NULL DEFAULT 'rtgs',
  `cheque_number` VARCHAR(50) DEFAULT NULL,
  `cheque_date` DATE DEFAULT NULL,
  `bank_name` VARCHAR(255) DEFAULT NULL,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `tds_amount` DECIMAL(15,2) DEFAULT 0.00,
  `tds_section` VARCHAR(20) DEFAULT NULL,
  `voucher_number` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','cleared','bounced','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lap_deal` (`land_deal_id`),
  KEY `idx_lap_type` (`payment_type`),
  KEY `idx_lap_date` (`payment_date`),
  KEY `idx_lap_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "7. land_deal_payments\n";
execTable($db, $sql, 'land_deal_payments', $results);

// ============================================================
// 8. colony_development_costs
// ============================================================
$sql = "CREATE TABLE `colony_development_costs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `colony_id` INT(11) UNSIGNED NOT NULL,
  `cost_type` ENUM('land_acquisition','road','electricity','water','sewerage','street_light','drainage','compound_wall','gate','security','landscaping','approval_fee','legal','brokerage','marketing','office_setup','staff','other') NOT NULL,
  `vendor_id` INT(11) UNSIGNED DEFAULT NULL,
  `vendor_name` VARCHAR(255) DEFAULT NULL,
  `work_description` TEXT DEFAULT NULL,
  `invoice_number` VARCHAR(100) DEFAULT NULL,
  `invoice_date` DATE DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `gst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `tds_section` VARCHAR(20) DEFAULT NULL,
  `payment_status` ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `paid_amount` DECIMAL(15,2) DEFAULT 0.00,
  `balance_amount` DECIMAL(15,2) DEFAULT 0.00,
  `completion_date` DATE DEFAULT NULL,
  `status` ENUM('planned','in_progress','completed','on_hold','cancelled') NOT NULL DEFAULT 'planned',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cdc_colony` (`colony_id`),
  KEY `idx_cdc_type` (`cost_type`),
  KEY `idx_cdc_status` (`status`),
  KEY `idx_cdc_pay_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "8. colony_development_costs\n";
execTable($db, $sql, 'colony_development_costs', $results);

// ============================================================
// 9. colony_layouts
// ============================================================
$sql = "CREATE TABLE `colony_layouts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `colony_id` INT(11) UNSIGNED NOT NULL,
  `layout_name` VARCHAR(255) NOT NULL,
  `total_plots` INT(11) NOT NULL DEFAULT 0,
  `total_area_sqft` DECIMAL(12,2) DEFAULT NULL,
  `layout_plan_image` VARCHAR(500) DEFAULT NULL,
  `approved_by` VARCHAR(255) DEFAULT NULL,
  `approval_date` DATE DEFAULT NULL,
  `government_approval_number` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cl_colony` (`colony_id`),
  KEY `idx_cl_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "9. colony_layouts\n";
execTable($db, $sql, 'colony_layouts', $results);

// ============================================================
// 10. plot_status_history
// ============================================================
$sql = "CREATE TABLE `plot_status_history` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plot_id` INT(11) UNSIGNED NOT NULL,
  `old_status` VARCHAR(50) DEFAULT NULL,
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` INT(11) UNSIGNED DEFAULT NULL,
  `change_reason` VARCHAR(255) DEFAULT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metadata_json` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_psh_plot` (`plot_id`),
  KEY `idx_psh_changed` (`changed_at`),
  KEY `idx_psh_user` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
echo "10. plot_status_history\n";
execTable($db, $sql, 'plot_status_history', $results);

// ============================================================
// Summary
// ============================================================
echo "\n================================================================\n";
echo " Migration Summary\n";
echo "================================================================\n";
echo "Tables created:  " . count($results['created']) . "\n";
echo "Tables existed:  " . count($results['existed']) . "\n";
echo "Tables failed:   " . count($results['failed']) . "\n";
echo "Total:           " . (count($results['created']) + count($results['existed'])) . " / 10\n";

if (!empty($results['created'])) {
    echo "\nNew tables:\n";
    foreach ($results['created'] as $t) echo "  - $t\n";
}
if (!empty($results['failed'])) {
    echo "\nFailed tables:\n";
    foreach ($results['failed'] as $t) echo "  - $t\n";
    exit(1);
}

echo "\n✓ Module 1 migration complete.\n";
