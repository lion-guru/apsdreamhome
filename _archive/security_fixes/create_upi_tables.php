<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create UPI payments table
$pdo->exec("
CREATE TABLE IF NOT EXISTS upi_payment_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    payment_type ENUM('emi', 'booking_token', 'agreement_value', 'down_payment', 'other') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    upi_id VARCHAR(100) NOT NULL COMMENT 'Merchant UPI ID',
    payer_name VARCHAR(100) DEFAULT NULL,
    payer_vpa VARCHAR(100) DEFAULT NULL,
    note VARCHAR(200) DEFAULT NULL,
    qr_code TEXT DEFAULT NULL COMMENT 'Base64 encoded QR code',
    upi_link TEXT DEFAULT NULL COMMENT 'Full UPI deep link',
    status ENUM('created', 'paid', 'expired', 'failed') DEFAULT 'created',
    expires_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    transaction_ref VARCHAR(100) DEFAULT NULL,
    webhook_data JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_booking (booking_id),
    KEY idx_status (status),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "upi_payment_links table created/verified\n";

// Create UPI config table
$pdo->exec("
CREATE TABLE IF NOT EXISTS upi_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_name VARCHAR(100) NOT NULL,
    upi_id VARCHAR(100) NOT NULL UNIQUE,
    merchant_code VARCHAR(50) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    api_credentials JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "upi_config table created/verified\n";

// Insert default UPI config
$stmt = $pdo->prepare("
    INSERT INTO upi_config (merchant_name, upi_id, merchant_code, is_active, is_default)
    VALUES (?, ?, ?, 1, 1)
    ON DUPLICATE KEY UPDATE merchant_name = VALUES(merchant_name), is_default = VALUES(is_default)
");
$stmt->execute(['APS Dream Home', 'apsdreamhome@upi', 'APS001']);

echo "Default UPI config inserted\n";

echo "\n=== All UPI tables created successfully ===\n";?>