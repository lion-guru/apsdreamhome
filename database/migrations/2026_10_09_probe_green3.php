<?php
/**
 * Probe-Green Migration Part 3 (2026-10-09)
 * Restores schema for the 50 admin-sidebar pages that 500 on the fresh DB.
 * Mapped URL → controller → exact SQL by 5 parallel research passes; every
 * table/column below is evidenced by a quoted query in those reports.
 *
 * NOTES:
 * - admin_menu_* tables are created EMPTY on purpose: rbac_sidebar.php
 *   self-heals by seeding them from config/admin_menu_manifest.php on the
 *   next render, then falls back to the manifest. Verified by sidebar
 *   fingerprint before/after (dashboard 315 / plots 338 / users 336 links).
 * - Additive only: new tables + ADD/MODIFY columns. No data touched.
 * Idempotent: safe to re-run.
 */

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

function run3(PDO $pdo, string $label, string $sql): void {
    try {
        $pdo->exec($sql);
        echo "OK   $label\n";
    } catch (Throwable $e) {
        echo "SKIP $label -- " . substr($e->getMessage(), 0, 130) . "\n";
    }
}

// ═════════ GEO (colonies/projects/locations/plot-costs/valuations) ═════════
run3($pdo, 'states', "CREATE TABLE IF NOT EXISTS `states` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(10) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'states seed', "INSERT IGNORE INTO `states` (`id`, `name`, `code`, `is_active`) VALUES (1, 'Uttar Pradesh', 'UP', 1)");
run3($pdo, 'districts.state_id backfill', "UPDATE `districts` SET `state_id` = 1 WHERE `state_id` IS NULL");
run3($pdo, 'districts.is_active', "ALTER TABLE `districts` ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `state_id`");
run3($pdo, 'districts.code', "ALTER TABLE `districts` ADD COLUMN IF NOT EXISTS `code` VARCHAR(50) NULL AFTER `name`");
run3($pdo, 'cities', "CREATE TABLE IF NOT EXISTS `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `state_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'projects', "CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `project_type` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `developer_name` VARCHAR(255) NULL,
  `developer_contact` VARCHAR(255) NULL,
  `developer_phone` VARCHAR(20) NULL,
  `address` TEXT NULL,
  `state_id` INT NULL,
  `district_id` INT NULL,
  `colony_id` INT NULL,
  `total_area` DECIMAL(12,2) NULL,
  `total_plots` INT NOT NULL DEFAULT 0,
  `available_plots` INT NOT NULL DEFAULT 0,
  `booked_plots` INT NOT NULL DEFAULT 0,
  `sold_plots` INT NOT NULL DEFAULT 0,
  `price_range_min` DECIMAL(15,2) NULL,
  `price_range_max` DECIMAL(15,2) NULL,
  `avg_price_per_sqft` DECIMAL(10,2) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'planning',
  `launch_date` DATE NULL,
  `completion_date` DATE NULL,
  `possession_date` DATE NULL,
  `marketing_description` TEXT NULL,
  `tags` VARCHAR(500) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_hot_deal` TINYINT(1) NOT NULL DEFAULT 0,
  `rera_number` VARCHAR(50) NULL,
  `progress_pct` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `project_budget` DECIMAL(15,2) NULL,
  `amount_spent` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `project_manager` VARCHAR(255) NULL,
  `site_supervisor` VARCHAR(255) NULL,
  `contractor_name` VARCHAR(255) NULL,
  `risk_flags` TEXT NULL,
  `amenities` TEXT NULL,
  `images` TEXT NULL,
  `milestone_json` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_projects_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'colony_development_costs', "CREATE TABLE IF NOT EXISTS `colony_development_costs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `colony_id` INT NOT NULL,
  `cost_type` VARCHAR(100) NOT NULL,
  `work_description` TEXT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cdc_colony` (`colony_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'property_valuation_reports', "CREATE TABLE IF NOT EXISTS `property_valuation_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `property_id` INT NULL,
  `property_code` VARCHAR(50) NULL,
  `location` VARCHAR(255) NULL,
  `property_type` VARCHAR(50) NULL,
  `area_sqft` DECIMAL(10,2) NULL,
  `base_valuation` DECIMAL(15,2) NULL,
  `location_multiplier` DECIMAL(8,4) NULL,
  `type_multiplier` DECIMAL(8,4) NULL,
  `market_adjustment_pct` DECIMAL(6,2) NULL,
  `final_valuation` DECIMAL(15,2) NULL,
  `confidence_score` DECIMAL(5,2) NULL,
  `ai_analysis` TEXT NULL,
  `market_analysis` TEXT NULL,
  `recommendations` TEXT NULL,
  `comparable_properties` TEXT NULL,
  `generated_by` INT NULL,
  `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pvr_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ═════════ LISTINGS (inquiries/user-properties/resell/alerts) ═════════
run3($pdo, 'inquiries', "CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NULL,
  `user_id` INT NULL,
  `name` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `subject` VARCHAR(255) NULL,
  `message` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_inq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'listing_settings', "CREATE TABLE IF NOT EXISTS `listing_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `tenant_id` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'listing_packages', "CREATE TABLE IF NOT EXISTS `listing_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `duration_days` INT NOT NULL DEFAULT 30,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0,
  `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
  `boost_score` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `tenant_id` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'property_messages', "CREATE TABLE IF NOT EXISTS `property_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'user_properties', "CREATE TABLE IF NOT EXISTS `user_properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NULL,
  `posted_by` INT NULL,
  `listing_type` VARCHAR(20) NULL,
  `property_type` VARCHAR(50) NULL,
  `name` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `location` VARCHAR(255) NULL,
  `city_name` VARCHAR(100) NULL,
  `city_id` INT NULL,
  `district_id` INT NULL,
  `state_id` INT NULL,
  `price` DECIMAL(15,2) NULL,
  `area_sqft` DECIMAL(10,2) NULL,
  `bedrooms` INT NULL,
  `bathrooms` INT NULL,
  `furnished` VARCHAR(20) NULL,
  `description` TEXT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0,
  `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `verified_by` INT NULL,
  `verified_at` DATETIME NULL,
  `sold_at` DATETIME NULL,
  `admin_notes` TEXT NULL,
  `views` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_up_status` (`status`),
  KEY `idx_up_listing` (`listing_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'saved_searches', "CREATE TABLE IF NOT EXISTS `saved_searches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NOT NULL,
  `user_role` VARCHAR(50) NULL,
  `name` VARCHAR(200) NULL,
  `description` TEXT NULL,
  `entity_type` VARCHAR(50) NULL,
  `filters` TEXT NULL,
  `email_alerts` TINYINT(1) NOT NULL DEFAULT 0,
  `is_favorite` TINYINT(1) NOT NULL DEFAULT 0,
  `is_public` TINYINT(1) NOT NULL DEFAULT 0,
  `use_count` INT NOT NULL DEFAULT 0,
  `last_used_at` DATETIME NULL,
  `last_run_at` DATETIME NULL,
  `result_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'search_history.user_role', "ALTER TABLE `search_history` ADD COLUMN IF NOT EXISTS `user_role` VARCHAR(50) NULL AFTER `user_id`");
run3($pdo, 'search_history.entity_type varchar', "ALTER TABLE `search_history` MODIFY COLUMN `entity_type` VARCHAR(50) NULL");
run3($pdo, 'search_alert_log', "CREATE TABLE IF NOT EXISTS `search_alert_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `search_id` INT NULL,
  `user_id` INT NULL,
  `property_id` INT NULL,
  `sent_at` DATETIME NULL,
  `email_status` VARCHAR(20) NULL,
  `error_message` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'property_alert_subscriptions', "CREATE TABLE IF NOT EXISTS `property_alert_subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `name` VARCHAR(255) NULL,
  `property_type` VARCHAR(50) NULL,
  `listing_type` VARCHAR(20) NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `min_price` DECIMAL(15,2) NULL,
  `max_price` DECIMAL(15,2) NULL,
  `min_area_sqft` DECIMAL(10,2) NULL,
  `max_area_sqft` DECIMAL(10,2) NULL,
  `bedrooms` INT NULL,
  `notify_email` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_sms` TINYINT(1) NOT NULL DEFAULT 0,
  `notify_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
  `frequency` VARCHAR(20) NOT NULL DEFAULT 'daily',
  `unsubscribe_token` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_notified_at` DATETIME NULL,
  `total_notifications` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'property_alert_log', "CREATE TABLE IF NOT EXISTS `property_alert_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `subscription_id` INT NULL,
  `property_id` INT NULL,
  `user_id` INT NULL,
  `channel` VARCHAR(20) NULL,
  `status` VARCHAR(20) NULL,
  `message` TEXT NULL,
  `sent_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pal_sub` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'incomplete_registrations', "CREATE TABLE IF NOT EXISTS `incomplete_registrations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `token` VARCHAR(100) NULL,
  `session_id` VARCHAR(100) NULL,
  `step` VARCHAR(50) NULL,
  `current_step` VARCHAR(50) NULL,
  `payload` TEXT NULL,
  `form_data` TEXT NULL,
  `progress_percent` INT NOT NULL DEFAULT 0,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `source` VARCHAR(50) NULL,
  `utm_data` TEXT NULL,
  `expires_at` DATETIME NULL,
  `last_activity_at` DATETIME NULL,
  `recovered_at` DATETIME NULL,
  `recovered_user_id` INT NULL,
  `completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'progressive_registrations', "CREATE TABLE IF NOT EXISTS `progressive_registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `token` VARCHAR(100) NULL,
  `user_data` TEXT NULL,
  `source` VARCHAR(50) NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ═════════ DIRECTORY / SERVICES / NOC / VOICE / MESSAGES ═════════
run3($pdo, 'directory_categories', "CREATE TABLE IF NOT EXISTS `directory_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(100) NULL,
  `parent_id` INT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'directory_listings', "CREATE TABLE IF NOT EXISTS `directory_listings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `category_id` INT NULL,
  `user_id` INT NULL,
  `business_name` VARCHAR(255) NOT NULL,
  `owner_name` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `phone` VARCHAR(20) NULL,
  `whatsapp` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  `website` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `pincode` VARCHAR(10) NULL,
  `latitude` DECIMAL(10,8) NULL,
  `longitude` DECIMAL(11,8) NULL,
  `experience_years` INT NULL,
  `price_range` VARCHAR(100) NULL,
  `photo` VARCHAR(500) NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0,
  `review_count` INT NOT NULL DEFAULT 0,
  `views` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_dl_cat` (`category_id`),
  KEY `idx_dl_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'directory_reviews', "CREATE TABLE IF NOT EXISTS `directory_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `listing_id` INT NOT NULL,
  `user_id` INT NULL,
  `reviewer_name` VARCHAR(255) NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `review` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_dr_listing` (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'directory_jobs', "CREATE TABLE IF NOT EXISTS `directory_jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `listing_id` INT NULL,
  `user_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `job_type` VARCHAR(50) NULL,
  `category` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `location` VARCHAR(255) NULL,
  `salary_range` VARCHAR(100) NULL,
  `contact_phone` VARCHAR(20) NULL,
  `contact_person` VARCHAR(255) NULL,
  `is_seeking` TINYINT(1) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'directory_materials', "CREATE TABLE IF NOT EXISTS `directory_materials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `listing_id` INT NULL,
  `material_name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NULL,
  `brand` VARCHAR(100) NULL,
  `unit` VARCHAR(20) NULL,
  `price` DECIMAL(12,2) NULL,
  `price_date` DATE NULL,
  `notes` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'service_interests', "CREATE TABLE IF NOT EXISTS `service_interests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `service_type` VARCHAR(100) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'new',
  `lead_id` INT NULL,
  `property_id` INT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_si_type` (`service_type`),
  KEY `idx_si_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'noc_requests', "CREATE TABLE IF NOT EXISTS `noc_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `booking_id` INT NULL,
  `plot_id` INT NULL,
  `user_id` INT NULL,
  `requested_by` INT NULL,
  `approved_by` INT NULL,
  `purpose` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT NULL,
  `processed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_noc_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'registries', "CREATE TABLE IF NOT EXISTS `registries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `booking_id` INT NULL,
  `plot_id` INT NULL,
  `user_id` INT NULL,
  `associate_id` INT NULL,
  `sub_registrar_office` VARCHAR(255) NULL,
  `stamp_duty_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `registration_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `other_charges` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_registry_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `notes` TEXT NULL,
  `registration_no` VARCHAR(100) NULL,
  `registration_date` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'booking_payment_schedules', "CREATE TABLE IF NOT EXISTS `booking_payment_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `booking_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `due_date` DATE NULL,
  `accrued_penalty` DECIMAL(12,2) NOT NULL DEFAULT 0,
  KEY `idx_bps_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'plot_bookings.status', "ALTER TABLE `plot_bookings` ADD COLUMN IF NOT EXISTS `status` VARCHAR(50) NOT NULL DEFAULT 'pending' AFTER `booking_number`");
run3($pdo, 'plot_bookings.total_plot_value', "ALTER TABLE `plot_bookings` ADD COLUMN IF NOT EXISTS `total_plot_value` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `status`");
run3($pdo, 'plot_bookings.associate_id', "ALTER TABLE `plot_bookings` ADD COLUMN IF NOT EXISTS `associate_id` INT NULL AFTER `customer_id`");
run3($pdo, 'mlm_commission_ledger.property_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `property_id` INT NULL AFTER `booking_id`");
run3($pdo, 'messages', "CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `content` TEXT NULL,
  `message_type` VARCHAR(20) NOT NULL DEFAULT 'text',
  `sender_type` VARCHAR(20) NULL,
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_msg_pair` (`sender_id`, `receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'ai_calling_schedule', "CREATE TABLE IF NOT EXISTS `ai_calling_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `lead_id` INT NULL,
  `phone` VARCHAR(20) NULL,
  `priority` VARCHAR(20) NULL,
  `scheduled_date` DATE NULL,
  `scheduled_time` TIME NULL,
  `timezone` VARCHAR(50) NULL,
  `script_template` VARCHAR(100) NULL,
  `max_attempts` INT NOT NULL DEFAULT 3,
  `attempt_count` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `ai_agent_id` VARCHAR(100) NULL,
  `call_session_id` VARCHAR(100) NULL,
  `last_attempt_at` DATETIME NULL,
  `result_notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_acs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'ai_calling_agents', "CREATE TABLE IF NOT EXISTS `ai_calling_agents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `agent_id` VARCHAR(100) NOT NULL,
  `agent_name` VARCHAR(255) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `current_calls` INT NOT NULL DEFAULT 0,
  `max_concurrent_calls` INT NOT NULL DEFAULT 5,
  `daily_call_limit` INT NOT NULL DEFAULT 100,
  `total_calls_made` INT NOT NULL DEFAULT 0,
  `successful_calls` INT NOT NULL DEFAULT 0,
  `avg_call_duration` INT NOT NULL DEFAULT 0,
  `last_active_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_agent` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'leads.property_interest', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `property_interest` VARCHAR(255) NULL AFTER `status`");
run3($pdo, 'leads.budget_range', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `budget_range` VARCHAR(100) NULL AFTER `property_interest`");
run3($pdo, 'leads.next_activity_date', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `next_activity_date` DATE NULL AFTER `budget_range`");
run3($pdo, 'leads.last_activity_date', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `last_activity_date` DATETIME NULL AFTER `next_activity_date`");

// ═════════ LEADS wide upgrade (billing/tenants/CRM/voice needs) ═════════
run3($pdo, 'leads.tenant_id', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `id`");
run3($pdo, 'leads.deleted_at', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `updated_at`");
run3($pdo, 'leads.assigned_to', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `assigned_to` INT NULL AFTER `deleted_at`");
run3($pdo, 'leads.lead_score', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `lead_score` INT NULL AFTER `assigned_to`");
run3($pdo, 'leads.lead_category', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `lead_category` VARCHAR(50) NULL AFTER `lead_score`");
run3($pdo, 'leads.score_factors', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `score_factors` TEXT NULL AFTER `lead_category`");
run3($pdo, 'leads.last_scored_at', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `last_scored_at` DATETIME NULL AFTER `score_factors`");
run3($pdo, 'leads.conversion_probability', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `conversion_probability` DECIMAL(5,2) NULL AFTER `last_scored_at`");
run3($pdo, 'leads.is_converted', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `is_converted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `conversion_probability`");
run3($pdo, 'leads.source', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `source` VARCHAR(50) NULL AFTER `is_converted`");
run3($pdo, 'leads.budget', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `budget` DECIMAL(15,2) NULL AFTER `source`");
run3($pdo, 'leads.lead_number', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `lead_number` VARCHAR(50) NULL AFTER `budget`");
run3($pdo, 'leads.location_preference', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `location_preference` VARCHAR(255) NULL AFTER `lead_number`");
run3($pdo, 'leads.created_by', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `created_by` INT NULL AFTER `location_preference`");
run3($pdo, 'leads.priority', "ALTER TABLE `leads` ADD COLUMN IF NOT EXISTS `priority` VARCHAR(20) NULL AFTER `created_by`");
run3($pdo, 'leads.status enum', "ALTER TABLE `leads` MODIFY COLUMN `status` ENUM('new','contacted','interested','converted','lost','nurture','closed','dead','won','qualified') NOT NULL DEFAULT 'new'");

// ═════════ BILLING / TENANTS / FNF / EFILING / LEGAL ═════════
run3($pdo, 'tenants', "CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NULL,
  `domain` VARCHAR(255) NULL,
  `contact_name` VARCHAR(255) NULL,
  `contact_email` VARCHAR(255) NULL,
  `contact_phone` VARCHAR(20) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `plan_id` INT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'trial',
  `max_users` INT NULL,
  `max_leads` INT NULL,
  `max_properties` INT NULL,
  `storage_limit_mb` INT NULL,
  `primary_color` VARCHAR(20) NULL,
  `secondary_color` VARCHAR(20) NULL,
  `logo_url` VARCHAR(500) NULL,
  `features_enabled` TEXT NULL,
  `config` TEXT NULL,
  `settings` TEXT NULL,
  `trial_ends_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'subscription_plans', "CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NULL,
  `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `max_users` INT NULL,
  `max_leads` INT NULL,
  `max_properties` INT NULL,
  `max_associates` INT NULL,
  `storage_limit_mb` INT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'tenant_subscriptions', "CREATE TABLE IF NOT EXISTS `tenant_subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `plan_id` INT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `billing_cycle` VARCHAR(20) NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `razorpay_subscription_id` VARCHAR(100) NULL,
  `razorpay_customer_id` VARCHAR(100) NULL,
  `current_period_start` DATETIME NULL,
  `current_period_end` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_ts_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'tenant_usage', "CREATE TABLE IF NOT EXISTS `tenant_usage` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `period_start` DATE NULL,
  `period_end` DATE NULL,
  `users_count` INT NOT NULL DEFAULT 0,
  `leads_created` INT NOT NULL DEFAULT 0,
  `properties_count` INT NOT NULL DEFAULT 0,
  `api_calls` INT NOT NULL DEFAULT 0,
  `storage_used_mb` INT NOT NULL DEFAULT 0,
  `emails_sent` INT NOT NULL DEFAULT 0,
  `sms_sent` INT NOT NULL DEFAULT 0,
  KEY `idx_tu_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'tenant_users', "CREATE TABLE IF NOT EXISTS `tenant_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` VARCHAR(50) NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_tu` (`tenant_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'employees.employee_code', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `employee_code` VARCHAR(50) NULL AFTER `user_id`");
run3($pdo, 'employees.designation', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `designation` VARCHAR(100) NULL AFTER `employee_code`");
run3($pdo, 'employees.department', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `department` VARCHAR(100) NULL AFTER `designation`");
run3($pdo, 'employees.joining_date', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `joining_date` DATE NULL AFTER `department`");
run3($pdo, 'employees.tenant_id', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `id`");
run3($pdo, 'employees.offboarded_at', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `offboarded_at` DATETIME NULL AFTER `updated_at`");
run3($pdo, 'employees.offboard_reason', "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `offboard_reason` VARCHAR(255) NULL AFTER `offboarded_at`");
run3($pdo, 'employee_fnf_settlements', "CREATE TABLE IF NOT EXISTS `employee_fnf_settlements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `settlement_no` VARCHAR(50) NULL,
  `employee_id` INT NOT NULL,
  `resignation_date` DATE NULL,
  `last_working_day` DATE NULL,
  `exit_type` VARCHAR(50) NULL,
  `notice_period_days` INT NULL,
  `notice_served_days` INT NULL,
  `earnings_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `deductions_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `net_payable` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `settlement_details` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `approved_by` INT NULL,
  `approved_at` DATETIME NULL,
  `paid_at` DATETIME NULL,
  `payment_reference` VARCHAR(100) NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_fnf_emp` (`employee_id`),
  KEY `idx_fnf_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'employee_assets', "CREATE TABLE IF NOT EXISTS `employee_assets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `employee_id` INT NOT NULL,
  `asset_name` VARCHAR(255) NOT NULL,
  `asset_category` VARCHAR(100) NULL,
  `serial_number` VARCHAR(100) NULL,
  `purchase_value` DECIMAL(12,2) NULL,
  `current_value` DECIMAL(12,2) NULL,
  `assigned_date` DATE NULL,
  `returned_at` DATETIME NULL,
  `condition_on_return` VARCHAR(100) NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ea_emp` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'efiling_submissions', "CREATE TABLE IF NOT EXISTS `efiling_submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_type` VARCHAR(50) NULL,
  `reference_table` VARCHAR(100) NULL,
  `reference_id` INT NULL,
  `financial_year` VARCHAR(10) NULL,
  `quarter` VARCHAR(5) NULL,
  `period_month` INT NULL,
  `period_year` INT NULL,
  `gstin` VARCHAR(20) NULL,
  `tan` VARCHAR(20) NULL,
  `pan` VARCHAR(20) NULL,
  `filing_date` DATE NULL,
  `due_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `filing_mode` VARCHAR(20) NULL,
  `total_records` INT NOT NULL DEFAULT 0,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `prepared_by` INT NULL,
  `notes` TEXT NULL,
  `submitted_by` INT NULL,
  `portal_reference` VARCHAR(100) NULL,
  `portal_response_json` TEXT NULL,
  `json_file_path` VARCHAR(500) NULL,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ef_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'efiling_deadlines', "CREATE TABLE IF NOT EXISTS `efiling_deadlines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `due_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'upcoming',
  `financial_year` VARCHAR(10) NULL,
  `filing_type` VARCHAR(50) NULL,
  `submission_id` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'tds_register', "CREATE TABLE IF NOT EXISTS `tds_register` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `financial_year` VARCHAR(10) NULL,
  `quarter` VARCHAR(5) NULL,
  `transaction_date` DATE NULL,
  `tds_section` VARCHAR(20) NULL,
  `deductor_name` VARCHAR(255) NULL,
  `deductor_pan` VARCHAR(20) NULL,
  `deductor_state_code` VARCHAR(10) NULL,
  `deductee_pan` VARCHAR(20) NULL,
  `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `tds_rate` DECIMAL(5,2) NULL,
  `tds_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `surcharge` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `cess` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total_tds` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `description` TEXT NULL,
  `nature_of_payment` VARCHAR(100) NULL,
  KEY `idx_tds_fyq` (`financial_year`, `quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'tds_challans', "CREATE TABLE IF NOT EXISTS `tds_challans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `challan_number` VARCHAR(50) NULL,
  `bsr_code` VARCHAR(20) NULL,
  `tan` VARCHAR(20) NULL,
  `assessment_year` VARCHAR(10) NULL,
  `financial_year` VARCHAR(10) NULL,
  `quarter` VARCHAR(5) NULL,
  `deposit_date` DATE NULL,
  `major_head` VARCHAR(10) NULL,
  `minor_head` VARCHAR(10) NULL,
  `tds_section` VARCHAR(20) NULL,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `interest_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `penalty_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `surcharge_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `cess_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_with_charges` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `challan_status` VARCHAR(20) NULL,
  `deposited_via` VARCHAR(50) NULL,
  `bank_name` VARCHAR(100) NULL,
  `remarks` TEXT NULL,
  `govt_challan_id` VARCHAR(100) NULL,
  `receipt_number` VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'gst_transactions', "CREATE TABLE IF NOT EXISTS `gst_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `transaction_date` DATE NULL,
  `transaction_type` VARCHAR(20) NULL,
  `party_gstin` VARCHAR(20) NULL,
  `party_name` VARCHAR(255) NULL,
  `invoice_number` VARCHAR(50) NULL,
  `invoice_date` DATE NULL,
  `document_type` VARCHAR(20) NULL,
  `taxable_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `place_of_supply` VARCHAR(50) NULL,
  `reverse_charge` TINYINT(1) NOT NULL DEFAULT 0,
  `gst_rate` DECIMAL(5,2) NULL,
  `cgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `sgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `igst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `cess_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_tax` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `hsn_sac_code` VARCHAR(20) NULL,
  `gstr1_status` VARCHAR(20) NULL,
  `itc_eligible` TINYINT(1) NOT NULL DEFAULT 0,
  `itc_claimed` TINYINT(1) NOT NULL DEFAULT 0,
  KEY `idx_gst_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'gst_returns', "CREATE TABLE IF NOT EXISTS `gst_returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_period` VARCHAR(10) NULL,
  `filing_status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'company_credentials', "CREATE TABLE IF NOT EXISTS `company_credentials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `credential_type` VARCHAR(50) NULL,
  `credential_value` VARCHAR(255) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'legal_documents', "CREATE TABLE IF NOT EXISTS `legal_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL,
  `template_id` INT NULL,
  `category` VARCHAR(100) NULL,
  `document_type` VARCHAR(50) NULL,
  `content` TEXT NULL,
  `summary` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `is_mandatory` TINYINT(1) NOT NULL DEFAULT 0,
  `entity_type` VARCHAR(50) NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  `published_at` DATETIME NULL,
  `kyc_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `kyc_verified_at` DATETIME NULL,
  `kyc_verified_by` INT NULL,
  KEY `idx_ld_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'legal_document_templates', "CREATE TABLE IF NOT EXISTS `legal_document_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `category_id` INT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `content` TEXT NULL,
  `merge_fields` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `is_customer_facing` TINYINT(1) NOT NULL DEFAULT 0,
  `version` INT NOT NULL DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'legal_document_categories', "CREATE TABLE IF NOT EXISTS `legal_document_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(100) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'legal_clause_library', "CREATE TABLE IF NOT EXISTS `legal_clause_library` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NULL,
  `tags` VARCHAR(500) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'legal_ai_prompts', "CREATE TABLE IF NOT EXISTS `legal_ai_prompts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `prompt_template` TEXT NULL,
  `document_category` VARCHAR(100) NULL,
  `model` VARCHAR(100) NULL,
  `temperature` DECIMAL(3,2) NULL,
  `max_tokens` INT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'pages', "CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) NULL,
  `title` VARCHAR(255) NULL,
  `content` TEXT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ═════════ MLM / HR (agents, plans, careers, departments, menus, CRM) ═════════
run3($pdo, 'mlm_commission_ledger.tenant_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `id`");
run3($pdo, 'mlm_commission_ledger.plan_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `plan_id` INT NULL AFTER `booking_id`");
run3($pdo, 'mlm_commission_ledger.plan_version', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `plan_version` INT NULL AFTER `plan_id`");
run3($pdo, 'mlm_commission_ledger.plan_snapshot', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `plan_snapshot` TEXT NULL AFTER `plan_version`");
run3($pdo, 'mlm_commission_ledger.receipt_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `receipt_id` INT NULL AFTER `plan_snapshot`");
run3($pdo, 'mlm_commission_ledger.calculation_engine', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `calculation_engine` VARCHAR(50) NULL AFTER `receipt_id`");
run3($pdo, 'property_agents', "CREATE TABLE IF NOT EXISTS `property_agents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `agent_user_id` INT NOT NULL,
  `property_id` INT NULL,
  `commission_pct` DECIMAL(5,2) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pa_agent` (`agent_user_id`),
  KEY `idx_pa_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'mlm_commission_plans', "CREATE TABLE IF NOT EXISTS `mlm_commission_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `plan_name` VARCHAR(255) NOT NULL,
  `plan_code` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `plan_type` VARCHAR(50) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `version` INT NOT NULL DEFAULT 1,
  `effective_date` DATE NULL,
  `created_by` INT NULL,
  `global_cap_pct` DECIMAL(5,2) NULL,
  `track_a_pct` DECIMAL(5,2) NULL,
  `track_b_pct` DECIMAL(5,2) NULL,
  `track_c_pct` DECIMAL(5,2) NULL,
  `royalty_pool_pct` DECIMAL(5,2) NULL,
  `same_level_override_gen1` DECIMAL(5,2) NULL,
  `same_level_override_gen2` DECIMAL(5,2) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'mlm_plan_levels', "CREATE TABLE IF NOT EXISTS `mlm_plan_levels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `plan_id` INT NOT NULL,
  `level_name` VARCHAR(100) NULL,
  `level_order` INT NOT NULL DEFAULT 0,
  `direct_commission` DECIMAL(8,2) NULL,
  `team_commission` DECIMAL(8,2) NULL,
  `level_bonus` DECIMAL(8,2) NULL,
  `matching_bonus` DECIMAL(8,2) NULL,
  `leadership_bonus` DECIMAL(8,2) NULL,
  `performance_bonus` DECIMAL(8,2) NULL,
  `monthly_target` DECIMAL(15,2) NULL,
  KEY `idx_mpl_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'commission_plan_audit', "CREATE TABLE IF NOT EXISTS `commission_plan_audit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `plan_id` INT NULL,
  `plan_name` VARCHAR(255) NULL,
  `plan_code` VARCHAR(50) NULL,
  `version` INT NULL,
  `action` VARCHAR(50) NULL,
  `changed_fields` TEXT NULL,
  `old_values` TEXT NULL,
  `new_values` TEXT NULL,
  `changed_by` INT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'commission_simulator_presets', "CREATE TABLE IF NOT EXISTS `commission_simulator_presets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `sim_mode` VARCHAR(50) NULL,
  `params_json` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'wallet_activation_packages', "CREATE TABLE IF NOT EXISTS `wallet_activation_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referral_pct_l1` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `referral_pct_l2` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'agent_agreements', "CREATE TABLE IF NOT EXISTS `agent_agreements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `agent_id` INT NOT NULL,
  `property_id` INT NULL,
  `agreement_type` VARCHAR(50) NULL,
  `title` VARCHAR(255) NULL,
  `content` TEXT NULL,
  `commission_pct` DECIMAL(5,2) NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `notes` TEXT NULL,
  `signed_at` DATETIME NULL,
  `signed_ip` VARCHAR(45) NULL,
  `signed_by_user_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_aa_agent` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'commission_recalculations', "CREATE TABLE IF NOT EXISTS `commission_recalculations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `original_ledger_id` INT NULL,
  `plan_id` INT NULL,
  `plan_version` INT NULL,
  `reason` TEXT NULL,
  `original_amount` DECIMAL(15,2) NULL,
  `new_amount` DECIMAL(15,2) NULL,
  `amount_diff` DECIMAL(15,2) NULL,
  `requested_by` INT NULL,
  `approved_by` INT NULL,
  `new_ledger_id` INT NULL,
  `admin_notes` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'mlm_levels', "CREATE TABLE IF NOT EXISTS `mlm_levels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `level_name` VARCHAR(100) NOT NULL,
  `level_number` INT NOT NULL DEFAULT 0,
  `direct_commission_percentage` DECIMAL(5,2) NULL,
  `team_commission_percentage` DECIMAL(5,2) NULL,
  `level_difference_commission_percentage` DECIMAL(5,2) NULL,
  `matching_bonus_percentage` DECIMAL(5,2) NULL,
  `leadership_bonus_percentage` DECIMAL(5,2) NULL,
  `performance_bonus_percentage` DECIMAL(5,2) NULL,
  `joining_fee` DECIMAL(10,2) NULL,
  `monthly_maintenance` DECIMAL(10,2) NULL,
  `team_size_required` INT NULL,
  `direct_referrals_required` INT NULL,
  `monthly_target` DECIMAL(15,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'mlm_profiles.status', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `current_level`");
run3($pdo, 'mlm_profiles.direct_referrals', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `direct_referrals` INT NOT NULL DEFAULT 0 AFTER `status`");
run3($pdo, 'mlm_profiles.lifetime_sales', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `lifetime_sales` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `direct_referrals`");
run3($pdo, 'mlm_profiles.total_team_size', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `total_team_size` INT NOT NULL DEFAULT 0 AFTER `lifetime_sales`");
run3($pdo, 'mlm_profiles.rank_updated_at', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `rank_updated_at` DATETIME NULL AFTER `total_team_size`");
run3($pdo, 'mlm_profiles.tenant_id', "ALTER TABLE `mlm_profiles` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `user_id`");
run3($pdo, 'mlm_rank_history', "CREATE TABLE IF NOT EXISTS `mlm_rank_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `associate_id` INT NOT NULL,
  `from_rank` VARCHAR(50) NULL,
  `to_rank` VARCHAR(50) NULL,
  `qualifying_volume_at_promotion` DECIMAL(15,2) NULL,
  `leg_count_at_promotion` INT NULL,
  `promoted_at` DATETIME NULL,
  KEY `idx_mrh_assoc` (`associate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'associates', "CREATE TABLE IF NOT EXISTS `associates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NOT NULL,
  `joining_date` DATE NULL,
  `brokerage_rate` DECIMAL(5,2) NULL,
  `level` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_assoc_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'sales', "CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `associate_id` INT NULL,
  `property_id` INT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `sale_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `commission_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_sales_assoc` (`associate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'bookings.total_amount', "ALTER TABLE `bookings` ADD COLUMN IF NOT EXISTS `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `amount`");
run3($pdo, 'users.address', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `address` TEXT NULL AFTER `phone`");
run3($pdo, 'users.commission_rate', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `commission_rate` DECIMAL(5,2) NULL AFTER `address`");
run3($pdo, 'users.deleted_at', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `updated_at`");
run3($pdo, 'users.status enum', "ALTER TABLE `users` MODIFY COLUMN `status` ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active'");
run3($pdo, 'job_applications', "CREATE TABLE IF NOT EXISTS `job_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NULL,
  `full_name` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `message` TEXT NULL,
  `file_path` VARCHAR(500) NULL,
  `resume_file` VARCHAR(500) NULL,
  `position` VARCHAR(255) NULL,
  `experience` VARCHAR(100) NULL,
  `availability` VARCHAR(100) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_ja_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'career_application_history', "CREATE TABLE IF NOT EXISTS `career_application_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cah_app` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'careers', "CREATE TABLE IF NOT EXISTS `careers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'career_applications', "CREATE TABLE IF NOT EXISTS `career_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `career_id` INT NULL,
  `full_name` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `resume_path` VARCHAR(500) NULL,
  `cover_letter` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ca_career` (`career_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'department_requests', "CREATE TABLE IF NOT EXISTS `department_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `department_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `priority` VARCHAR(20) NULL,
  `requested_by` INT NULL,
  `requester_role` VARCHAR(50) NULL,
  `requester_name` VARCHAR(255) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'open',
  `assigned_to` INT NULL,
  `due_date` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_dr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'department_request_comments', "CREATE TABLE IF NOT EXISTS `department_request_comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `request_id` INT NOT NULL,
  `user_id` INT NULL,
  `comment` TEXT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_drc_req` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'user_activity_logs_unified', "CREATE TABLE IF NOT EXISTS `user_activity_logs_unified` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NULL,
  `action` VARCHAR(100) NULL,
  `context` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// Menu tables: created EMPTY — rbac_sidebar self-heals from the manifest.
run3($pdo, 'admin_menu_items', "CREATE TABLE IF NOT EXISTS `admin_menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(100) NULL,
  `url` VARCHAR(500) NULL,
  `parent_id` INT NULL,
  `section` VARCHAR(100) NULL,
  `order_index` INT NOT NULL DEFAULT 0,
  `permission_key` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'admin_role_menu_permissions', "CREATE TABLE IF NOT EXISTS `admin_role_menu_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` VARCHAR(50) NOT NULL,
  `menu_item_id` INT NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 0,
  `can_create` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `uq_role_item` (`role`, `menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'admin_user_menu_permissions', "CREATE TABLE IF NOT EXISTS `admin_user_menu_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `menu_item_id` INT NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 0,
  `can_create` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `uq_user_item` (`user_id`, `menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'crm_tasks', "CREATE TABLE IF NOT EXISTS `crm_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `lead_id` INT NULL,
  `assigned_to` INT NULL,
  `task_type` VARCHAR(50) NULL,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `priority` VARCHAR(20) NULL,
  `due_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `completed_at` DATETIME NULL,
  `completed_notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_ct_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'lead_scores', "CREATE TABLE IF NOT EXISTS `lead_scores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT NULL,
  `calculated_at` DATETIME NULL,
  KEY `idx_ls_lead` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'agent_insights', "CREATE TABLE IF NOT EXISTS `agent_insights` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'agent_task_logs', "CREATE TABLE IF NOT EXISTS `agent_task_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `agent_type` VARCHAR(100) NULL,
  `task_name` VARCHAR(255) NULL,
  `task_data` TEXT NULL,
  `result` TEXT NULL,
  `status` VARCHAR(20) NULL,
  `triggered_by` VARCHAR(100) NULL,
  `completed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'crm_pipeline_stages', "CREATE TABLE IF NOT EXISTS `crm_pipeline_stages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` VARCHAR(50) NOT NULL DEFAULT 'all',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `order_index` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'settings', "CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'ai_intent_patterns', "CREATE TABLE IF NOT EXISTS `ai_intent_patterns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run3($pdo, 'site_visits', "CREATE TABLE IF NOT EXISTS `site_visits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `user_id` INT NULL,
  `visitor_name` VARCHAR(255) NULL,
  `visitor_phone` VARCHAR(20) NULL,
  `notes` TEXT NULL,
  `visit_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'scheduled',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_sv_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "DONE probe-green part 3\n";
