<?php
/**
 * Migration: Create service_configs table
 * 
 * The service_configs table is referenced by multiple services but was
 * never created by any migration. This migration creates it with the
 * expected columns.
 * 
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS.
 * 
 * Run via: php database/migrations/create_service_configs_table_s396.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS service_configs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_name VARCHAR(100) NOT NULL,
            config_key VARCHAR(100) NOT NULL,
            config_value TEXT NOT NULL,
            config_type ENUM('string','number','boolean','json') DEFAULT 'string',
            description TEXT,
            is_secret TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_service_configs_key (service_name, config_key),
            KEY idx_service_name (service_name),
            KEY idx_config_key (config_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ CREATED: service_configs\n";
} catch (\Throwable $e) {
    echo "❌ ERROR creating service_configs: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ service_configs migration complete!\n";