<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create pincodes table
$pdo->exec("
CREATE TABLE IF NOT EXISTS pincodes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pincode VARCHAR(6) NOT NULL,
    office_name VARCHAR(255) NOT NULL,
    office_type VARCHAR(50) NOT NULL,
    delivery_status VARCHAR(50) NOT NULL,
    division_name VARCHAR(100) DEFAULT NULL,
    region_name VARCHAR(100) DEFAULT NULL,
    circle_name VARCHAR(100) DEFAULT NULL,
    taluk VARCHAR(100) DEFAULT NULL,
    district_name VARCHAR(100) NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    state_code VARCHAR(10) DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pincode_office (pincode, office_name),
    KEY idx_pincode (pincode),
    KEY idx_district_state (district_name, state_name),
    KEY idx_state (state_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Pincodes table created/verified\n";?>