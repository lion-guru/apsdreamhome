<?php
// Idempotent: 3-stage WhatsApp drip state per site visit.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS site_visit_drips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            visit_id INT NOT NULL,
            stage TINYINT NOT NULL,
            due_date DATE NOT NULL,
            sent_at DATETIME NULL,
            status ENUM('pending','sent','failed','skipped') NOT NULL DEFAULT 'pending',
            provider_response VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_visit_stage (visit_id, stage),
            KEY idx_due (status, due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "site_visit_drips table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
