<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create circle_rates table
$pdo->exec("
CREATE TABLE IF NOT EXISTS circle_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_code VARCHAR(10) NOT NULL,
    district VARCHAR(100) NOT NULL,
    tehsil VARCHAR(100) DEFAULT NULL,
    area_name VARCHAR(200) NOT NULL,
    area_type ENUM('residential', 'commercial', 'agricultural', 'industrial') NOT NULL DEFAULT 'residential',
    rate_per_sqft DECIMAL(12,2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_circle_rate (state_code, district, tehsil, area_name, area_type, effective_from),
    KEY idx_state_district (state_code, district),
    KEY idx_area_type (area_type),
    KEY idx_effective (effective_from, effective_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "circle_rates table created/verified\n";

// Circle rates for UP (Gorakhpur, Lucknow), Delhi
$circleRates = [
    // Gorakhpur
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Civil Lines', 'residential', 8500],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Civil Lines', 'commercial', 18000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Medical College Road', 'residential', 7500],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Medical College Road', 'commercial', 16000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'University Road', 'residential', 7000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'University Road', 'commercial', 15000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Bank Road', 'residential', 8000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Bank Road', 'commercial', 17000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Gorakhpur GIDA', 'residential', 5500],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Gorakhpur GIDA', 'commercial', 12000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Pipraich Road', 'residential', 5000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Pipraich Road', 'commercial', 12000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Deoria Road', 'residential', 6500],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Deoria Road', 'commercial', 14000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Kushmi', 'residential', 5000],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Kushmi', 'commercial', 10000],
    
    // Lucknow
    ['UP', 'Lucknow', 'Lucknow', 'Hazratganj', 'residential', 25000],
    ['UP', 'Lucknow', 'Lucknow', 'Hazratganj', 'commercial', 50000],
    ['UP', 'Lucknow', 'Lucknow', 'Gomti Nagar', 'residential', 15000],
    ['UP', 'Lucknow', 'Lucknow', 'Gomti Nagar', 'commercial', 35000],
    ['UP', 'Lucknow', 'Lucknow', 'Aliganj', 'residential', 12000],
    ['UP', 'Lucknow', 'Lucknow', 'Aliganj', 'commercial', 28000],
    ['UP', 'Lucknow', 'Lucknow', 'Indira Nagar', 'residential', 13000],
    ['UP', 'Lucknow', 'Lucknow', 'Indira Nagar', 'commercial', 30000],
    ['UP', 'Lucknow', 'Lucknow', 'Vikas Nagar', 'residential', 10000],
    ['UP', 'Lucknow', 'Lucknow', 'Vikas Nagar', 'commercial', 22000],
    ['UP', 'Lucknow', 'Lucknow', 'Rajajipuram', 'residential', 9000],
    ['UP', 'Lucknow', 'Lucknow', 'Rajajipuram', 'commercial', 20000],
    ['UP', 'Lucknow', 'Lucknow', 'Alambagh', 'residential', 9500],
    ['UP', 'Lucknow', 'Lucknow', 'Alambagh', 'commercial', 21000],
    ['UP', 'Lucknow', 'Lucknow', 'Mahanagar', 'residential', 11000],
    ['UP', 'Lucknow', 'Lucknow', 'Mahanagar', 'commercial', 25000],
    ['UP', 'Lucknow', 'Lucknow', 'Nirala Nagar', 'residential', 10500],
    ['UP', 'Lucknow', 'Lucknow', 'Nirala Nagar', 'commercial', 23000],
    ['UP', 'Lucknow', 'Lucknow', 'Aashiana', 'residential', 8500],
    ['UP', 'Lucknow', 'Lucknow', 'Aashiana', 'commercial', 18000],
    ['UP', 'Lucknow', 'Lucknow', 'Transport Nagar', 'residential', 8000],
    ['UP', 'Lucknow', 'Lucknow', 'Transport Nagar', 'commercial', 17000],
    ['UP', 'Lucknow', 'Lucknow', 'Sarojini Nagar', 'residential', 9000],
    ['UP', 'Lucknow', 'Lucknow', 'Sarojini Nagar', 'commercial', 19000],
    ['UP', 'Lucknow', 'Lucknow', 'Amausi', 'residential', 7500],
    ['UP', 'Lucknow', 'Lucknow', 'Amausi', 'commercial', 16000],
    ['UP', 'Lucknow', 'Lucknow', 'Kakori', 'residential', 6000],
    ['UP', 'Lucknow', 'Lucknow', 'Kakori', 'commercial', 13000],
    ['UP', 'Lucknow', 'Lucknow', 'Bakshi Ka Talab', 'residential', 5500],
    ['UP', 'Lucknow', 'Lucknow', 'Bakshi Ka Talab', 'commercial', 12000],
    ['UP', 'Lucknow', 'Lucknow', 'Mohanlalganj', 'residential', 5000],
    ['UP', 'Lucknow', 'Lucknow', 'Mohanlalganj', 'commercial', 11000],
    
    // Delhi
    ['DL', 'New Delhi', 'Connaught Place', 'Connaught Place', 'residential', 45000],
    ['DL', 'New Delhi', 'Connaught Place', 'Connaught Place', 'commercial', 100000],
    ['DL', 'South Delhi', 'Saket', 'Saket', 'residential', 35000],
    ['DL', 'South Delhi', 'Saket', 'Saket', 'commercial', 80000],
    ['DL', 'West Delhi', 'Rajouri Garden', 'Rajouri Garden', 'residential', 25000],
    ['DL', 'West Delhi', 'Rajouri Garden', 'Rajouri Garden', 'commercial', 55000],
    ['DL', 'North Delhi', 'Model Town', 'Model Town', 'residential', 28000],
    ['DL', 'North Delhi', 'Model Town', 'Model Town', 'commercial', 60000],
    ['DL', 'East Delhi', 'Laxmi Nagar', 'Laxmi Nagar', 'residential', 22000],
    ['DL', 'East Delhi', 'Laxmi Nagar', 'Laxmi Nagar', 'commercial', 45000],
    ['DL', 'South West Delhi', 'Dwarka', 'Dwarka', 'residential', 18000],
    ['DL', 'South West Delhi', 'Dwarka', 'Dwarka', 'commercial', 38000],
    ['DL', 'North West Delhi', 'Rohini', 'Rohini', 'residential', 16000],
    ['DL', 'North West Delhi', 'Rohini', 'Rohini', 'commercial', 35000],
];

$stmt = $pdo->prepare("
    INSERT INTO circle_rates 
    (state_code, district, tehsil, area_name, area_type, rate_per_sqft, effective_from, is_active)
    VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 1)
    ON DUPLICATE KEY UPDATE
        rate_per_sqft = VALUES(rate_per_sqft),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($circleRates as $rate) {
    try {
        $stmt->execute($rate);
        $inserted++;
    } catch (Exception $e) {
        echo "Error inserting {$rate[3]}: " . $e->getMessage() . "\n";
    }
}

echo "Inserted/Updated $inserted circle rates\n";

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM circle_rates WHERE is_active = 1")->fetchColumn();
echo "Total active circle rates: $count\n";?>