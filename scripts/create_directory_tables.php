<?php
/**
 * Migration: Create directory_categories and directory_listings tables if not exists
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("
        CREATE TABLE IF NOT EXISTS `directory_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `icon` varchar(100) DEFAULT 'fas fa-building',
            `parent_id` int(11) DEFAULT NULL,
            `sort_order` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `idx_active` (`is_active`),
            KEY `idx_parent` (`parent_id`),
            KEY `idx_directory_categories_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        CREATE TABLE IF NOT EXISTS `directory_listings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `category_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL COMMENT 'Who submitted this',
            `business_name` varchar(255) NOT NULL,
            `owner_name` varchar(255) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `whatsapp` varchar(20) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `website` varchar(500) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `city` varchar(100) DEFAULT NULL,
            `state` varchar(100) DEFAULT NULL,
            `pincode` varchar(10) DEFAULT NULL,
            `latitude` decimal(10,7) DEFAULT NULL,
            `longitude` decimal(10,7) DEFAULT NULL,
            `experience_years` int(11) DEFAULT 0,
            `price_range` varchar(50) DEFAULT NULL COMMENT 'Budget/Mid-range/Premium',
            `photo` varchar(500) DEFAULT NULL,
            `photos` text DEFAULT NULL COMMENT 'JSON array of additional photos',
            `is_verified` tinyint(1) DEFAULT 0,
            `is_featured` tinyint(1) DEFAULT 0,
            `status` enum('pending','approved','rejected') DEFAULT 'pending',
            `views` int(11) DEFAULT 0,
            `rating` decimal(2,1) DEFAULT 0.0,
            `review_count` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_featured` (`is_featured`),
            KEY `idx_city` (`city`),
            KEY `idx_category_status` (`category_id`,`status`),
            KEY `idx_directory_listings_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "directory tables verified/created successfully.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "Error creating directory tables: " . $e->getMessage() . "\n";
    exit(1);
}
