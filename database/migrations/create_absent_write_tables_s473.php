<?php
/**
 * Migration S473 Part 3: create the absent tables that the code WRITES to.
 *
 * WHY (S473):
 *   The schema ratchet reported 155 tables referenced by the code but
 *   absent from the schema.  90 of them are WRITTEN to (INSERT/UPDATE),
 *   so every one of those writes died with 1146 and the feature silently
 *   never worked.  This migration creates them with the columns the code
 *   actually references, types inferred from the column names and the
 *   bound values in the INSERT statements.
 *
 *   The 64 read-only references (SELECT only) are a separate, lower-
 *   impact batch - they already return empty through their try/catch.
 *
 *   The two renamed tables (`mlm_goal_progress` -> `goal_progress`,
 *   `failed_activity_logs_unified` -> `activity_logs_unified`) were
 *   handled by repointing the code, not by renaming a live table.
 *
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S473 Part 3: create the absent write-tables ===\n";

if (!function_exists('s474_tableExists')) {
    function s474_tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (s474_tableExists($pdo, 'admin_error_log')) {
    echo "  [=] admin_error_log already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_error_log` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id` INT UNSIGNED NULL,
    `error_type` VARCHAR(255) NULL,
    `context` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] admin_error_log created\n";
    } catch (\Throwable $e) {
        echo "  [!] admin_error_log FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'agent_executions')) {
    echo "  [=] agent_executions already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `agent_executions` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` INT UNSIGNED NULL,
    `agent_id` INT UNSIGNED NULL,
    `status` VARCHAR(50) NULL,
    `result` VARCHAR(255) NULL,
    `duration_ms` VARCHAR(255) NULL,
    `error_message` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] agent_executions created\n";
    } catch (\Throwable $e) {
        echo "  [!] agent_executions FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'agent_state')) {
    echo "  [=] agent_state already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `agent_state` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `agent_id` INT UNSIGNED NULL,
    `context` VARCHAR(255) NULL,
    `state_data` VARCHAR(255) NULL,
    `expires_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] agent_state created\n";
    } catch (\Throwable $e) {
        echo "  [!] agent_state FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'associate_invitations')) {
    echo "  [=] associate_invitations already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `associate_invitations` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` BIGINT UNSIGNED NULL,
    `sponsor_id` INT UNSIGNED NULL,
    `invitation_message` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `sent_at` VARCHAR(255) NULL,
    `expires_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] associate_invitations created\n";
    } catch (\Throwable $e) {
        echo "  [!] associate_invitations FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'coding_challenges')) {
    echo "  [=] coding_challenges already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `coding_challenges` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NULL,
    `slug` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `difficulty` VARCHAR(255) NULL,
    `category` VARCHAR(50) NULL,
    `problem_statement` VARCHAR(255) NULL,
    `input_format` VARCHAR(255) NULL,
    `output_format` VARCHAR(255) NULL,
    `constraints` VARCHAR(255) NULL,
    `sample_input` VARCHAR(255) NULL,
    `sample_output` VARCHAR(255) NULL,
    `explanation` VARCHAR(255) NULL,
    `starter_code` VARCHAR(255) NULL,
    `solution_code` VARCHAR(255) NULL,
    `time_limit_seconds` VARCHAR(255) NULL,
    `memory_limit_mb` VARCHAR(255) NULL,
    `points` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NULL,
    `created_by` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] coding_challenges created\n";
    } catch (\Throwable $e) {
        echo "  [!] coding_challenges FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'colony_land_costing')) {
    echo "  [=] colony_land_costing already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `colony_land_costing` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `colony_id` INT UNSIGNED NULL,
    `costing_label` VARCHAR(255) NULL,
    `total_land_sqft` VARCHAR(255) NULL,
    `land_purchase_rate` DECIMAL(15,2) NULL,
    `land_registry_cost` DECIMAL(15,2) NULL,
    `road_wastage_pct` VARCHAR(255) NULL,
    `drainage_wastage_pct` VARCHAR(255) NULL,
    `park_wastage_pct` VARCHAR(255) NULL,
    `other_wastage_pct` VARCHAR(255) NULL,
    `road_dev_cost_sqft` VARCHAR(255) NULL,
    `drainage_dev_cost_sqft` VARCHAR(255) NULL,
    `electricity_cost_sqft` VARCHAR(255) NULL,
    `water_pipeline_cost_sqft` VARCHAR(255) NULL,
    `boundary_wall_cost_sqft` VARCHAR(255) NULL,
    `other_dev_cost_sqft` VARCHAR(255) NULL,
    `legal_approval_cost` DECIMAL(15,2) NULL,
    `admin_overhead_pct` VARCHAR(255) NULL,
    `marketing_commission_pct` VARCHAR(255) NULL,
    `target_profit_pct` VARCHAR(255) NULL,
    `landing_cost_sqft` VARCHAR(255) NULL,
    `suggested_price_sqft` VARCHAR(255) NULL,
    `final_price_sqft` VARCHAR(255) NULL,
    `is_approved` TINYINT(1) NULL,
    `version` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_by` VARCHAR(255) NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] colony_land_costing created\n";
    } catch (\Throwable $e) {
        echo "  [!] colony_land_costing FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'colony_land_costing_items')) {
    echo "  [=] colony_land_costing_items already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `colony_land_costing_items` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `costing_id` INT UNSIGNED NULL,
    `item_type` VARCHAR(255) NULL,
    `item_label` VARCHAR(255) NULL,
    `quantity` VARCHAR(255) NULL,
    `unit` VARCHAR(255) NULL,
    `rate` DECIMAL(15,2) NULL,
    `amount` DECIMAL(15,2) NULL,
    `is_deduction` TINYINT(1) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] colony_land_costing_items created\n";
    } catch (\Throwable $e) {
        echo "  [!] colony_land_costing_items FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'commission_payout_commissions')) {
    echo "  [=] commission_payout_commissions already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `commission_payout_commissions` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payout_id` INT UNSIGNED NULL,
    `commission_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] commission_payout_commissions created\n";
    } catch (\Throwable $e) {
        echo "  [!] commission_payout_commissions FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'customer_alerts')) {
    echo "  [=] customer_alerts already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `customer_alerts` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` BIGINT UNSIGNED NULL,
    `property_type_id` INT UNSIGNED NULL,
    `city` VARCHAR(255) NULL,
    `state` VARCHAR(255) NULL,
    `min_price` DECIMAL(15,2) NULL,
    `max_price` DECIMAL(15,2) NULL,
    `min_bedrooms` VARCHAR(255) NULL,
    `max_bedrooms` VARCHAR(255) NULL,
    `alert_type` VARCHAR(255) NULL,
    `frequency` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] customer_alerts created\n";
    } catch (\Throwable $e) {
        echo "  [!] customer_alerts FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'deal_activities')) {
    echo "  [=] deal_activities already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `deal_activities` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `deal_id` INT UNSIGNED NULL,
    `activity_type` VARCHAR(255) NULL,
    `activity_title` VARCHAR(255) NULL,
    `activity_description` VARCHAR(255) NULL,
    `activity_date` DATETIME NULL,
    `created_by` VARCHAR(255) NULL,
    `duration_minutes` VARCHAR(255) NULL,
    `outcome` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] deal_activities created\n";
    } catch (\Throwable $e) {
        echo "  [!] deal_activities FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'document_locker')) {
    echo "  [=] document_locker already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `document_locker` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `document_type` VARCHAR(255) NULL,
    `document_name` VARCHAR(255) NULL,
    `file_path` VARCHAR(500) NULL,
    `file_size` VARCHAR(255) NULL,
    `mime_type` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `remarks` TEXT NULL,
    `uploaded_by` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] document_locker created\n";
    } catch (\Throwable $e) {
        echo "  [!] document_locker FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'document_qr_codes')) {
    echo "  [=] document_qr_codes already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `document_qr_codes` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT UNSIGNED NULL,
    `document_type` VARCHAR(255) NULL,
    `qr_code_path` VARCHAR(500) NULL,
    `qr_code_url` VARCHAR(500) NULL,
    `verification_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] document_qr_codes created\n";
    } catch (\Throwable $e) {
        echo "  [!] document_qr_codes FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'document_reviews')) {
    echo "  [=] document_reviews already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `document_reviews` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT UNSIGNED NULL,
    `reviewer_id` BIGINT UNSIGNED NULL,
    `review_status` VARCHAR(255) NULL,
    `comments` VARCHAR(255) NULL,
    `reviewed_at` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] document_reviews created\n";
    } catch (\Throwable $e) {
        echo "  [!] document_reviews FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'document_vault')) {
    echo "  [=] document_vault already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `document_vault` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `document_type` VARCHAR(255) NULL,
    `document_name` VARCHAR(255) NULL,
    `file_path` VARCHAR(500) NULL,
    `file_size` VARCHAR(255) NULL,
    `mime_type` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `reason` VARCHAR(50) NULL,
    `uploaded_by` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] document_vault created\n";
    } catch (\Throwable $e) {
        echo "  [!] document_vault FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'email_classifications')) {
    echo "  [=] email_classifications already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `email_classifications` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `subject` VARCHAR(255) NULL,
    `content_hash` VARCHAR(255) NULL,
    `category` VARCHAR(50) NULL,
    `confidence` VARCHAR(255) NULL,
    `priority` VARCHAR(50) NULL,
    `method` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] email_classifications created\n";
    } catch (\Throwable $e) {
        echo "  [!] email_classifications FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'email_routings')) {
    echo "  [=] email_routings already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `email_routings` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `category` VARCHAR(50) NULL,
    `assigned_to` VARCHAR(255) NULL,
    `team` VARCHAR(255) NULL,
    `reason` VARCHAR(50) NULL,
    `sla_hours` INT NULL,
    `priority` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] email_routings created\n";
    } catch (\Throwable $e) {
        echo "  [!] email_routings FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'employee_salary_structures')) {
    echo "  [=] employee_salary_structures already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `employee_salary_structures` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NULL,
    `ctc` VARCHAR(255) NULL,
    `gross_salary` DECIMAL(15,2) NULL,
    `components` VARCHAR(255) NULL,
    `effective_from` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] employee_salary_structures created\n";
    } catch (\Throwable $e) {
        echo "  [!] employee_salary_structures FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'escalation_rules')) {
    echo "  [=] escalation_rules already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `escalation_rules` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL,
    `conditions` VARCHAR(255) NULL,
    `actions` VARCHAR(255) NULL,
    `enabled` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] escalation_rules created\n";
    } catch (\Throwable $e) {
        echo "  [!] escalation_rules FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'farmer_activities')) {
    echo "  [=] farmer_activities already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `farmer_activities` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `farmer_id` INT UNSIGNED NULL,
    `activity_type` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `data` TEXT NULL,
    `created_by` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] farmer_activities created\n";
    } catch (\Throwable $e) {
        echo "  [!] farmer_activities FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'farmer_commissions')) {
    echo "  [=] farmer_commissions already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `farmer_commissions` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `farmer_id` INT UNSIGNED NULL,
    `commission_type` VARCHAR(255) NULL,
    `amount` DECIMAL(15,2) NULL,
    `commission_rate` DECIMAL(15,2) NULL,
    `reference_id` INT UNSIGNED NULL,
    `reference_data` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] farmer_commissions created\n";
    } catch (\Throwable $e) {
        echo "  [!] farmer_commissions FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'feature_usage')) {
    echo "  [=] feature_usage already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `feature_usage` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `feature_type` VARCHAR(255) NULL,
    `usage_data` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] feature_usage created\n";
    } catch (\Throwable $e) {
        echo "  [!] feature_usage FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'forecast_results')) {
    echo "  [=] forecast_results already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `forecast_results` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `metric_name` VARCHAR(255) NULL,
    `method` VARCHAR(255) NULL,
    `periods` VARCHAR(255) NULL,
    `predictions` VARCHAR(255) NULL,
    `r_squared` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] forecast_results created\n";
    } catch (\Throwable $e) {
        echo "  [!] forecast_results FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'kyc_verification')) {
    echo "  [=] kyc_verification already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `kyc_verification` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `associate_id` INT UNSIGNED NULL,
    `aadhar_doc` VARCHAR(255) NULL,
    `pan_doc` VARCHAR(255) NULL,
    `address_doc` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `submitted_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] kyc_verification created\n";
    } catch (\Throwable $e) {
        echo "  [!] kyc_verification FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'lead_assignment_log')) {
    echo "  [=] lead_assignment_log already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `lead_assignment_log` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NULL,
    `associate_id` INT UNSIGNED NULL,
    `assigned_by` VARCHAR(255) NULL,
    `action` VARCHAR(50) NULL,
    `old_associate_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] lead_assignment_log created\n";
    } catch (\Throwable $e) {
        echo "  [!] lead_assignment_log FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'lead_scoring_history')) {
    echo "  [=] lead_scoring_history already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `lead_scoring_history` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NULL,
    `rule_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NULL,
    `points_change` VARCHAR(255) NULL,
    `old_score` VARCHAR(255) NULL,
    `new_score` VARCHAR(255) NULL,
    `reason` VARCHAR(50) NULL,
    `applied_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] lead_scoring_history created\n";
    } catch (\Throwable $e) {
        echo "  [!] lead_scoring_history FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'legal_deadlines')) {
    echo "  [=] legal_deadlines already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `legal_deadlines` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `legal_type` VARCHAR(255) NULL,
    `deadline_date` DATETIME NULL,
    `assigned_to` VARCHAR(255) NULL,
    `status` VARCHAR(50) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] legal_deadlines created\n";
    } catch (\Throwable $e) {
        echo "  [!] legal_deadlines FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'marketplace_apps')) {
    echo "  [=] marketplace_apps already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `marketplace_apps` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `app_name` VARCHAR(255) NULL,
    `provider` VARCHAR(255) NULL,
    `app_url` VARCHAR(500) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] marketplace_apps created\n";
    } catch (\Throwable $e) {
        echo "  [!] marketplace_apps FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'media_galleries')) {
    echo "  [=] media_galleries already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `media_galleries` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL,
    `options` VARCHAR(255) NULL,
    `created_by` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] media_galleries created\n";
    } catch (\Throwable $e) {
        echo "  [!] media_galleries FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'media_gallery_items')) {
    echo "  [=] media_gallery_items already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `media_gallery_items` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `gallery_id` INT UNSIGNED NULL,
    `media_id` INT UNSIGNED NULL,
    `position` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] media_gallery_items created\n";
    } catch (\Throwable $e) {
        echo "  [!] media_gallery_items FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'message_queue')) {
    echo "  [=] message_queue already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `message_queue` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` INT UNSIGNED NULL,
    `message_data` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] message_queue created\n";
    } catch (\Throwable $e) {
        echo "  [!] message_queue FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'monthly_salary_payments')) {
    echo "  [=] monthly_salary_payments already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `monthly_salary_payments` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NULL,
    `month` VARCHAR(255) NULL,
    `year` VARCHAR(255) NULL,
    `gross_salary` DECIMAL(15,2) NULL,
    `total_deductions` VARCHAR(255) NULL,
    `net_salary` DECIMAL(15,2) NULL,
    `working_days` INT NULL,
    `present_days` INT NULL,
    `leave_days` INT NULL,
    `overtime_hours` INT NULL,
    `overtime_amount` DECIMAL(15,2) NULL,
    `payment_status` VARCHAR(255) NULL,
    `processed_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] monthly_salary_payments created\n";
    } catch (\Throwable $e) {
        echo "  [!] monthly_salary_payments FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'offboarding_template_items')) {
    echo "  [=] offboarding_template_items already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `offboarding_template_items` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NULL,
    `template_id` INT UNSIGNED NULL,
    `item_name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `category` VARCHAR(50) NULL,
    `is_mandatory` TINYINT(1) NULL,
    `assigned_to_role` VARCHAR(255) NULL,
    `due_days` INT NULL,
    `sort_order` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] offboarding_template_items created\n";
    } catch (\Throwable $e) {
        echo "  [!] offboarding_template_items FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'offboarding_templates')) {
    echo "  [=] offboarding_templates already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `offboarding_templates` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] offboarding_templates created\n";
    } catch (\Throwable $e) {
        echo "  [!] offboarding_templates FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'onboarding_template_items')) {
    echo "  [=] onboarding_template_items already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `onboarding_template_items` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NULL,
    `template_id` INT UNSIGNED NULL,
    `item_name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `category` VARCHAR(50) NULL,
    `is_mandatory` TINYINT(1) NULL,
    `assigned_to_role` VARCHAR(255) NULL,
    `due_days` INT NULL,
    `sort_order` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] onboarding_template_items created\n";
    } catch (\Throwable $e) {
        echo "  [!] onboarding_template_items FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'onboarding_templates')) {
    echo "  [=] onboarding_templates already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `onboarding_templates` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] onboarding_templates created\n";
    } catch (\Throwable $e) {
        echo "  [!] onboarding_templates FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'payment_installments')) {
    echo "  [=] payment_installments already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_installments` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `schedule_id` INT UNSIGNED NULL,
    `installment_number` VARCHAR(255) NULL,
    `milestone_id` INT UNSIGNED NULL,
    `amount` DECIMAL(15,2) NULL,
    `due_date` DATETIME NULL,
    `status` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] payment_installments created\n";
    } catch (\Throwable $e) {
        echo "  [!] payment_installments FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'payment_settings')) {
    echo "  [=] payment_settings already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_settings` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(255) NULL,
    `setting_value` DECIMAL(15,2) NULL,
    `setting_type` VARCHAR(255) NULL,
    `setting_category` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] payment_settings created\n";
    } catch (\Throwable $e) {
        echo "  [!] payment_settings FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'performance_config')) {
    echo "  [=] performance_config already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `performance_config` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `config_key` VARCHAR(255) NULL,
    `config_value` DECIMAL(15,2) NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] performance_config created\n";
    } catch (\Throwable $e) {
        echo "  [!] performance_config FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'performance_log')) {
    echo "  [=] performance_log already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `performance_log` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `action` VARCHAR(50) NULL,
    `duration` VARCHAR(255) NULL,
    `details` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] performance_log created\n";
    } catch (\Throwable $e) {
        echo "  [!] performance_log FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'php_benchmark_results')) {
    echo "  [=] php_benchmark_results already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `php_benchmark_results` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `memory_usage` VARCHAR(255) NULL,
    `cpu_time` VARCHAR(255) NULL,
    `io_time` VARCHAR(255) NULL,
    `total_time` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] php_benchmark_results created\n";
    } catch (\Throwable $e) {
        echo "  [!] php_benchmark_results FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'php_optimization_log')) {
    echo "  [=] php_optimization_log already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `php_optimization_log` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `optimization_type` VARCHAR(255) NULL,
    `config_key` VARCHAR(255) NULL,
    `old_value` DECIMAL(15,2) NULL,
    `new_value` DECIMAL(15,2) NULL,
    `status` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] php_optimization_log created\n";
    } catch (\Throwable $e) {
        echo "  [!] php_optimization_log FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'plot_emi_schedule')) {
    echo "  [=] plot_emi_schedule already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `plot_emi_schedule` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` INT UNSIGNED NULL,
    `installment_number` VARCHAR(255) NULL,
    `due_date` DATETIME NULL,
    `amount` DECIMAL(15,2) NULL,
    `status` VARCHAR(50) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] plot_emi_schedule created\n";
    } catch (\Throwable $e) {
        echo "  [!] plot_emi_schedule FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'plot_payments')) {
    echo "  [=] plot_payments already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `plot_payments` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` INT UNSIGNED NULL,
    `amount` DECIMAL(15,2) NULL,
    `payment_mode` VARCHAR(255) NULL,
    `payment_date` DATETIME NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] plot_payments created\n";
    } catch (\Throwable $e) {
        echo "  [!] plot_payments FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'plot_status_history')) {
    echo "  [=] plot_status_history already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `plot_status_history` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `plot_id` INT UNSIGNED NULL,
    `old_status` VARCHAR(255) NULL,
    `new_status` VARCHAR(255) NULL,
    `changed_by` VARCHAR(255) NULL,
    `change_reason` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] plot_status_history created\n";
    } catch (\Throwable $e) {
        echo "  [!] plot_status_history FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'policy_violations')) {
    echo "  [=] policy_violations already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `policy_violations` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `policy_id` INT UNSIGNED NULL,
    `violation_type` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `severity` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] policy_violations created\n";
    } catch (\Throwable $e) {
        echo "  [!] policy_violations FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'property_ai_tags')) {
    echo "  [=] property_ai_tags already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `property_ai_tags` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` INT UNSIGNED NULL,
    `tag` VARCHAR(255) NULL,
    `confidence` VARCHAR(255) NULL,
    `source` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] property_ai_tags created\n";
    } catch (\Throwable $e) {
        echo "  [!] property_ai_tags FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'review_helpful_votes')) {
    echo "  [=] review_helpful_votes already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `review_helpful_votes` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `review_id` INT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] review_helpful_votes created\n";
    } catch (\Throwable $e) {
        echo "  [!] review_helpful_votes FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'royalty_pool_distributions')) {
    echo "  [=] royalty_pool_distributions already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `royalty_pool_distributions` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `month_year` VARCHAR(255) NULL,
    `share_pct` VARCHAR(255) NULL,
    `amount` DECIMAL(15,2) NULL,
    `distributed_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] royalty_pool_distributions created\n";
    } catch (\Throwable $e) {
        echo "  [!] royalty_pool_distributions FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'security_configurations')) {
    echo "  [=] security_configurations already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `security_configurations` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category` VARCHAR(50) NULL,
    `config_key` VARCHAR(255) NULL,
    `config_value` DECIMAL(15,2) NULL,
    `is_sensitive` TINYINT(1) NULL,
    `updated_by` VARCHAR(255) NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] security_configurations created\n";
    } catch (\Throwable $e) {
        echo "  [!] security_configurations FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'security_threats')) {
    echo "  [=] security_threats already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `security_threats` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `threat_type` VARCHAR(255) NULL,
    `threat_data` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] security_threats created\n";
    } catch (\Throwable $e) {
        echo "  [!] security_threats FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'site_visit_requests')) {
    echo "  [=] site_visit_requests already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_visit_requests` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `user_name` VARCHAR(255) NULL,
    `user_email` VARCHAR(255) NULL,
    `user_phone` VARCHAR(255) NULL,
    `property_id` INT UNSIGNED NULL,
    `preferred_date` DATETIME NULL,
    `preferred_time` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `status` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] site_visit_requests created\n";
    } catch (\Throwable $e) {
        echo "  [!] site_visit_requests FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'slow_queries_log')) {
    echo "  [=] slow_queries_log already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `slow_queries_log` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `query_sql` VARCHAR(255) NULL,
    `query_params` VARCHAR(255) NULL,
    `execution_time` VARCHAR(255) NULL,
    `memory_usage` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] slow_queries_log created\n";
    } catch (\Throwable $e) {
        echo "  [!] slow_queries_log FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'system_metrics')) {
    echo "  [=] system_metrics already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `system_metrics` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `timestamp` VARCHAR(255) NULL,
    `memory_current` VARCHAR(255) NULL,
    `memory_peak` VARCHAR(255) NULL,
    `cpu_load_1` VARCHAR(255) NULL,
    `cpu_load_5` VARCHAR(255) NULL,
    `cpu_load_15` VARCHAR(255) NULL,
    `disk_total` DECIMAL(15,2) NULL,
    `disk_used` VARCHAR(255) NULL,
    `disk_free` VARCHAR(255) NULL,
    `server_uptime` VARCHAR(255) NULL,
    `db_connections` VARCHAR(255) NULL,
    `active_sessions` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] system_metrics created\n";
    } catch (\Throwable $e) {
        echo "  [!] system_metrics FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'task_queue')) {
    echo "  [=] task_queue already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `task_queue` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id` INT UNSIGNED NULL,
    `queue_name` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] task_queue created\n";
    } catch (\Throwable $e) {
        echo "  [!] task_queue FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'tour_hotspots')) {
    echo "  [=] tour_hotspots already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tour_hotspots` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tour_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `x_position` VARCHAR(255) NULL,
    `y_position` VARCHAR(255) NULL,
    `hotspot_type` VARCHAR(50) NULL,
    `content` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] tour_hotspots created\n";
    } catch (\Throwable $e) {
        echo "  [!] tour_hotspots FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'training_lesson_progress')) {
    echo "  [=] training_lesson_progress already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `training_lesson_progress` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `enrollment_id` INT UNSIGNED NULL,
    `lesson_id` INT UNSIGNED NULL,
    `completed_at` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] training_lesson_progress created\n";
    } catch (\Throwable $e) {
        echo "  [!] training_lesson_progress FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'training_lessons')) {
    echo "  [=] training_lessons already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `training_lessons` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `content_type` VARCHAR(255) NULL,
    `content_url` VARCHAR(500) NULL,
    `content_text` VARCHAR(255) NULL,
    `duration_minutes` VARCHAR(255) NULL,
    `sort_order` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] training_lessons created\n";
    } catch (\Throwable $e) {
        echo "  [!] training_lessons FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'training_modules')) {
    echo "  [=] training_modules already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `training_modules` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `sort_order` VARCHAR(255) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `order_index` VARCHAR(255) NULL,
    `content_type` VARCHAR(255) NULL,
    `content_url` VARCHAR(500) NULL,
    `duration_minutes` VARCHAR(255) NULL,
    `is_required` TINYINT(1) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] training_modules created\n";
    } catch (\Throwable $e) {
        echo "  [!] training_modules FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'training_quiz_attempts')) {
    echo "  [=] training_quiz_attempts already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `training_quiz_attempts` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `enrollment_id` INT UNSIGNED NULL,
    `lesson_id` INT UNSIGNED NULL,
    `score` VARCHAR(255) NULL,
    `total` DECIMAL(15,2) NULL,
    `percentage` VARCHAR(255) NULL,
    `passed` VARCHAR(255) NULL,
    `answers` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] training_quiz_attempts created\n";
    } catch (\Throwable $e) {
        echo "  [!] training_quiz_attempts FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'training_quizzes')) {
    echo "  [=] training_quizzes already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `training_quizzes` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lesson_id` INT UNSIGNED NULL,
    `question` VARCHAR(255) NULL,
    `options` VARCHAR(255) NULL,
    `correct_answer` VARCHAR(255) NULL,
    `points` VARCHAR(255) NULL,
    `sort_order` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] training_quizzes created\n";
    } catch (\Throwable $e) {
        echo "  [!] training_quizzes FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'typing_indicators')) {
    echo "  [=] typing_indicators already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `typing_indicators` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` INT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] typing_indicators created\n";
    } catch (\Throwable $e) {
        echo "  [!] typing_indicators FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'user_admin_notes')) {
    echo "  [=] user_admin_notes already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_admin_notes` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `admin_id` INT UNSIGNED NULL,
    `note` VARCHAR(255) NULL,
    `tags` VARCHAR(255) NULL,
    `is_important` TINYINT(1) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] user_admin_notes created\n";
    } catch (\Throwable $e) {
        echo "  [!] user_admin_notes FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'user_ai_provider')) {
    echo "  [=] user_ai_provider already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_ai_provider` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `tenant_id` INT UNSIGNED NULL,
    `ai_provider` VARCHAR(255) NULL,
    `ai_api_key` VARCHAR(255) NULL,
    `ai_model` VARCHAR(255) NULL,
    `updated_at` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] user_ai_provider created\n";
    } catch (\Throwable $e) {
        echo "  [!] user_ai_provider FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'user_quick_actions')) {
    echo "  [=] user_quick_actions already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_quick_actions` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `user_type` VARCHAR(50) NULL,
    `action_name` VARCHAR(255) NULL,
    `action_icon` VARCHAR(255) NULL,
    `action_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] user_quick_actions created\n";
    } catch (\Throwable $e) {
        echo "  [!] user_quick_actions FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'user_theme_preferences')) {
    echo "  [=] user_theme_preferences already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_theme_preferences` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `user_type` VARCHAR(50) NULL,
    `is_dark_mode` TINYINT(1) NULL,
    `theme_preset` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] user_theme_preferences created\n";
    } catch (\Throwable $e) {
        echo "  [!] user_theme_preferences FAILED: " . $e->getMessage() . "\n";
    }
}

if (s474_tableExists($pdo, 'visit_reminders')) {
    echo "  [=] visit_reminders already exists - skipped\n";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `visit_reminders` (\n    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `visit_id` INT UNSIGNED NULL,
    `reminder_type` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "  [+] visit_reminders created\n";
    } catch (\Throwable $e) {
        echo "  [!] visit_reminders FAILED: " . $e->getMessage() . "\n";
    }
}

echo "=== MIGRATION S473 PART 3 COMPLETE ===\n";