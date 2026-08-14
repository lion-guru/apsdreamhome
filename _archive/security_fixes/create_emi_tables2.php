<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// EMI agreements for bookings
$pdo->exec("
CREATE TABLE IF NOT EXISTS booking_emi_agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    agreement_type ENUM('emi_agreement', 'loan_agreement', 'cancellation_policy', 'prepayment_terms') DEFAULT 'emi_agreement',
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    status ENUM('draft', 'pending_signature', 'signed', 'completed', 'rejected') DEFAULT 'pending_signature',
    signed_at TIMESTAMP NULL DEFAULT NULL,
    signed_by BIGINT UNSIGNED DEFAULT NULL,
    signature_data LONGTEXT DEFAULT NULL,
    signature_type ENUM('digital', 'esign', 'video', 'physical') DEFAULT 'digital',
    ip_address VARCHAR(45) DEFAULT '',
    user_agent TEXT DEFAULT '',
    video_url TEXT DEFAULT NULL,
    terms_accepted TINYINT(1) DEFAULT 0,
    privacy_accepted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_booking (booking_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "booking_emi_agreements table created/verified\n";

// EMI agreement installments
$pdo->exec("
CREATE TABLE IF NOT EXISTS booking_emi_installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agreement_id BIGINT UNSIGNED NOT NULL,
    installment_no INT NOT NULL,
    due_date DATE NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_amount DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'partial') DEFAULT 'pending',
    paid_amount DECIMAL(15,2) DEFAULT 0,
    paid_date DATE DEFAULT NULL,
    payment_id INT UNSIGNED DEFAULT NULL,
    is_moratorium TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agreement_id) REFERENCES booking_emi_agreements(id) ON DELETE CASCADE,
    KEY idx_agreement (agreement_id),
    KEY idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "booking_emi_installments table created/verified\n";

echo "\nAll EMI tables created successfully!\n";?>