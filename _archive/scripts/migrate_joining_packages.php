<?php
/**
 * Migration: Create mlm_joining_packages and mlm_associate_registrations tables
 *
 * Supports the joining package commission system where:
 *  - Associates purchase a package (e.g. â‚¹1,000)
 *  - Direct sponsor bonus + multi-level bonuses are distributed up the chain
 *
 * Run: php scripts/migrate_joining_packages.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Migration: Joining Packages ===\n\n";

    // 1. mlm_joining_packages â€” package definitions
    echo "[1/2] Creating mlm_joining_packages... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mlm_joining_packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            package_code VARCHAR(20) NOT NULL UNIQUE,
            package_name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
            direct_sponsor_bonus DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            level_payout_json JSON DEFAULT NULL,
            description TEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // 2. mlm_associate_registrations â€” registration records
    echo "[2/2] Creating mlm_associate_registrations... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mlm_associate_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            package_id INT NOT NULL,
            registration_number VARCHAR(30) NOT NULL UNIQUE,
            payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
            payment_method VARCHAR(50) DEFAULT NULL,
            payment_reference VARCHAR(100) DEFAULT NULL,
            amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            paid_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_reg_user (user_id),
            KEY idx_reg_package (package_id),
            KEY idx_reg_status (payment_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";

    // 3. Seed default packages
    echo "\n[3/3] Seeding default packages... ";
    $stmt = $pdo->query("SELECT COUNT(*) FROM mlm_joining_packages");
    $count = (int) $stmt->fetchColumn();

    if ($count === 0) {
        $pdo->exec("
            INSERT INTO mlm_joining_packages (package_code, package_name, price, direct_sponsor_bonus, level_payout_json, description) VALUES
            ('BASIC',    'Basic Associate',    1000.00, 200.00,  '{\"1\": 100, \"2\": 50, \"3\": 25}',  'Entry-level associate package with basic commission eligibility'),
            ('PREMIUM',  'Premium Associate',   5000.00, 1000.00, '{\"1\": 500, \"2\": 250, \"3\": 100}', 'Premium package with enhanced commission rates'),
            ('BUSINESS', 'Business Associate',  25000.00, 5000.00, '{\"1\": 2500, \"2\": 1000, \"3\": 500}', 'Enterprise package for serious network builders')
        ");
        echo "3 packages seeded\n";
    } else {
        echo "SKIP ({$count} packages already exist)\n";
    }

    echo "\nMigration complete.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>