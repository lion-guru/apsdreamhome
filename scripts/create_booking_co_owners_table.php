<?php
// Idempotent: joint-allotment co-owners per booking (PRO-3).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS booking_co_owners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            booking_id INT NOT NULL,
            full_name VARCHAR(150) NOT NULL,
            relationship VARCHAR(50) NULL,
            gender ENUM('male','female','other') NOT NULL DEFAULT 'male',
            aadhaar_no VARCHAR(20) NULL,
            pan_no VARCHAR(20) NULL,
            share_percentage DECIMAL(5,2) NOT NULL DEFAULT 50.00,
            photo_path VARCHAR(255) NULL,
            is_primary_contact TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_booking (booking_id),
            KEY idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "booking_co_owners table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
