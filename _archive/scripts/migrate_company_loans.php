<?php
// Company Loans Migration - In-House Loan System
// Creates all tables for the company loan system

try {
    $db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Loan Offers (promotional interest-free/reduced-rate offers)
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_offers` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(200) NOT NULL,
        `description` TEXT,
        `offer_type` ENUM('interest_free','reduced_rate') NOT NULL DEFAULT 'interest_free',
        `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
        `interest_free_months` INT UNSIGNED DEFAULT 0,
        `max_tenure_months` INT UNSIGNED DEFAULT 0,
        `max_amount` DECIMAL(15,2) DEFAULT 0.00,
        `terms_conditions` TEXT,
        `is_active` TINYINT(1) DEFAULT 1,
        `valid_from` DATE DEFAULT NULL,
        `valid_until` DATE DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed a default interest-free offer
    $check = $db->query("SELECT COUNT(*) FROM loan_offers")->fetchColumn();
    if ($check == 0) {
        $db->exec("INSERT INTO `loan_offers` (`name`, `description`, `offer_type`, `interest_free_months`, `max_tenure_months`, `max_amount`, `terms_conditions`, `valid_from`, `valid_until`) VALUES
        ('3-Year Interest-Free Offer', 'Interest-free financing for first 3 years on plot purchases. Interest applies if 3 consecutive EMIs are missed.', 'interest_free', 36, 60, 5000000.00, 'Terms: 3 consecutive missed EMIs trigger interest from that point. Max 3 years interest-free. Longer tenure (>3 years) attracts standard interest beyond 3-year period.', '2026-01-01', '2027-12-31'),
        ('1-Year Interest-Free Offer', 'Interest-free financing for first year. Perfect for short-term financing needs.', 'interest_free', 12, 24, 2500000.00, 'Terms: 3 consecutive missed EMIs trigger interest from that point. Max 1 year interest-free.', '2026-01-01', '2027-12-31'),
        ('Reduced Rate Offer (5%)', 'Special reduced interest rate of 5% p.a. for first 2 years, then standard rate applies.', 'reduced_rate', 0, 36, 3000000.00, NULL, '2026-01-01', '2026-12-31')");
        echo "Seeded 3 loan offers\n";
    }

    // 2. Company Loans (main loan table)
    $db->exec("CREATE TABLE IF NOT EXISTS `company_loans` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `customer_id` INT UNSIGNED NOT NULL,
        `plot_booking_id` INT UNSIGNED DEFAULT NULL,
        `property_id` INT UNSIGNED DEFAULT NULL,
        `offer_id` INT UNSIGNED DEFAULT NULL,
        `loan_number` VARCHAR(50) NOT NULL UNIQUE,
        `loan_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `interest_rate` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
        `interest_type` ENUM('fixed','reducing') NOT NULL DEFAULT 'reducing',
        `tenure_months` INT UNSIGNED NOT NULL DEFAULT 12,
        `emi_amount` DECIMAL(12,2) DEFAULT 0.00,
        `total_payable` DECIMAL(15,2) DEFAULT 0.00,
        `total_interest` DECIMAL(15,2) DEFAULT 0.00,
        `amount_paid` DECIMAL(15,2) DEFAULT 0.00,
        `balance_amount` DECIMAL(15,2) DEFAULT 0.00,
        `interest_free_months` INT UNSIGNED DEFAULT 0,
        `interest_free_active` TINYINT(1) DEFAULT 0,
        `interest_start_date` DATE DEFAULT NULL,
        `missed_consecutive_emis` INT UNSIGNED DEFAULT 0,
        `start_date` DATE NOT NULL,
        `end_date` DATE DEFAULT NULL,
        `status` ENUM('pending','active','completed','defaulted','foreclosed','cancelled') NOT NULL DEFAULT 'pending',
        `disbursed_at` DATETIME DEFAULT NULL,
        `disbursed_by` INT UNSIGNED DEFAULT NULL,
        `closed_at` DATETIME DEFAULT NULL,
        `purpose` VARCHAR(500) DEFAULT NULL,
        `notes` TEXT,
        `created_by` INT UNSIGNED DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_customer_id` (`customer_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_plot_booking` (`plot_booking_id`),
        INDEX `idx_offer` (`offer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Loan Installments (payment schedule)
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_installments` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `loan_id` INT UNSIGNED NOT NULL,
        `installment_no` INT UNSIGNED NOT NULL,
        `due_date` DATE NOT NULL,
        `principal_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `interest_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `paid_amount` DECIMAL(12,2) DEFAULT 0.00,
        `penalty_amount` DECIMAL(12,2) DEFAULT 0.00,
        `accrued_penalty` DECIMAL(12,2) DEFAULT 0.00,
        `waived_interest` DECIMAL(12,2) DEFAULT 0.00,
        `status` ENUM('pending','paid','overdue','partial') NOT NULL DEFAULT 'pending',
        `paid_at` DATETIME DEFAULT NULL,
        `payment_method` VARCHAR(50) DEFAULT NULL,
        `transaction_id` VARCHAR(100) DEFAULT NULL,
        `notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_loan_id` (`loan_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_due_date` (`due_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Loan Documents (legal documentation)
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_documents` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `loan_id` INT UNSIGNED NOT NULL,
        `document_type` ENUM('loan_agreement','promissory_note','demand_letter','default_notice','guarantor_agreement','early_settlement','foreclosure','noc') NOT NULL,
        `title` VARCHAR(200) NOT NULL,
        `document_number` VARCHAR(50) DEFAULT NULL,
        `content` LONGTEXT,
        `file_path` VARCHAR(500) DEFAULT NULL,
        `file_size` INT UNSIGNED DEFAULT 0,
        `mime_type` VARCHAR(100) DEFAULT 'text/html',
        `status` ENUM('draft','final','signed','expired','cancelled') NOT NULL DEFAULT 'draft',
        `signed_by_customer` TINYINT(1) DEFAULT 0,
        `signed_at` DATETIME DEFAULT NULL,
        `generated_by` INT UNSIGNED DEFAULT NULL,
        `generated_at` DATETIME DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_loan_id` (`loan_id`),
        INDEX `idx_doc_type` (`document_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Loan Guarantors
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_guarantors` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `loan_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(200) NOT NULL,
        `phone` VARCHAR(20) NOT NULL,
        `email` VARCHAR(200) DEFAULT NULL,
        `address` TEXT,
        `pan_number` VARCHAR(20) DEFAULT NULL,
        `aadhar_number` VARCHAR(20) DEFAULT NULL,
        `occupation` VARCHAR(100) DEFAULT NULL,
        `annual_income` DECIMAL(12,2) DEFAULT 0.00,
        `relationship` VARCHAR(100) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_loan_id` (`loan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 6. Loan Early Payment Incentives
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_early_incentives` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(200) NOT NULL,
        `description` TEXT,
        `incentive_type` ENUM('interest_discount','cashback','penalty_waiver','partial_waiver') NOT NULL DEFAULT 'interest_discount',
        `calculation_method` ENUM('percentage','fixed','slab') NOT NULL DEFAULT 'percentage',
        `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
        `fixed_amount` DECIMAL(12,2) DEFAULT 0.00,
        `min_remaining_months` INT UNSIGNED DEFAULT 0,
        `max_remaining_months` INT UNSIGNED DEFAULT 0,
        `min_loan_amount` DECIMAL(15,2) DEFAULT 0.00,
        `max_loan_amount` DECIMAL(15,2) DEFAULT 0.00,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed default early payment incentives
    $check2 = $db->query("SELECT COUNT(*) FROM loan_early_incentives")->fetchColumn();
    if ($check2 == 0) {
        $db->exec("INSERT INTO `loan_early_incentives` (`name`, `description`, `incentive_type`, `calculation_method`, `discount_percent`, `fixed_amount`, `min_remaining_months`) VALUES
        ('Early Bird Discount', '50% off remaining interest if paid off within 12 months', 'interest_discount', 'percentage', 50.00, 0.00, 0),
        ('Standard Early Settlement', '25% off remaining interest', 'interest_discount', 'percentage', 25.00, 0.00, 0),
        ('Penalty Waiver', 'Full penalty waiver on early settlement', 'penalty_waiver', 'fixed', 0.00, 0.00, 0)");
        echo "Seeded 3 early payment incentives\n";
    }

    // 7. Loan Activity Log
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_activity_log` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `loan_id` INT UNSIGNED NOT NULL,
        `action` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `old_value` TEXT,
        `new_value` TEXT,
        `performed_by` INT UNSIGNED DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_loan_id` (`loan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "All company loan tables created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
