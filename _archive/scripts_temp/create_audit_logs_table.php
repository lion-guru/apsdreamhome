<?php
/**
 * Audit Logs Migration
 */
require __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();

try {
    $db->query("
        CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `user_role` VARCHAR(50) NOT NULL,
            `action` VARCHAR(100) NOT NULL,
            `action_type` ENUM('create','read','update','delete','login','logout','export','import','print','approve','reject','payment','commission') NOT NULL DEFAULT 'update',
            `entity_type` VARCHAR(100) NULL,
            `entity_id` BIGINT UNSIGNED NULL,
            `description` TEXT NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `request_url` VARCHAR(500) NULL,
            `request_method` VARCHAR(10) NULL,
            `session_id` VARCHAR(128) NULL,
            `status` ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
            `error_message` TEXT NULL,
            `metadata` JSON NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_user_role` (`user_role`),
            INDEX `idx_action` (`action`),
            INDEX `idx_entity` (`entity_type`, `entity_id`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "audit_logs table created successfully\n";
} catch (Exception $e) {
    echo "Error creating audit_logs table: " . $e->getMessage() . "\n";
}