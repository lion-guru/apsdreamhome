<?php
/**
 * Migration: Create notifications_archive table matching notifications table schema exactly
 * Session: 348
 * Idempotent: Creates table if not exists with all columns
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$pdo->exec("DROP TABLE IF EXISTS `notifications_archive`");

$sql = "CREATE TABLE IF NOT EXISTS `notifications_archive` (
    `id` bigint(20) unsigned NOT NULL,
    `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
    `user_id` bigint(20) unsigned DEFAULT NULL,
    `type` varchar(50) NOT NULL DEFAULT 'system',
    `title` varchar(255) DEFAULT NULL,
    `message` text DEFAULT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'read',
    `is_read` tinyint(1) NOT NULL DEFAULT 1,
    `priority` varchar(20) NOT NULL DEFAULT 'normal',
    `related_id` bigint(20) unsigned DEFAULT NULL,
    `related_type` varchar(50) DEFAULT NULL,
    `action_url` varchar(500) DEFAULT NULL,
    `template_key` varchar(50) DEFAULT NULL,
    `template_data` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `read_at` timestamp NULL DEFAULT NULL,
    `is_important` tinyint(1) DEFAULT 0,
    `expires_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_arch_user` (`user_id`),
    KEY `idx_arch_type` (`type`),
    KEY `idx_arch_created` (`created_at`),
    KEY `idx_arch_archived` (`archived_at`),
    KEY `idx_arch_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "RE-SEEDED table notifications_archive successfully with exact schema matching.\n";
