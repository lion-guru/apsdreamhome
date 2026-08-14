<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Property tax rates table
$pdo->exec("
CREATE TABLE IF NOT EXISTS property_tax_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_code VARCHAR(10) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zone VARCHAR(100) DEFAULT '',
    property_type ENUM('residential', 'commercial', 'industrial', 'vacant_land', 'institutional') NOT NULL,
    tax_rate_per_sqft DECIMAL(10,4) NOT NULL COMMENT 'Annual tax per sq ft',
    min_tax_amount DECIMAL(12,2) DEFAULT 0,
    max_tax_amount DECIMAL(12,2) DEFAULT NULL,
    rebate_percent DECIMAL(5,2) DEFAULT 0 COMMENT 'Rebate for early payment',
    penalty_percent_per_month DECIMAL(5,2) DEFAULT 1.0 COMMENT 'Late payment penalty per month',
    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tax_rate (state_code, city, zone, property_type, effective_from),
    KEY idx_state_city (state_code, city),
    KEY idx_active (is_active),
    KEY idx_effective (effective_from, effective_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "property_tax_rates table created/verified\n";

// Property tax assessments table
$pdo->exec("
CREATE TABLE IF NOT EXISTS property_tax_assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    assessment_year YEAR NOT NULL,
    property_type ENUM('residential', 'commercial', 'industrial', 'vacant_land', 'institutional') NOT NULL,
    built_up_area_sqft DECIMAL(12,2) DEFAULT 0,
    land_area_sqft DECIMAL(12,2) DEFAULT 0,
    carpet_area_sqft DECIMAL(12,2) DEFAULT 0,
    tax_rate_applied DECIMAL(10,4) NOT NULL,
    annual_tax_amount DECIMAL(12,2) NOT NULL,
    rebate_amount DECIMAL(12,2) DEFAULT 0,
    penalty_amount DECIMAL(12,2) DEFAULT 0,
    total_due DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    balance_due DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'partial', 'paid', 'overdue', 'disputed', 'exempted') DEFAULT 'pending',
    due_date DATE DEFAULT NULL,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    payment_ref VARCHAR(100) DEFAULT '',
    notes TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_property (property_id),
    KEY idx_year (assessment_year),
    KEY idx_status (status),
    KEY idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "property_tax_assessments table created/verified\n";

// Sample tax rates for UP (Gorakhpur, Lucknow) and Delhi
$taxRates = [
    // Gorakhpur
    ['UP', 'Gorakhpur', 'Zone A', 'residential', 8.00, 500, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone A', 'commercial', 25.00, 1000, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone A', 'industrial', 30.00, 2000, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone A', 'vacant_land', 5.00, 200, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone A', 'institutional', 15.00, 500, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    ['UP', 'Gorakhpur', 'Zone B', 'residential', 6.00, 400, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone B', 'commercial', 20.00, 800, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone B', 'industrial', 25.00, 1500, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone B', 'vacant_land', 4.00, 150, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone B', 'institutional', 12.00, 400, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    ['UP', 'Gorakhpur', 'Zone C', 'residential', 4.00, 300, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone C', 'commercial', 15.00, 600, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone C', 'industrial', 20.00, 1000, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone C', 'vacant_land', 3.00, 100, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Gorakhpur', 'Zone C', 'institutional', 10.00, 300, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    // Lucknow
    ['UP', 'Lucknow', 'Zone A', 'residential', 12.00, 600, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone A', 'commercial', 35.00, 2000, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone A', 'industrial', 40.00, 3000, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone A', 'vacant_land', 8.00, 500, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone A', 'institutional', 20.00, 800, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    ['UP', 'Lucknow', 'Zone B', 'residential', 8.00, 500, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone B', 'commercial', 25.00, 1500, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone B', 'industrial', 30.00, 2000, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone B', 'vacant_land', 6.00, 400, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone B', 'institutional', 15.00, 600, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    ['UP', 'Lucknow', 'Zone C', 'residential', 5.00, 400, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone C', 'commercial', 18.00, 1000, NULL, 5.0, 1.5, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone C', 'industrial', 22.00, 1500, NULL, 5.0, 2.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone C', 'vacant_land', 4.00, 300, NULL, 5.0, 1.0, '2024-04-01', NULL],
    ['UP', 'Lucknow', 'Zone C', 'institutional', 12.00, 500, NULL, 10.0, 1.0, '2024-04-01', NULL],
    
    // Delhi
    ['DL', 'New Delhi', 'A', 'residential', 15.00, 1000, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'A', 'commercial', 50.00, 5000, NULL, 10.0, 1.5, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'A', 'industrial', 60.00, 10000, NULL, 10.0, 2.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'A', 'vacant_land', 12.00, 800, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'A', 'institutional', 30.00, 2000, NULL, 15.0, 1.0, '2024-04-01', NULL],
    
    ['DL', 'New Delhi', 'B', 'residential', 12.00, 800, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'B', 'commercial', 40.00, 4000, NULL, 10.0, 1.5, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'B', 'industrial', 50.00, 8000, NULL, 10.0, 2.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'B', 'vacant_land', 10.00, 600, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'B', 'institutional', 25.00, 1500, NULL, 15.0, 1.0, '2024-04-01', NULL],
    
    ['DL', 'New Delhi', 'C', 'residential', 8.00, 600, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'C', 'commercial', 30.00, 3000, NULL, 10.0, 1.5, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'C', 'industrial', 40.00, 6000, NULL, 10.0, 2.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'C', 'vacant_land', 7.00, 500, NULL, 10.0, 1.0, '2024-04-01', NULL],
    ['DL', 'New Delhi', 'C', 'institutional', 20.00, 1200, NULL, 15.0, 1.0, '2024-04-01', NULL],
];

$stmt = $pdo->prepare("
    INSERT INTO property_tax_rates 
    (state_code, city, zone, property_type, tax_rate_per_sqft, min_tax_amount, max_tax_amount, 
     rebate_percent, penalty_percent_per_month, effective_from, effective_to, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
        tax_rate_per_sqft = VALUES(tax_rate_per_sqft),
        min_tax_amount = VALUES(min_tax_amount),
        max_tax_amount = VALUES(max_tax_amount),
        rebate_percent = VALUES(rebate_percent),
        penalty_percent_per_month = VALUES(penalty_percent_per_month),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($taxRates as $rate) {
    try {
        $stmt->execute($rate);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted property tax rates\n";

echo "\n=== Property Tax Calculator tables and data created successfully ===\n";?>