<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS plot_plc_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colony_id INT UNSIGNED NULL,
            name VARCHAR(100) NOT NULL,
            attribute_key VARCHAR(50) NOT NULL,
            condition_type ENUM('equals', 'equals_true', 'greater_than', 'contains', 'in') NOT NULL,
            condition_value VARCHAR(100) NOT NULL,
            charge_type ENUM('percentage', 'fixed_per_sqft', 'fixed_total') NOT NULL,
            charge_value DECIMAL(15,2) NOT NULL,
            priority INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_colony (colony_id),
            KEY idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "plot_plc_rules table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}