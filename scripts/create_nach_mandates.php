<?php
/**
 * Create nach_mandates table for NACH/e-Mandate registration
 * Run once: php scripts/create_nach_mandates.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS nach_mandates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    mandate_type VARCHAR(50) NOT NULL DEFAULT 'nach',
    bank_name VARCHAR(255) NOT NULL,
    bank_account_number VARCHAR(50) NOT NULL,
    ifsc_code VARCHAR(20) NOT NULL,
    account_holder_name VARCHAR(255) NOT NULL,
    mandate_amount DECIMAL(12,2) NOT NULL,
    frequency VARCHAR(50) NOT NULL DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    next_debit_date DATE DEFAULT NULL,
    status ENUM('active','paused','cancelled','completed') DEFAULT 'active',
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking (booking_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_next_debit (next_debit_date),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->query($sql);
    echo "nach_mandates table created OK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>