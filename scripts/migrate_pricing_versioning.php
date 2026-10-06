<?php
/**
 * Migration: Add pricing versioning tables
 * Run: php scripts/migrate_pricing_versioning.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getPdo();

$sql = "
CREATE TABLE IF NOT EXISTS pricing_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colony_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price_per_sqft DECIMAL(12,2) NOT NULL,
    premiums JSON NOT NULL,
    config JSON NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    parent_version_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_active (colony_id, is_active),
    INDEX idx_version (version),
    UNIQUE KEY uk_colony_version (colony_id, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pricing_plan_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pricing_plan_id BIGINT UNSIGNED NOT NULL,
    colony_id INT UNSIGNED NOT NULL,
    applied_by BIGINT UNSIGNED NOT NULL,
    plots_updated INT UNSIGNED DEFAULT 0,
    total_value DECIMAL(15,2) DEFAULT 0,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_colony_applied (colony_id, applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

echo "Creating pricing versioning tables...\n";
try {
    $pdo = Database::getInstance()->getPdo();
    $pdo->exec($sql);
    echo "✓ pricing_plans table created\n";
    echo "✓ pricing_plan_applications table created\n";
    echo "Done.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}