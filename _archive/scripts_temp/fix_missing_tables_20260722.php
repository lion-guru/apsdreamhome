<?php
/**
 * Fix missing DB tables and columns identified in audit (2026-07-22)
 * Run: php scripts/fix_missing_tables.php
 */

$basePath = dirname(__DIR__);
require_once $basePath . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

echo "=== APS Dream Home â€” Missing Tables/Columns Fix ===\n\n";

// â”€â”€â”€ 1. Create crm_lead_scores_history â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "1. crm_lead_scores_history...\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `crm_lead_scores_history` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `old_score` INT(11) DEFAULT 0,
        `new_score` INT(11) DEFAULT 0,
        `score_factors` JSON DEFAULT NULL,
        `scored_by` ENUM('system','admin','ai') DEFAULT 'system',
        `reason` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_score_history_lead` (`lead_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "   âœ“ Created\n";
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ 2. Create crm_lead_sources_extended â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "2. crm_lead_sources_extended...\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `crm_lead_sources_extended` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `campaign_id` INT(11) DEFAULT NULL,
        `form_id` INT(11) DEFAULT NULL,
        `source_type` ENUM('website','social_media','google_ads','facebook_ads','referral','walk_in','call_in','event','whatsapp','other') DEFAULT 'website',
        `source_detail` VARCHAR(200) DEFAULT NULL,
        `medium` VARCHAR(100) DEFAULT NULL,
        `utm_source` VARCHAR(100) DEFAULT NULL,
        `utm_medium` VARCHAR(100) DEFAULT NULL,
        `utm_campaign` VARCHAR(100) DEFAULT NULL,
        `utm_term` VARCHAR(100) DEFAULT NULL,
        `utm_content` VARCHAR(100) DEFAULT NULL,
        `gclid` VARCHAR(200) DEFAULT NULL,
        `fbclid` VARCHAR(200) DEFAULT NULL,
        `landing_page` VARCHAR(500) DEFAULT NULL,
        `referrer` VARCHAR(500) DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `city` VARCHAR(100) DEFAULT NULL,
        `state` VARCHAR(100) DEFAULT NULL,
        `country` VARCHAR(50) DEFAULT NULL,
        `device` VARCHAR(100) DEFAULT NULL,
        `browser` VARCHAR(100) DEFAULT NULL,
        `os` VARCHAR(100) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_source_lead` (`lead_id`),
        KEY `idx_crm_source_type` (`source_type`),
        KEY `idx_crm_source_campaign` (`campaign_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "   âœ“ Created\n";
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ 3. Check leads table for source_detail column â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "3. leads.source_detail column...\n";
try {
    $cols = $db->query("SHOW COLUMNS FROM leads LIKE 'source_detail'")->fetch();
    if ($cols) {
        echo "   âœ“ Already exists\n";
    } else {
        $db->exec("ALTER TABLE `leads` ADD COLUMN `source_detail` VARCHAR(200) DEFAULT NULL AFTER `source`");
        echo "   âœ“ Added source_detail column\n";
    }
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ 4. Check loan_documents for signed_by_customer / signed_at â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "4. loan_documents.signed_by_customer + signed_at columns...\n";
try {
    $hasSigned = $db->query("SHOW COLUMNS FROM loan_documents LIKE 'signed_by_customer'")->fetch();
    $hasSignedAt = $db->query("SHOW COLUMNS FROM loan_documents LIKE 'signed_at'")->fetch();
    if ($hasSigned && $hasSignedAt) {
        echo "   âœ“ Already exists\n";
    } else {
        if (!$hasSigned) {
            $db->exec("ALTER TABLE `loan_documents` ADD COLUMN `signed_by_customer` TINYINT(1) NOT NULL DEFAULT 0");
            echo "   âœ“ Added signed_by_customer\n";
        }
        if (!$hasSignedAt) {
            $db->exec("ALTER TABLE `loan_documents` ADD COLUMN `signed_at` DATETIME DEFAULT NULL");
            echo "   âœ“ Added signed_at\n";
        }
    }
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ 5. Check booking_document_signatures table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "5. booking_document_signatures table...\n";
try {
    $exists = $db->query("SHOW TABLES LIKE 'booking_document_signatures'")->fetch();
    if ($exists) {
        echo "   âœ“ Already exists\n";
    } else {
        $db->exec("CREATE TABLE IF NOT EXISTS `booking_document_signatures` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `document_id` INT(11) NOT NULL,
            `booking_id` INT(11) NOT NULL,
            `customer_id` INT(11) DEFAULT NULL,
            `signature_data` LONGTEXT DEFAULT NULL,
            `signature_type` ENUM('digital','wet','aadhaar') DEFAULT 'digital',
            `signed_at` DATETIME NOT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(500) DEFAULT NULL,
            `video_consent` TINYINT(1) NOT NULL DEFAULT 0,
            `video_url` VARCHAR(500) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_doc_booking` (`document_id`, `booking_id`),
            KEY `idx_bds_booking` (`booking_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "   âœ“ Created\n";
    }
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ 6. Check search_history table exists â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "6. search_history table...\n";
try {
    $exists = $db->query("SHOW TABLES LIKE 'search_history'")->fetch();
    if ($exists) {
        echo "   âœ“ Already exists\n";
    } else {
        $db->exec("CREATE TABLE IF NOT EXISTS `search_history` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) DEFAULT NULL,
            `user_role` VARCHAR(50) DEFAULT NULL,
            `entity_type` VARCHAR(50) DEFAULT 'property',
            `search_term` VARCHAR(255) DEFAULT NULL,
            `filters` JSON DEFAULT NULL,
            `results_count` INT(11) DEFAULT 0,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_sh_user` (`user_id`),
            KEY `idx_sh_term` (`search_term`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "   âœ“ Created\n";
    }
} catch (\Exception $e) {
    echo "   âœ— " . $e->getMessage() . "\n";
}

// â”€â”€â”€ Summary â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "\n=== Done ===\n";

// Verify
$tables = ['crm_lead_scores_history', 'crm_lead_sources_extended', 'booking_document_signatures', 'search_history'];
echo "\nVerification:\n";
foreach ($tables as $t) {
    $r = $db->query("SHOW TABLES LIKE '$t'");
    echo "  $t: " . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
}
echo "\n";?>