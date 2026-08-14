<?php
/**
 * Migration: Create 7 engagement tables
 * - engagement_goals (for EngagementController)
 * - mlm_associate_metrics (for EngagementService)
 * - mlm_leaderboard_snapshots (for EngagementService)
 * - mlm_goals (for EngagementService)
 * - mlm_goal_progress (for EngagementService)
 * - mlm_goal_events (for EngagementService)
 * - mlm_notification_preferences (for EngagementService)
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database\n\n";

    $tables = [
        // 1. engagement_goals â€” used by EngagementController raw queries
        "CREATE TABLE IF NOT EXISTS `engagement_goals` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `goal_type` ENUM('user_growth','sales_target','commission_target','network_growth','engagement_rate') NOT NULL DEFAULT 'user_growth',
            `target_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `current_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `period` ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `priority` INT(11) NOT NULL DEFAULT 5,
            `status` ENUM('active','completed','expired','cancelled') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_eg_status` (`status`),
            KEY `idx_eg_type_period` (`goal_type`,`period`),
            KEY `idx_eg_dates` (`start_date`,`end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 2. mlm_associate_metrics â€” EngagementService getAssociateMetrics()
        "CREATE TABLE IF NOT EXISTS `mlm_associate_metrics` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `period_start` DATE NOT NULL,
            `period_end` DATE NOT NULL,
            `rank_label` VARCHAR(50) DEFAULT NULL,
            `total_sales_volume` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `personal_sales_volume` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `team_sales_volume` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `direct_referrals` INT(11) NOT NULL DEFAULT 0,
            `team_size` INT(11) NOT NULL DEFAULT 0,
            `total_commission_earned` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_payouts_received` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `active_legs` INT(11) NOT NULL DEFAULT 0,
            `consecutive_qualifying_months` INT(11) NOT NULL DEFAULT 0,
            `engagement_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `login_count` INT(11) NOT NULL DEFAULT 0,
            `last_login_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_am_user_id` (`user_id`),
            KEY `idx_am_period` (`period_start`,`period_end`),
            KEY `idx_am_rank` (`rank_label`),
            KEY `idx_am_user_period` (`user_id`,`period_start`,`period_end`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 3. mlm_leaderboard_snapshots â€” EngagementService getLeaderboardSnapshot()
        "CREATE TABLE IF NOT EXISTS `mlm_leaderboard_snapshots` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `metric_type` VARCHAR(50) NOT NULL,
            `snapshot_date` DATE NOT NULL,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `rank_position` INT(11) NOT NULL DEFAULT 0,
            `metric_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `previous_rank` INT(11) DEFAULT NULL,
            `rank_change` INT(11) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_lbs_metric_date_user` (`metric_type`,`snapshot_date`,`user_id`),
            KEY `idx_lbs_user` (`user_id`),
            KEY `idx_lbs_metric_date` (`metric_type`,`snapshot_date`),
            KEY `idx_lbs_rank` (`rank_position`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 4. mlm_goals â€” EngagementService getGoals(), createGoal(), updateGoal(), updateGoalStatus()
        "CREATE TABLE IF NOT EXISTS `mlm_goals` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `goal_type` ENUM('sales','recruits','commission','custom') NOT NULL DEFAULT 'sales',
            `scope` ENUM('individual','team') NOT NULL DEFAULT 'individual',
            `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
            `target_value` DECIMAL(15,2) NOT NULL,
            `target_units` VARCHAR(50) DEFAULT NULL,
            `current_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `status` ENUM('draft','active','completed','expired','cancelled') NOT NULL DEFAULT 'active',
            `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_mg_user` (`user_id`),
            KEY `idx_mg_status` (`status`),
            KEY `idx_mg_type_scope` (`goal_type`,`scope`),
            KEY `idx_mg_dates` (`start_date`,`end_date`),
            KEY `idx_mg_active_user` (`status`,`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 5. mlm_goal_progress â€” EngagementService getGoalProgress(), recordGoalProgress()
        "CREATE TABLE IF NOT EXISTS `mlm_goal_progress` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `goal_id` BIGINT(20) UNSIGNED NOT NULL,
            `checkpoint_date` DATE NOT NULL,
            `actual_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `percentage_complete` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `notes` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_mgp_goal_date` (`goal_id`,`checkpoint_date`),
            KEY `idx_mgp_goal` (`goal_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 6. mlm_goal_events â€” EngagementService getGoalEvents(), logGoalEvent()
        "CREATE TABLE IF NOT EXISTS `mlm_goal_events` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `goal_id` BIGINT(20) UNSIGNED NOT NULL,
            `event_type` VARCHAR(50) NOT NULL,
            `event_message` VARCHAR(255) DEFAULT NULL,
            `event_payload` JSON DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_mge_goal` (`goal_id`),
            KEY `idx_mge_type` (`event_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        // 7. mlm_notification_preferences â€” EngagementService getNotificationPreferences()
        "CREATE TABLE IF NOT EXISTS `mlm_notification_preferences` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'general',
            `email_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `sms_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `push_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `whatsapp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `frequency` ENUM('instant','daily','weekly','never') NOT NULL DEFAULT 'instant',
            `quiet_hours_start` TIME DEFAULT NULL,
            `quiet_hours_end` TIME DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_mnp_user_category` (`user_id`,`category`),
            KEY `idx_mnp_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];

    $created = 0;
    foreach ($tables as $i => $sql) {
        $pdo->exec($sql);
        $created++;
        // Extract table name from CREATE TABLE statement
        preg_match('/`(\w+)`/', $sql, $m);
        $tableName = $m[1] ?? "table_$i";
        echo "  Created: $tableName\n";
    }

    echo "\n=== $created tables created successfully ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>