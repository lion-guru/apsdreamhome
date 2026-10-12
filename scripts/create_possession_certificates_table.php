<?php
// Idempotent: tracked possession handover certificates.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS possession_certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            booking_id INT NOT NULL,
            plot_id INT NULL,
            customer_id INT NULL,
            certificate_no VARCHAR(50) NULL,
            handover_date DATE NOT NULL,
            demarcation_done TINYINT(1) NOT NULL DEFAULT 1,
            pillar_photo_path VARCHAR(255) NULL,
            site_engineer_name VARCHAR(100) NULL,
            site_engineer_phone VARCHAR(20) NULL,
            handover_status ENUM('provisional','final') NOT NULL DEFAULT 'final',
            remarks TEXT NULL,
            issued_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_cert_no (certificate_no),
            KEY idx_booking (booking_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "possession_certificates table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
