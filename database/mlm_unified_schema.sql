-- ------------------------------------------------------------
-- APS Dream Home - Unified MLM Referral Schema (Phase 1)
-- ------------------------------------------------------------
-- This script defines the core tables required to unify registration,
-- referral management, and commission tracking across all user roles.
-- Execute in staging first:
--   mysql -u <user> -p apsdreamhome < database/mlm_unified_schema.sql
-- ------------------------------------------------------------

START TRANSACTION;

-- 1. MLM profiles (extends canonical users table)
CREATE TABLE IF NOT EXISTS `mlm_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `referral_code` VARCHAR(20) NOT NULL,
    `sponsor_user_id` BIGINT UNSIGNED DEFAULT NULL,
    `sponsor_code` VARCHAR(20) DEFAULT NULL,
    `user_type` ENUM('customer','agent','associate','builder','investor','admin') NOT NULL DEFAULT 'customer',
    `current_level` VARCHAR(50) NOT NULL DEFAULT 'Associate',
    `rank_updated_at` TIMESTAMP NULL DEFAULT NULL,
    `plan_mode` ENUM('rank','custom') NOT NULL DEFAULT 'rank',
    `total_team_size` INT UNSIGNED NOT NULL DEFAULT 0,
    `direct_referrals` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_commission` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `pending_commission` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `lifetime_sales` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `verification_status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_mlm_profiles_user` (`user_id`),
    UNIQUE KEY `uniq_mlm_profiles_ref_code` (`referral_code`),
    KEY `idx_mlm_profiles_sponsor` (`sponsor_user_id`),
    CONSTRAINT `fk_mlm_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mlm_profiles_sponsor` FOREIGN KEY (`sponsor_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8a. Payout batch approvals (multi-approver workflow)
CREATE TABLE IF NOT EXISTS `mlm_payout_batch_approvals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `approver_user_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_batch_approver` (`batch_id`,`approver_user_id`),
    KEY `idx_batch_approvals_batch` (`batch_id`),
    CONSTRAINT `fk_batch_approvals_batch` FOREIGN KEY (`batch_id`) REFERENCES `mlm_payout_batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_batch_approvals_user` FOREIGN KEY (`approver_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1b. Commission agreements for custom payout structures
CREATE TABLE IF NOT EXISTS `mlm_commission_agreements` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `property_id` INT UNSIGNED DEFAULT NULL,
    `commission_rate` DECIMAL(6,3) DEFAULT NULL,
    `flat_amount` DECIMAL(15,2) DEFAULT NULL,
    `valid_from` DATE DEFAULT NULL,
    `valid_to` DATE DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_commission_agreements_user` (`user_id`),
    KEY `idx_commission_agreements_property` (`property_id`),
    CONSTRAINT `fk_commission_agreements_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Network tree (materialised ancestor / descendant map)
CREATE TABLE IF NOT EXISTS `mlm_network_tree` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ancestor_user_id` BIGINT UNSIGNED NOT NULL,
    `descendant_user_id` BIGINT UNSIGNED NOT NULL,
    `level` TINYINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_network_relation` (`ancestor_user_id`, `descendant_user_id`),
    KEY `idx_network_level` (`level`),
    CONSTRAINT `fk_network_ancestor` FOREIGN KEY (`ancestor_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_network_descendant` FOREIGN KEY (`descendant_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Referral audit log (captures every signup path)
CREATE TABLE IF NOT EXISTS `mlm_referrals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `referrer_user_id` BIGINT UNSIGNED NOT NULL,
    `referred_user_id` BIGINT UNSIGNED NOT NULL,
    `referral_type` ENUM('customer','agent','associate','builder','investor','admin') NOT NULL,
    `channel` ENUM('direct_link','qr_code','admin_invite','event','other') DEFAULT 'direct_link',
    `commission_amount` DECIMAL(15,2) DEFAULT 0.00,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_referrals_referrer` (`referrer_user_id`),
    KEY `idx_referrals_referred` (`referred_user_id`),
    CONSTRAINT `fk_referrals_referrer` FOREIGN KEY (`referrer_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_referrals_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Commission ledger (unified across all bonus types)
CREATE TABLE IF NOT EXISTS `mlm_commission_ledger` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `beneficiary_user_id` BIGINT UNSIGNED NOT NULL,
    `source_user_id` BIGINT UNSIGNED NOT NULL,
    `commission_type` ENUM('referral','direct_sale','team_bonus','level_bonus','performance_bonus','special_reward') NOT NULL DEFAULT 'referral',
    `amount` DECIMAL(15,2) NOT NULL,
    `level` TINYINT UNSIGNED DEFAULT NULL,
    `property_id` INT DEFAULT NULL,
    `sale_amount` DECIMAL(15,2) DEFAULT NULL,
    `commission_percentage` DECIMAL(5,2) DEFAULT NULL,
    `status` ENUM('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
    `payout_batch_id` BIGINT UNSIGNED DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_commission_beneficiary` (`beneficiary_user_id`),
    KEY `idx_commission_source` (`source_user_id`),
    KEY `idx_commission_status` (`status`),
    KEY `idx_commission_level` (`level`),
    CONSTRAINT `fk_commission_beneficiary` FOREIGN KEY (`beneficiary_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_commission_source` FOREIGN KEY (`source_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Payout batches (groups commissions for finance team)
CREATE TABLE IF NOT EXISTS `mlm_payout_batches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `batch_reference` VARCHAR(40) NOT NULL,
    `processed_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
    `approved_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft','pending_approval','processing','completed','cancelled') NOT NULL DEFAULT 'draft',
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_records` INT UNSIGNED NOT NULL DEFAULT 0,
    `required_approvals` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `approval_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `processed_at` TIMESTAMP NULL DEFAULT NULL,
    `disbursement_reference` VARCHAR(100) DEFAULT NULL,
    `processed_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_payout_batch` (`batch_reference`),
    KEY `idx_payout_status` (`status`),
    KEY `idx_payout_approved_by` (`approved_by_user_id`),
    CONSTRAINT `fk_payout_processed_by` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_payout_approved_by` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Import audit (tracks legacy sponsor assignments and data corrections)
CREATE TABLE IF NOT EXISTS `mlm_import_audit` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `batch_reference` VARCHAR(50) DEFAULT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `sponsor_user_id` BIGINT UNSIGNED DEFAULT NULL,
    `referral_code` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('pending','success','skipped','error') NOT NULL DEFAULT 'pending',
    `message` TEXT DEFAULT NULL,
    `payload` JSON DEFAULT NULL,
    `processed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_import_audit_user` (`user_id`),
    KEY `idx_import_audit_sponsor` (`sponsor_user_id`),
    KEY `idx_import_audit_batch` (`batch_reference`),
    KEY `idx_import_audit_status` (`status`),
    CONSTRAINT `fk_import_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_import_audit_sponsor` FOREIGN KEY (`sponsor_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Payout batch items link individual commission entries
CREATE TABLE IF NOT EXISTS `mlm_payout_batch_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `commission_id` BIGINT UNSIGNED NOT NULL,
    `beneficiary_user_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending','approved','paid','failed') NOT NULL DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_batch_items_batch` (`batch_id`),
    KEY `idx_batch_items_commission` (`commission_id`),
    CONSTRAINT `fk_batch_items_batch` FOREIGN KEY (`batch_id`) REFERENCES `mlm_payout_batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_batch_items_commission` FOREIGN KEY (`commission_id`) REFERENCES `mlm_commission_ledger`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Notification log captures outbound alerts for auditing
CREATE TABLE IF NOT EXISTS `mlm_notification_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `channel` ENUM('email','whatsapp','sms','system') NOT NULL DEFAULT 'email',
    `type` VARCHAR(60) NOT NULL,
    `subject` VARCHAR(150) DEFAULT NULL,
    `message` TEXT DEFAULT NULL,
    `payload` JSON DEFAULT NULL,
    `status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    `error_message` TEXT DEFAULT NULL,
    `sent_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notification_user` (`user_id`),
    KEY `idx_notification_type` (`type`),
    KEY `idx_notification_channel_status` (`channel`,`status`),
    CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. MLM settings for thresholds and toggles
CREATE TABLE IF NOT EXISTS `mlm_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(120) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_mlm_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- ------------------------------------------------------------
-- Post-creation tasks (execute separately)
--   1. Populate referral codes for every existing user.
--   2. Migrate data from legacy tables (mlm_agents, mlm_tree, etc.).
--   3. Rebuild ancestor/descendant rows in mlm_network_tree.
-- ------------------------------------------------------------
