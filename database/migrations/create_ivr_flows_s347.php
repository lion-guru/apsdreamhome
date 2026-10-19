<?php
/**
 * Migration: IVR Flows + Steps tables (Phase 4 AI Voice Calling)
 * Idempotent: CREATE TABLE IF NOT EXISTS
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$pdo->exec("
CREATE TABLE IF NOT EXISTS `ivr_flows` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `welcome_message` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `ivr_flow_steps` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `flow_id` INT UNSIGNED NOT NULL,
    `step_order` INT UNSIGNED NOT NULL DEFAULT 1,
    `step_type` ENUM('message','menu','transfer','voicemail','hangup') NOT NULL DEFAULT 'message',
    `config_json` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_flow` (`flow_id`),
    KEY `idx_order` (`flow_id`, `step_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Seed a default welcome flow
$pdo->exec("
INSERT IGNORE INTO `ivr_flows` (`id`, `name`, `description`, `welcome_message`, `is_active`)
VALUES (1, 'Default Welcome Flow', 'Standard welcome IVR with menu options', 
        'Welcome to APS Dream Home. Press 1 for Sales, 2 for Support, 3 for Account Information.', 1)
");

echo "DONE: ivr_flows + ivr_flow_steps tables ready.\n";