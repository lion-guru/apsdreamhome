#!/usr/bin/env php
<?php
/**
 * Create Buyer Tables â€” APS Dream Home
 * Creates buyer_interests and property_commissions tables
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Creating buyer_interests table..." . PHP_EOL;
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS buyer_interests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            property_id INT NULL,
            property_type VARCHAR(50) NOT NULL DEFAULT 'plot',
            budget_min DECIMAL(15,2) DEFAULT 0,
            budget_max DECIMAL(15,2) DEFAULT 0,
            preferred_location VARCHAR(200) DEFAULT '',
            preferred_area VARCHAR(100) DEFAULT '',
            area_min INT DEFAULT 0,
            area_max INT DEFAULT 0,
            bedrooms_needed TINYINT DEFAULT 0,
            requirements TEXT,
            status ENUM('active','matched','closed','expired') DEFAULT 'active',
            matched_property_id INT NULL,
            assigned_to BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_type (property_type),
            INDEX idx_budget (budget_min, budget_max)
        )
    ");
    echo "  âœ… buyer_interests created" . PHP_EOL;

    echo "Creating property_commissions table..." . PHP_EOL;
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            booking_id INT NULL,
            seller_id BIGINT UNSIGNED NOT NULL,
            buyer_id BIGINT UNSIGNED NULL,
            associate_id BIGINT UNSIGNED NULL,
            agent_id BIGINT UNSIGNED NULL,
            property_type VARCHAR(50) NOT NULL,
            listing_type ENUM('sell','rent') DEFAULT 'sell',
            sale_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 2.00,
            commission_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            company_share DECIMAL(15,2) NOT NULL DEFAULT 0,
            associate_share DECIMAL(15,2) NOT NULL DEFAULT 0,
            agent_share DECIMAL(15,2) NOT NULL DEFAULT 0,
            tds_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            gst_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            net_payout DECIMAL(15,2) NOT NULL DEFAULT 0,
            status ENUM('pending','approved','paid','cancelled') DEFAULT 'pending',
            paid_at TIMESTAMP NULL,
            metadata JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_property (property_id),
            INDEX idx_seller (seller_id),
            INDEX idx_associate (associate_id),
            INDEX idx_status (status)
        )
    ");
    echo "  âœ… property_commissions created" . PHP_EOL;

    // Check if metadata column exists in user_properties
    $check = $pdo->query("SHOW COLUMNS FROM user_properties LIKE 'metadata'");
    if ($check->rowCount() === 0) {
        echo "Adding metadata column to user_properties..." . PHP_EOL;
        $pdo->exec("ALTER TABLE user_properties ADD COLUMN metadata JSON AFTER status");
        echo "  âœ… metadata column added" . PHP_EOL;
    }

    echo PHP_EOL . "âœ… All buyer/commission tables created!" . PHP_EOL;

} catch (\Throwable $e) {
    echo "â�Œ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}?>