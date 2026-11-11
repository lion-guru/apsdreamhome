<?php
/**
 * Migration: Create land_brokers table
 * 
 * The unify_collations_s218.php migration checks land_brokers.rera_number column,
 * but the table was never created.
 * 
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS.
 * 
 * Run via: php database/migrations/create_land_brokers_table_s402.php
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
        CREATE TABLE IF NOT EXISTS land_brokers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            rera_number VARCHAR(100) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_land_brokers_rera_number (rera_number),
            KEY idx_status (status),
            KEY idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ CREATED: land_brokers\n";
} catch (\Throwable $e) {
    echo "❌ ERROR creating land_brokers: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ land_brokers table migration complete!\n";