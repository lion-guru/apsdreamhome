<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create booking_document_signatures table
$pdo->exec("
CREATE TABLE IF NOT EXISTS booking_document_signatures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED DEFAULT NULL,
    signature_data LONGTEXT NOT NULL,
    signature_type ENUM('digital', 'esign', 'video', 'physical') DEFAULT 'digital',
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT '',
    user_agent TEXT DEFAULT '',
    video_consent TINYINT(1) DEFAULT 0,
    video_url TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_document_booking (document_id, booking_id),
    KEY idx_booking (booking_id),
    KEY idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "booking_document_signatures table created/verified\n";

// Create booking_video_consents table
$pdo->exec("
CREATE TABLE IF NOT EXISTS booking_video_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    video_url TEXT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT '',
    terms_accepted TINYINT(1) DEFAULT 0,
    privacy_accepted TINYINT(1) DEFAULT 0,
    UNIQUE KEY uk_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "booking_video_consents table created/verified\n";

// Create booking_digital_agreements table for the digital booking flow
$pdo->exec("
CREATE TABLE IF NOT EXISTS booking_digital_agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    agreement_type ENUM('booking_agreement', 'emi_terms', 'cancellation_policy', 'privacy_policy', 'terms_conditions') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    status ENUM('draft', 'pending_signature', 'signed', 'completed', 'rejected') DEFAULT 'pending_signature',
    signed_at TIMESTAMP NULL DEFAULT NULL,
    signed_by BIGINT UNSIGNED DEFAULT NULL,
    signature_data LONGTEXT DEFAULT NULL,
    signature_type ENUM('digital', 'esign', 'video') DEFAULT 'digital',
    ip_address VARCHAR(45) DEFAULT '',
    user_agent TEXT DEFAULT '',
    video_url TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_booking_type (booking_id, agreement_type),
    KEY idx_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "booking_digital_agreements table created/verified\n";

echo "All tables created successfully!\n";