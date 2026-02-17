-- ------------------------------------------------------------
-- APS Dream Home - MLM Phase 5 Engagement Schema
-- ------------------------------------------------------------
-- Creates tables to support associate metrics, leaderboards,
-- goal tracking, and notification feeds for engagement features.
-- Execute in staging first:
--   mysql -u <user> -p apsdreamhome < database/mlm_phase5_engagement.sql
-- ------------------------------------------------------------

START TRANSACTION;

-- Rolling metrics aggregated per associate and period
CREATE TABLE IF NOT EXISTS `mlm_associate_metrics` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `sales_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `commissions_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `recruits_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `active_team_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `rank_label` VARCHAR(50) DEFAULT NULL,
    `snapshot_json` JSON DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `period_start`, `period_end`),
    CONSTRAINT `fk_metrics_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Leaderboard run audit metadata
CREATE TABLE IF NOT EXISTS `mlm_leaderboard_runs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `metric_type` VARCHAR(50) NOT NULL,
    `snapshot_date` DATE NOT NULL,
    `status` ENUM('pending','complete','failed') NOT NULL DEFAULT 'pending',
    `processed_records` INT UNSIGNED NOT NULL DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_leaderboard_runs_date` (`snapshot_date`),
    KEY `idx_leaderboard_runs_metric` (`metric_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cached leaderboard rows for fast UI rendering
CREATE TABLE IF NOT EXISTS `mlm_leaderboard_snapshots` (
    `snapshot_date` DATE NOT NULL,
    `metric_type` VARCHAR(50) NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `run_id` BIGINT UNSIGNED DEFAULT NULL,
    `rank_position` INT UNSIGNED NOT NULL,
    `metric_value` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `tie_breaker` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `payload_json` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`snapshot_date`, `metric_type`, `user_id`),
    KEY `idx_leaderboard_rank` (`metric_type`, `snapshot_date`, `rank_position`),
    KEY `idx_leaderboard_run` (`run_id`),
    CONSTRAINT `fk_leaderboard_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_leaderboard_run` FOREIGN KEY (`run_id`) REFERENCES `mlm_leaderboard_runs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goal definitions (individual or team scope)
CREATE TABLE IF NOT EXISTS `mlm_goals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `goal_type` ENUM('sales','recruits','commission','custom') NOT NULL DEFAULT 'sales',
    `scope` ENUM('individual','team') NOT NULL DEFAULT 'individual',
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `target_value` DECIMAL(15,2) NOT NULL,
    `target_units` VARCHAR(30) DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('draft','active','completed','expired','cancelled') NOT NULL DEFAULT 'active',
    `created_by` BIGINT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_goals_user` (`user_id`),
    KEY `idx_goals_status` (`status`),
    CONSTRAINT `fk_goals_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_goals_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goal progress checkpoints
CREATE TABLE IF NOT EXISTS `mlm_goal_progress` (
    `goal_id` BIGINT UNSIGNED NOT NULL,
    `checkpoint_date` DATE NOT NULL,
    `actual_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `percentage_complete` DECIMAL(6,3) NOT NULL DEFAULT 0.000,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`goal_id`, `checkpoint_date`),
    CONSTRAINT `fk_goal_progress_goal` FOREIGN KEY (`goal_id`) REFERENCES `mlm_goals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goal events for timeline and notifications
CREATE TABLE IF NOT EXISTS `mlm_goal_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `goal_id` BIGINT UNSIGNED NOT NULL,
    `event_type` VARCHAR(50) NOT NULL,
    `event_message` VARCHAR(255) NOT NULL,
    `event_payload` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_goal_events_goal` (`goal_id`),
    CONSTRAINT `fk_goal_events_goal` FOREIGN KEY (`goal_id`) REFERENCES `mlm_goals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- In-app notification feed for associates
CREATE TABLE IF NOT EXISTS `mlm_notification_feed` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `category` ENUM('goal','rank','payout','network','system') NOT NULL DEFAULT 'system',
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `payload` JSON DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_notification_feed_user` (`user_id`, `created_at`),
    KEY `idx_notification_feed_category` (`category`),
    CONSTRAINT `fk_notification_feed_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Channel preferences for engagement notifications
CREATE TABLE IF NOT EXISTS `mlm_notification_preferences` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `channel` ENUM('email','app','sms','whatsapp') NOT NULL DEFAULT 'app',
    `category` ENUM('goal','rank','payout','network','system') NOT NULL DEFAULT 'system',
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `channel`, `category`),
    CONSTRAINT `fk_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- ------------------------------------------------------------
-- Post-creation notes:
--   1. Backfill mlm_associate_metrics using historical sales/commission data.
--   2. Schedule leaderboard snapshot jobs to populate mlm_leaderboard_runs and mlm_leaderboard_snapshots.
--   3. Seed default notification preferences where appropriate.
-- ------------------------------------------------------------
