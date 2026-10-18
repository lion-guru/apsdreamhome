<?php
/**
 * Migration: Engagement Surveys, eNPS, Survey Questions, Action Plans (Phase 4 HR)
 * Idempotent: CREATE TABLE IF NOT EXISTS
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$pdo->exec("
CREATE TABLE IF NOT EXISTS `survey_questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` INT UNSIGNED NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('rating','multiple_choice','text','boolean','nps') NOT NULL DEFAULT 'rating',
    `options_json` JSON NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `category` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_survey` (`survey_id`),
    KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `survey_action_plans` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `owner_user_id` INT UNSIGNED NULL,
    `due_date` DATE NULL,
    `status` ENUM('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
    `progress_pct` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_survey` (`survey_id`),
    KEY `idx_owner` (`owner_user_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `eNPS_surveys` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `survey_date` DATE NOT NULL,
    `department_id` INT UNSIGNED NULL,
    `is_anonymous` TINYINT(1) NOT NULL DEFAULT 1,
    `status` ENUM('draft','open','closed','archived') NOT NULL DEFAULT 'draft',
    `total_sent` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_responses` INT UNSIGNED NOT NULL DEFAULT 0,
    `promoters` INT UNSIGNED NOT NULL DEFAULT 0,
    `passives` INT UNSIGNED NOT NULL DEFAULT 0,
    `detractors` INT UNSIGNED NOT NULL DEFAULT 0,
    `enps_score` DECIMAL(5,2) NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_date` (`survey_date`),
    KEY `idx_status` (`status`),
    KEY `idx_dept` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `eNPS_responses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `category` ENUM('promoter','passive','detractor') NOT NULL,
    `feedback_text` TEXT NULL,
    `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_survey` (`survey_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `org_chart_relationships` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_node_id` INT UNSIGNED NOT NULL,
    `child_node_id` INT UNSIGNED NOT NULL,
    `relationship_type` ENUM('reports_to','dotted_line','mentor','cross_functional') NOT NULL DEFAULT 'reports_to',
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_parent_child` (`parent_node_id`, `child_node_id`),
    KEY `idx_parent` (`parent_node_id`),
    KEY `idx_child` (`child_node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "DONE: survey_questions, survey_action_plans, eNPS_surveys, eNPS_responses, org_chart_relationships tables ready.\n";