<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// eSign transactions table
$pdo->exec("
CREATE TABLE IF NOT EXISTS esign_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED DEFAULT NULL,
    document_id INT UNSIGNED DEFAULT NULL,
    document_type ENUM('booking_agreement', 'sale_deed', 'emi_agreement', 'cancellation_agreement', 'power_of_attorney', 'other') NOT NULL,
    transaction_id VARCHAR(64) NOT NULL UNIQUE,
    signer_name VARCHAR(255) NOT NULL,
    signer_aadhaar VARCHAR(14) NOT NULL COMMENT 'Masked: XXXX-XXXX-1234',
    signer_phone VARCHAR(20) NOT NULL,
    signer_email VARCHAR(255) DEFAULT '',
    esign_provider ENUM('nsl', 'cdsl', 'emudhra', 'nsdl', 'cdsl', 'mock') DEFAULT 'mock',
    document_hash VARCHAR(64) NOT NULL,
    document_content LONGTEXT NOT NULL COMMENT 'Base64 encoded document',
    template_id VARCHAR(100) DEFAULT NULL,
    status ENUM('initiated', 'pending_otp', 'signed', 'failed', 'expired', 'cancelled') DEFAULT 'initiated',
    otp_sent_at TIMESTAMP NULL DEFAULT NULL,
    otp_verified_at TIMESTAMP NULL DEFAULT NULL,
    signed_at TIMESTAMP NULL DEFAULT NULL,
    signed_document_url TEXT DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    response_data JSON DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_booking (booking_id),
    KEY idx_document (document_id),
    KEY idx_transaction (transaction_id),
    KEY idx_status (status),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "esign_transactions table created/verified\n";

echo "\n=== eSign tables created successfully ===\n";?>