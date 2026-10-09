<?php
/**
 * Migration: Create colonies table
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS `colonies` (
    `id` int(11) NOT NULL,
    `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `district_id` int(11) NOT NULL,
    `name` varchar(150) NOT NULL,
    `slug` varchar(200) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `amenities` text DEFAULT NULL,
    `key_highlights` longtext DEFAULT NULL,
    `nearby_places` longtext DEFAULT NULL,
    `gallery_images` longtext DEFAULT NULL,
    `youtube_video_url` varchar(500) DEFAULT NULL,
    `virtual_tour_url` varchar(500) DEFAULT NULL,
    `meta_title` varchar(200) DEFAULT NULL,
    `meta_description` text DEFAULT NULL,
    `map_link` varchar(500) DEFAULT NULL,
    `total_plots` int(11) DEFAULT 0,
    `total_area_acres` decimal(10,2) DEFAULT 0.00,
    `total_area_sqft` decimal(12,2) DEFAULT 0.00,
    `land_owner_name` varchar(255) DEFAULT NULL,
    `estimated_land_cost` decimal(15,2) DEFAULT 0.00,
    `rera_project_id` int(11) DEFAULT NULL,
    `rera_number` varchar(50) DEFAULT NULL,
    `location` varchar(255) DEFAULT NULL,
    `status` varchar(50) DEFAULT 'planning',
    `available_plots` int(11) DEFAULT 0,
    `starting_price` decimal(12,2) DEFAULT 0.00,
    `image_path` varchar(500) DEFAULT NULL,
    `layout_image` varchar(500) DEFAULT NULL,
    `banner_image` varchar(500) DEFAULT NULL,
    `brochure_path` varchar(500) DEFAULT NULL,
    `colony_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colony_documents`)),
    `is_featured` tinyint(4) DEFAULT 0,
    `is_active` tinyint(4) DEFAULT 1,
    `pipeline_stage` varchar(50) DEFAULT 'land_acquisition',
    `show_plots_publicly` tinyint(1) DEFAULT 0,
    `contact_phone` varchar(20) DEFAULT NULL,
    `contact_email` varchar(100) DEFAULT NULL,
    `land_cost` decimal(15,2) DEFAULT 0.00,
    `min_price_per_sqft` decimal(10,2) DEFAULT 0.00,
    `phase` varchar(50) DEFAULT NULL,
    `block_count` int(11) DEFAULT 0,
    `latitude` decimal(10,8) DEFAULT NULL,
    `longitude` decimal(11,8) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `deleted_at` datetime DEFAULT NULL,
    KEY `idx_tenant` (`tenant_id`),
    KEY `idx_status` (`status`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Colonies/Projects';
";

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    $db->exec($sql);
    echo "✅ colonies table created\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}