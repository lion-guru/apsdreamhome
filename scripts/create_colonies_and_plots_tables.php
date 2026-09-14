<?php
/**
 * Migration: Create states, districts, colonies, and plots tables if not exists.
 * Idempotent: safe to run repeatedly.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();

    $db->exec("
        CREATE TABLE IF NOT EXISTS `states` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `country_id` int(10) unsigned DEFAULT NULL,
            `name` varchar(100) NOT NULL,
            `code` varchar(10) NOT NULL,
            `is_active` tinyint(4) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_states_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        CREATE TABLE IF NOT EXISTS `districts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `state_id` int(11) DEFAULT NULL,
            `name` varchar(100) NOT NULL,
            `code` varchar(10) NOT NULL,
            `is_active` tinyint(4) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_districts_state_id` (`state_id`),
            KEY `idx_districts_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        CREATE TABLE IF NOT EXISTS `colonies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `district_id` int(11) DEFAULT NULL,
            `name` varchar(150) NOT NULL,
            `slug` varchar(200) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `amenities` text DEFAULT NULL,
            `location` varchar(255) DEFAULT NULL,
            `status` varchar(50) DEFAULT 'planning',
            `available_plots` int(11) DEFAULT 0,
            `starting_price` decimal(12,2) DEFAULT 0.00,
            `image_path` varchar(500) DEFAULT NULL,
            `layout_image` varchar(500) DEFAULT NULL,
            `is_featured` tinyint(4) DEFAULT 0,
            `is_active` tinyint(4) DEFAULT 1,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_colonies_district` (`district_id`),
            KEY `idx_colonies_slug` (`slug`),
            KEY `idx_colonies_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        CREATE TABLE IF NOT EXISTS `plots` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `colony_id` int(11) NOT NULL,
            `plot_number` varchar(50) NOT NULL,
            `plot_type` enum('residential','commercial','industrial','mixed') DEFAULT 'residential',
            `area_sqft` decimal(10,2) DEFAULT 0.00,
            `price_per_sqft` decimal(10,2) DEFAULT 0.00,
            `total_price` decimal(12,2) DEFAULT 0.00,
            `status` enum('available','booked','sold','hold','reserved','under_construction') DEFAULT 'available',
            `is_featured` tinyint(4) DEFAULT 0,
            `is_active` tinyint(4) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_plots_colony_status` (`colony_id`,`status`,`is_active`),
            KEY `idx_plots_tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // Ensure at least one reference state and district exist
    $countStates = (int)$db->query("SELECT COUNT(*) FROM `states`")->fetchColumn();
    if ($countStates === 0) {
        $db->exec("INSERT INTO `states` (`id`, `name`, `code`, `is_active`) VALUES (1, 'Uttar Pradesh', 'UP', 1)");
    }
    $countDistricts = (int)$db->query("SELECT COUNT(*) FROM `districts`")->fetchColumn();
    if ($countDistricts === 0) {
        $db->exec("INSERT INTO `districts` (`id`, `state_id`, `name`, `code`, `is_active`) VALUES (1, 1, 'Gorakhpur', 'GKP', 1)");
    }

    echo "colonies, plots, districts, states tables verified/created successfully.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "Error creating colonies/plots tables: " . $e->getMessage() . "\n";
    exit(1);
}
