<?php
/**
 * Migration: Create faqs table if not exists
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("
        CREATE TABLE IF NOT EXISTS `faqs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `question` text NOT NULL,
            `answer` text NOT NULL,
            `category` varchar(100) DEFAULT 'General',
            `display_order` int(11) DEFAULT 0,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `ix_faqs_status` (`status`),
            KEY `ix_faqs_created_at` (`created_at`),
            KEY `ix_faqs_updated_at` (`updated_at`),
            KEY `idx_faqs_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "faqs table verified/created successfully.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "Error creating faqs table: " . $e->getMessage() . "\n";
    exit(1);
}
