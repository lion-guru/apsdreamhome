<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create upi_payments table
$pdo->exec("
CREATE TABLE IF NOT EXISTS upi_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    transaction_ref VARCHAR(64) NOT NULL UNIQUE,
    amount DECIMAL(15,2) NOT NULL,
    payment_type ENUM('emi', 'booking', 'down_payment', 'registry', 'penalty', 'other') DEFAULT 'emi',
    upi_url TEXT NOT NULL,
    qr_code LONGTEXT DEFAULT NULL,
    note TEXT DEFAULT NULL,
    payer_name VARCHAR(255) DEFAULT '',
    status ENUM('pending', 'paid', 'failed', 'expired', 'cancelled') DEFAULT 'pending',
    paid_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    webhook_data JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_booking (booking_id),
    KEY idx_status (status),
    KEY idx_transaction (transaction_ref),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "upi_payments table created/verified\n";