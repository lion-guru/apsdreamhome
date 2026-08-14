<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Check existing banks table - it already exists with basic info
// Let's create an IFSC/branch table with more details

$pdo->exec("
CREATE TABLE IF NOT EXISTS bank_branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_id INT UNSIGNED DEFAULT NULL,
    ifsc_code VARCHAR(11) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    branch_name VARCHAR(255) NOT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    district VARCHAR(100) DEFAULT NULL,
    state VARCHAR(100) DEFAULT NULL,
    pincode VARCHAR(6) DEFAULT NULL,
    contact VARCHAR(50) DEFAULT NULL,
    micr_code VARCHAR(20) DEFAULT NULL,
    swift_code VARCHAR(20) DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ifsc (ifsc_code),
    KEY idx_bank_name (bank_name),
    KEY idx_city_state (city, state),
    KEY idx_pincode (pincode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Bank branches table created/verified\n";?>