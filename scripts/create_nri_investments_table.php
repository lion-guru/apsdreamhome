<?php
// Idempotent: NRI cross-border investment & FIRC compliance desk (PRO-5).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS nri_investments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            booking_id INT NOT NULL,
            customer_id INT NOT NULL,
            country_of_residence VARCHAR(100) NOT NULL,
            passport_number VARCHAR(50) NOT NULL,
            visa_type VARCHAR(50) NULL,
            bank_account_type ENUM('NRE','NRO','FCNR') NOT NULL DEFAULT 'NRE',
            firc_number VARCHAR(100) NULL,
            firc_doc_path VARCHAR(255) NULL,
            passport_doc_path VARCHAR(255) NULL,
            fema_declaration_signed TINYINT(1) NOT NULL DEFAULT 1,
            verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
            verified_by INT NULL,
            verified_at DATETIME NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_booking (booking_id),
            KEY idx_customer (customer_id),
            KEY idx_status (verification_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "nri_investments table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
