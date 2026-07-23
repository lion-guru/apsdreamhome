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
    state_name VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zone VARCHAR(100) DEFAULT '',
    property_type ENUM('residential', 'commercial', 'industrial', 'vacant_land', 'institutional', 'mixed_use') NOT NULL,
    tax_rate_per_sqft DECIMAL(10,2) NOT NULL,
    min_tax_amount DECIMAL(12,2) DEFAULT 500,
    max_tax_amount DECIMAL(15,2) DEFAULT NULL,
    rebate_percent DECIMAL(5,2) DEFAULT 0 COMMENT 'Early payment rebate %',
    penalty_percent_per_month DECIMAL(5,2) DEFAULT 2.0 COMMENT 'Penalty % per month overdue',
    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tax_rate (state_code, city, zone, property_type, effective_from),
    KEY idx_state_city (state_code, city),
    KEY idx_property_type (property_type),
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
    property_type ENUM('residential', 'commercial', 'industrial', 'vacant_land', 'institutional', 'mixed_use') NOT NULL,
    built_up_area_sqft DECIMAL(12,2) NOT NULL,
    land_area_sqft DECIMAL(12,2) NOT NULL,
    tax_rate_applied DECIMAL(10,2) NOT NULL,
    annual_tax_amount DECIMAL(12,2) NOT NULL,
    rebate_amount DECIMAL(12,2) DEFAULT 0,
    penalty_amount DECIMAL(12,2) DEFAULT 0,
    total_due DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'partial', 'exempted', 'disputed') DEFAULT 'pending',
    due_date DATE DEFAULT NULL,
    paid_date DATE DEFAULT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    receipt_number VARCHAR(50) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_property_year (property_id, assessment_year),
    KEY idx_property (property_id),
    KEY idx_year (assessment_year),
    KEY idx_status (status),
    KEY idx_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "property_tax_assessments table created/verified\n";

// Seed tax rates for UP (Gorakhpur/Lucknow)
$rates = [
    // Gorakhpur - Residential
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone A', 'residential', 2.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone B', 'residential', 2.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone C', 'residential', 1.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone D', 'residential', 1.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone A', 'commercial', 8.00, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone B', 'commercial', 6.50, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone C', 'commercial', 5.00, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone D', 'commercial', 4.00, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone A', 'industrial', 4.00, 1000, null, 2.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone B', 'industrial', 3.50, 1000, null, 2.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', 'Zone C', 'industrial', 3.00, 1000, null, 2.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', '', 'vacant_land', 0.50, 200, null, 2.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Gorakhpur', '', 'institutional', 3.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    
    // Lucknow - Residential
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone A', 'residential', 3.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone B', 'residential', 3.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone C', 'residential', 2.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone D', 'residential', 2.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone A', 'commercial', 10.00, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone B', 'commercial', 8.50, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone C', 'commercial', 7.00, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', 'Zone D', 'commercial', 5.50, 1000, null, 3.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', '', 'vacant_land', 0.75, 200, null, 2.0, 2.0, '2024-04-01', null],
    ['UP', 'Uttar Pradesh', 'Lucknow', '', 'institutional', 4.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    
    // Delhi
    ['DL', 'Delhi', 'New Delhi', 'A', 'residential', 5.00, 1000, null, 10.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'B', 'residential', 4.00, 1000, null, 10.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'C', 'residential', 3.00, 1000, null, 10.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'D', 'residential', 2.00, 1000, null, 10.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'A', 'commercial', 15.00, 2000, null, 5.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'B', 'commercial', 12.00, 2000, null, 5.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'C', 'commercial', 9.00, 2000, null, 5.0, 2.0, '2024-04-01', null],
    ['DL', 'Delhi', 'New Delhi', 'D', 'commercial', 6.00, 2000, null, 5.0, 2.0, '2024-04-01', null],
    
    // Maharashtra (Mumbai/Pune)
    ['MH', 'Maharashtra', 'Mumbai', 'A', 'residential', 6.00, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['MH', 'Maharashtra', 'Mumbai', 'B', 'residential', 5.00, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['MH', 'Maharashtra', 'Mumbai', 'C', 'residential', 4.00, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['MH', 'Maharashtra', 'Mumbai', 'D', 'residential', 3.00, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['MH', 'Maharashtra', 'Mumbai', 'A', 'commercial', 20.00, 2000, null, 3.0, 2.0, '2024-04-01', null],
    ['MH', 'Maharashtra', 'Mumbai', 'B', 'commercial', 16.00, 2000, null, 3.0, 2.0, '2024-04-01', null],
    
    // Karnataka (Bangalore)
    ['KA', 'Karnataka', 'Bengaluru', 'A', 'residential', 4.50, 800, null, 5.0, 2.0, '2024-04-01', null],
    ['KA', 'Karnataka', 'Bengaluru', 'B', 'residential', 3.50, 800, null, 5.0, 2.0, '2024-04-01', null],
    ['KA', 'Karnataka', 'Bengaluru', 'C', 'residential', 2.50, 800, null, 5.0, 2.0, '2024-04-01', null],
    ['KA', 'Karnataka', 'Bengaluru', 'D', 'residential', 1.50, 800, null, 5.0, 2.0, '2024-04-01', null],
    ['KA', 'Karnataka', 'Bengaluru', 'A', 'commercial', 18.00, 1500, null, 3.0, 2.0, '2024-04-01', null],
    
    // Haryana (Gurgaon)
    ['HR', 'Haryana', 'Gurugram', 'A', 'residential', 5.50, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['HR', 'Haryana', 'Gurugram', 'B', 'residential', 4.50, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['HR', 'Haryana', 'Gurugram', 'C', 'residential', 3.50, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['HR', 'Haryana', 'Gurugram', 'D', 'residential', 2.50, 1000, null, 5.0, 2.0, '2024-04-01', null],
    ['HR', 'Haryana', 'Gurugram', 'A', 'commercial', 18.00, 2000, null, 3.0, 2.0, '2024-04-01', null],
    
    // Rajasthan (Jaipur)
    ['RJ', 'Rajasthan', 'Jaipur', 'A', 'residential', 3.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['RJ', 'Rajasthan', 'Jaipur', 'B', 'residential', 2.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['RJ', 'Rajasthan', 'Jaipur', 'C', 'residential', 2.00, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['RJ', 'Rajasthan', 'Jaipur', 'D', 'residential', 1.50, 500, null, 5.0, 2.0, '2024-04-01', null],
    ['RJ', 'Rajasthan', 'Jaipur', 'A', 'commercial', 12.00, 1500, null, 3.0, 2.0, '2024-04-01', null],
];

$stmt = $pdo->prepare("
    INSERT INTO property_tax_rates 
    (state_code, state_name, city, zone, property_type, tax_rate_per_sqft, min_tax_amount, max_tax_amount, rebate_percent, penalty_percent_per_month, effective_from, effective_to)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        state_name = VALUES(state_name),
        tax_rate_per_sqft = VALUES(tax_rate_per_sqft),
        min_tax_amount = VALUES(min_tax_amount),
        max_tax_amount = VALUES(max_tax_amount),
        rebate_percent = VALUES(rebate_percent),
        penalty_percent_per_month = VALUES(penalty_percent_per_month),
        effective_to = VALUES(effective_to),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($rates as $rate) {
    try {
        $stmt->execute($rate);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted property tax rates\n";

// Also add for more cities in UP
$upCities = ['Varanasi', 'Kanpur', 'Agra', 'Meerut', 'Prayagraj', 'Bareilly', 'Moradabad', 'Aligarh', 'Saharanpur', 'Ghaziabad', 'Noida', 'Greater Noida'];
$upRates = [
    'residential' => ['A' => 2.50, 'B' => 2.00, 'C' => 1.50, 'D' => 1.00],
    'commercial' => ['A' => 8.00, 'B' => 6.50, 'C' => 5.00, 'D' => 4.00],
    'industrial' => ['A' => 4.00, 'B' => 3.50, 'C' => 3.00, 'D' => 2.50],
    'vacant_land' => ['' => 0.50],
    'institutional' => ['' => 3.00],
];

foreach ($upCities as $city) {
    foreach ($upRates as $propType => $zones) {
        foreach ($zones as $zone => $rate) {
            try {
                $stmt->execute([
                    'UP', 'Uttar Pradesh', $city, $zone, $propType, $rate, 500, null, 5.0, 2.0, '2024-04-01', null
                ]);
                $inserted++;
            } catch (Exception $e) {
                // Ignore
            }
        }
    }
}

echo "Total rates inserted/updated: $inserted\n";

echo "\n=== Property Tax tables and rates created successfully ===\n";