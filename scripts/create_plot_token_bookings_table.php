<?php
// Idempotent: spot token advance (bayana) + 7-day plot lock.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS plot_token_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            plot_id INT NOT NULL,
            colony_id INT NULL,
            associate_id INT NULL,
            customer_name VARCHAR(150) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_aadhaar VARCHAR(20) NULL,
            token_amount DECIMAL(12,2) NOT NULL,
            payment_mode ENUM('upi','cash','bank_transfer','cheque') NOT NULL DEFAULT 'upi',
            payment_ref VARCHAR(100) NULL,
            receipt_no VARCHAR(50) NULL,
            lock_expires_at DATETIME NOT NULL,
            status ENUM('active','converted','expired','refunded') NOT NULL DEFAULT 'active',
            notes TEXT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_plot (plot_id),
            KEY idx_receipt (receipt_no),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "plot_token_bookings table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
