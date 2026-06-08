<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE TABLE IF NOT EXISTS `cash_collections` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `booking_id` INT DEFAULT NULL,
        `installment_id` INT DEFAULT NULL,
        `collector_id` INT NOT NULL COMMENT 'users.id of the field agent/associate',
        `customer_name` VARCHAR(255) NOT NULL,
        `amount` DECIMAL(12,2) NOT NULL,
        `collection_date` DATE NOT NULL,
        `payment_method` ENUM('cash','cheque','upi','bank_transfer') DEFAULT 'cash',
        `reference_number` VARCHAR(100) DEFAULT NULL,
        `receipt_photo` VARCHAR(500) DEFAULT NULL COMMENT 'path to uploaded receipt image',
        `notes` TEXT DEFAULT NULL,
        `status` ENUM('submitted','verified','rejected','reconciled') DEFAULT 'submitted',
        `verified_by` INT DEFAULT NULL COMMENT 'users.id of admin who verified',
        `verified_at` TIMESTAMP NULL DEFAULT NULL,
        `rejection_reason` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_collections_collector` (`collector_id`),
        KEY `idx_collections_date` (`collection_date`),
        KEY `idx_collections_status` (`status`),
        KEY `idx_collections_booking` (`booking_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `reconciliation_collections` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_date` DATE NOT NULL,
        `collector_id` INT NOT NULL,
        `total_submitted` DECIMAL(12,2) DEFAULT 0,
        `total_verified` DECIMAL(12,2) DEFAULT 0,
        `total_rejected` DECIMAL(12,2) DEFAULT 0,
        `discrepancy_amount` DECIMAL(12,2) DEFAULT 0,
        `status` ENUM('open','closed','discrepancy') DEFAULT 'open',
        `closed_by` INT DEFAULT NULL,
        `closed_at` TIMESTAMP NULL DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY `idx_recon_collector` (`collector_id`),
        KEY `idx_recon_date` (`session_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "✓ Tables created successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
