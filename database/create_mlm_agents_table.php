<?php
/**
 * Script to create the mlm_agents table that matches the current PHP code expectations
 */

// Database configuration
require_once __DIR__ . '/../includes/config.php';

try {
    // Create mlm_agents table
    $sql = "
    CREATE TABLE IF NOT EXISTS `mlm_agents` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `full_name` varchar(255) NOT NULL,
        `mobile` varchar(20) NOT NULL,
        `email` varchar(100) NOT NULL,
        `aadhar_number` varchar(20) DEFAULT NULL,
        `pan_number` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `state` varchar(100) DEFAULT NULL,
        `district` varchar(100) DEFAULT NULL,
        `pin_code` varchar(10) DEFAULT NULL,
        `bank_account` varchar(50) DEFAULT NULL,
        `ifsc_code` varchar(20) DEFAULT NULL,
        `referral_code` varchar(20) NOT NULL,
        `sponsor_id` int(11) DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `current_level` varchar(50) DEFAULT 'Associate',
        `total_business` decimal(15,2) DEFAULT 0.00,
        `total_team_size` int(11) DEFAULT 0,
        `direct_referrals` int(11) DEFAULT 0,
        `status` enum('active','inactive','pending') DEFAULT 'pending',
        `registration_date` datetime DEFAULT NULL,
        `last_login` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_mobile` (`mobile`),
        UNIQUE KEY `unique_email` (`email`),
        UNIQUE KEY `unique_referral_code` (`referral_code`),
        KEY `sponsor_id` (`sponsor_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($db_connection->query($sql) === TRUE) {
        echo "Table 'mlm_agents' created successfully\n";
    } else {
        echo "Error creating table: " . $db_connection->error . "\n";
    }
    
    // Insert the existing associate data into mlm_agents table
    $sql = "
    INSERT INTO mlm_agents 
        (full_name, mobile, email, referral_code, password, current_level, status, registration_date)
    SELECT 
        a.name, a.phone, a.email, 
        CONCAT('APS', UPPER(LEFT(a.name, 2)), FLOOR(RAND() * 9000 + 1000)) as referral_code,
        u.password, 'Associate', a.status, a.join_date
    FROM associates a
    JOIN users u ON a.id = u.id
    WHERE NOT EXISTS (
        SELECT 1 FROM mlm_agents ma WHERE ma.email = a.email
    )
    ";
    
    if ($db_connection->query($sql) === TRUE) {
        echo "Existing associate data migrated to mlm_agents table\n";
    } else {
        echo "Error migrating data: " . $db_connection->error . "\n";
    }
    
    echo "mlm_agents table setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>