<?php
// Idempotent: daily site labor muster (mason/helper attendance + wages).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS site_muster_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            colony_id INT NULL,
            work_date DATE NOT NULL,
            worker_name VARCHAR(150) NOT NULL,
            worker_role ENUM('mason','helper','operator','supervisor','other') NOT NULL DEFAULT 'helper',
            daily_wage DECIMAL(10,2) NOT NULL DEFAULT 0,
            is_present TINYINT(1) NOT NULL DEFAULT 1,
            work_done VARCHAR(255) NULL,
            recorded_by INT NULL,
            notes VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_colony_date (colony_id, work_date),
            KEY idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "site_muster_log table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
