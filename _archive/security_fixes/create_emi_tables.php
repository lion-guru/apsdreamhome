<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Bank interest rates table
$pdo->exec("
CREATE TABLE IF NOT EXISTS bank_interest_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_code VARCHAR(20) NOT NULL UNIQUE,
    bank_name VARCHAR(100) NOT NULL,
    rate DECIMAL(5,2) NOT NULL COMMENT 'Interest rate % p.a.',
    max_tenure INT DEFAULT 30 COMMENT 'Max tenure in years',
    min_loan_amount DECIMAL(15,2) DEFAULT 0,
    max_loan_amount DECIMAL(15,2) DEFAULT 0,
    processing_fee_percent DECIMAL(5,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "bank_interest_rates table created/verified\n";

// EMI calculations history
$pdo->exec("
CREATE TABLE IF NOT EXISTS emi_calculations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    property_id INT UNSIGNED DEFAULT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    tenure_years INT NOT NULL,
    emi_amount DECIMAL(15,2) NOT NULL,
    total_interest DECIMAL(15,2) NOT NULL,
    total_payment DECIMAL(15,2) NOT NULL,
    calculation_data JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_property (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "emi_calculations table created/verified\n";

// Payment plans
$pdo->exec("
CREATE TABLE IF NOT EXISTS payment_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_type ENUM('construction_linked', 'time_linked', 'flexible', 'down_payment', 'possession_linked') DEFAULT 'construction_linked',
    total_amount DECIMAL(15,2) NOT NULL,
    down_payment_percent DECIMAL(5,2) DEFAULT 20,
    number_of_installments INT NOT NULL DEFAULT 1,
    installment_frequency ENUM('monthly', 'quarterly', 'half_yearly', 'yearly', 'milestone') DEFAULT 'milestone',
    interest_applicable TINYINT(1) DEFAULT 0,
    interest_rate DECIMAL(5,2) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_property (property_id),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "payment_plans table created/verified\n";

// Payment plan milestones
$pdo->exec("
CREATE TABLE IF NOT EXISTS payment_plan_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id BIGINT UNSIGNED NOT NULL,
    milestone_order INT NOT NULL,
    milestone_name VARCHAR(100) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL COMMENT 'Percentage of total amount',
    amount DECIMAL(15,2) NOT NULL,
    due_date DATE DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_mandatory TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES payment_plans(id) ON DELETE CASCADE,
    KEY idx_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "payment_plan_milestones table created/verified\n";

// Buyer payment schedules
$pdo->exec("
CREATE TABLE IF NOT EXISTS buyer_payment_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    property_id INT UNSIGNED NOT NULL,
    payment_plan_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2) NOT NULL,
    status ENUM('active', 'completed', 'defaulted', 'cancelled') DEFAULT 'active',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_property (property_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "buyer_payment_schedules table created/verified\n";

// Payment installments
$pdo->exec("
CREATE TABLE IF NOT EXISTS payment_installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id BIGINT UNSIGNED NOT NULL,
    installment_number INT NOT NULL,
    milestone_id BIGINT UNSIGNED DEFAULT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    due_date DATE NOT NULL,
    paid_date DATE DEFAULT NULL,
    status ENUM('pending', 'partial', 'paid', 'overdue', 'waived') DEFAULT 'pending',
    late_fee DECIMAL(10,2) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES buyer_payment_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (milestone_id) REFERENCES payment_plan_milestones(id) ON DELETE SET NULL,
    KEY idx_schedule (schedule_id),
    KEY idx_due_date (due_date),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "payment_installments table created/verified\n";

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