<?php
/**
 * Migration: Create kyc_verification_logs table
 * Stores every PAN/Aadhaar verification attempt for audit trail
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

    echo "Creating kyc_verification_logs...\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS `kyc_verification_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `type` ENUM('pan','aadhaar') NOT NULL,
        `identifier` VARCHAR(50) NOT NULL,
        `success` TINYINT(1) NOT NULL DEFAULT 0,
        `message` VARCHAR(255) DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
        PRIMARY KEY (`id`),
        KEY `idx_type_created` (`type`, `created_at`),
        KEY `idx_success` (`success`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "✓ kyc_verification_logs created\n";

    // Verify kyc_requests table has correct schema
    $stmt = $pdo->query("SHOW TABLES LIKE 'kyc_requests'");
    if ($stmt->rowCount() > 0) {
        echo "✓ kyc_requests table exists\n";
        
        // Check if kyc_status column exists in users table
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'kyc_status'");
        if ($stmt->rowCount() > 0) {
            echo "✓ users.kyc_status column exists\n";
        } else {
            echo "⚠ users.kyc_status column missing — adding...\n";
            $pdo->exec("ALTER TABLE users ADD COLUMN kyc_status ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none'");
            echo "✓ users.kyc_status added\n";
        }
    }

    echo "\n✅ KYC verification logs migration complete\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
