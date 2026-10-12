<?php
// Idempotent: land litigation / court stay risk radar.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS land_litigations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            khasra_no VARCHAR(100) NOT NULL,
            village VARCHAR(100) NOT NULL,
            court_name VARCHAR(150) NOT NULL,
            case_number VARCHAR(100) NOT NULL,
            petitioner_name VARCHAR(150) NOT NULL,
            respondent_name VARCHAR(150) NOT NULL,
            case_type VARCHAR(100) NULL,
            stay_order_active TINYINT(1) NOT NULL DEFAULT 0,
            stay_order_details TEXT NULL,
            next_hearing_date DATE NULL,
            advocate_name VARCHAR(100) NULL,
            advocate_phone VARCHAR(20) NULL,
            status ENUM('pending','decided_in_favor','decided_against','settled') NOT NULL DEFAULT 'pending',
            notes TEXT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_khasra (khasra_no),
            KEY idx_stay (stay_order_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "land_litigations table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
