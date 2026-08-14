<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

try {
    // 1. Create telecaller_performance table without explicit FK to avoid InnoDB constraint errors if schema types mismatch slightly
    $sql1 = "CREATE TABLE IF NOT EXISTS `telecaller_performance` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
        `telecaller_id` int(11) NOT NULL,
        `period_start` date NOT NULL,
        `period_end` date NOT NULL,
        `total_calls` int(11) DEFAULT 0,
        `connected_calls` int(11) DEFAULT 0,
        `leads_generated` int(11) DEFAULT 0,
        `leads_converted` int(11) DEFAULT 0,
        `total_commission` decimal(15,2) DEFAULT 0.00,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `telecaller_id` (`telecaller_id`),
        KEY `tenant_id` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql1);
    echo "telecaller_performance created successfully.<br>";

    // 2. Alter employees table to add incentive logic columns
    $sql2 = "ALTER TABLE `employees` 
        ADD COLUMN `incentive_model` ENUM('salary_only', 'commission_only', 'salary_plus_commission') DEFAULT 'salary_only' AFTER `salary`,
        ADD COLUMN `commission_rate` DECIMAL(10,2) DEFAULT 0.00 AFTER `incentive_model`,
        ADD COLUMN `commission_type` ENUM('flat', 'percentage') DEFAULT 'percentage' AFTER `commission_rate`;";
    
    $pdo->exec($sql2);
    echo "employees altered successfully.<br>";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "telecaller_performance created successfully.<br>";
        echo "Columns already exist in employees table.<br>";
    } else {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}?>