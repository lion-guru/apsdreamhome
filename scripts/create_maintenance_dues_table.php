<?php
// Idempotent: township society maintenance billing (makes RegistryNocService
// checkNoDues real instead of tolerated-missing).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS maintenance_dues (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            booking_id INT NOT NULL,
            colony_id INT NULL,
            customer_id INT NULL,
            bill_month TINYINT NOT NULL,
            bill_year SMALLINT NOT NULL,
            rate_per_sqft DECIMAL(10,2) NOT NULL DEFAULT 0,
            base_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            other_charges DECIMAL(12,2) NOT NULL DEFAULT 0,
            total_due DECIMAL(12,2) NOT NULL DEFAULT 0,
            paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            status ENUM('pending','paid','waived') NOT NULL DEFAULT 'pending',
            due_date DATE NULL,
            paid_at DATETIME NULL,
            receipt_id VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_booking_month (booking_id, bill_month, bill_year),
            KEY idx_colony_status (colony_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "maintenance_dues table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
