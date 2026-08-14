<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Stamp duty rates by state (as of 2024 - indicative rates)
$states = [
    'Uttar Pradesh' => [
        'state_code' => 'UP',
        'male_rate' => 7.0,
        'female_rate' => 6.0,
        'joint_rate' => 6.5,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Delhi' => [
        'state_code' => 'DL',
        'male_rate' => 6.0,
        'female_rate' => 4.0,
        'joint_rate' => 5.0,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Maharashtra' => [
        'state_code' => 'MH',
        'male_rate' => 6.0,
        'female_rate' => 5.0,
        'joint_rate' => 5.5,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 1.0, // Metro cess
        'cess' => 0,
    ],
    'Karnataka' => [
        'state_code' => 'KA',
        'male_rate' => 5.6,
        'female_rate' => 5.6,
        'joint_rate' => 5.6,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Haryana' => [
        'state_code' => 'HR',
        'male_rate' => 7.0,
        'female_rate' => 5.0,
        'joint_rate' => 6.0,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Rajasthan' => [
        'state_code' => 'RJ',
        'male_rate' => 6.0,
        'female_rate' => 5.0,
        'joint_rate' => 5.5,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Madhya Pradesh' => [
        'state_code' => 'MP',
        'male_rate' => 7.5,
        'female_rate' => 6.5,
        'joint_rate' => 7.0,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Bihar' => [
        'state_code' => 'BR',
        'male_rate' => 6.0,
        'female_rate' => 5.0,
        'joint_rate' => 5.5,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Punjab' => [
        'state_code' => 'PB',
        'male_rate' => 7.0,
        'female_rate' => 5.0,
        'joint_rate' => 6.0,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
    'Gujarat' => [
        'state_code' => 'GJ',
        'male_rate' => 4.9,
        'female_rate' => 4.9,
        'joint_rate' => 4.9,
        'registration_rate' => 1.0,
        'circle_rate_multiplier' => 1.0,
        'surcharge' => 0,
        'cess' => 0,
    ],
];

// Create stamp_duty_rates table
$pdo->exec("
CREATE TABLE IF NOT EXISTS stamp_duty_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_name VARCHAR(100) NOT NULL,
    state_code VARCHAR(10) NOT NULL,
    male_rate DECIMAL(5,2) NOT NULL COMMENT 'Stamp duty % for male buyer',
    female_rate DECIMAL(5,2) NOT NULL COMMENT 'Stamp duty % for female buyer',
    joint_rate DECIMAL(5,2) NOT NULL COMMENT 'Stamp duty % for joint ownership',
    registration_rate DECIMAL(5,2) NOT NULL DEFAULT 1.0 COMMENT 'Registration fee %',
    circle_rate_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.0,
    surcharge DECIMAL(5,2) DEFAULT 0 COMMENT 'Additional surcharge %',
    cess DECIMAL(5,2) DEFAULT 0 COMMENT 'Cess %',
    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_state_code (state_code),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "stamp_duty_rates table created/verified\n";

// Insert/update rates
$stmt = $pdo->prepare("
    INSERT INTO stamp_duty_rates 
    (state_name, state_code, male_rate, female_rate, joint_rate, registration_rate, circle_rate_multiplier, surcharge, cess, effective_from, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 1)
    ON DUPLICATE KEY UPDATE
        state_name = VALUES(state_name),
        male_rate = VALUES(male_rate),
        female_rate = VALUES(female_rate),
        joint_rate = VALUES(joint_rate),
        registration_rate = VALUES(registration_rate),
        circle_rate_multiplier = VALUES(circle_rate_multiplier),
        surcharge = VALUES(surcharge),
        cess = VALUES(cess),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($states as $stateName => $data) {
    $stmt->execute([
        $stateName,
        $data['state_code'],
        $data['male_rate'],
        $data['female_rate'],
        $data['joint_rate'],
        $data['registration_rate'],
        $data['circle_rate_multiplier'],
        $data['surcharge'],
        $data['cess'],
    ]);
    $inserted++;
}

echo "Inserted/Updated $inserted state stamp duty rates\n";

// Create circle_rates table for area-wise rates
$pdo->exec("
CREATE TABLE IF NOT EXISTS circle_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_code VARCHAR(10) NOT NULL,
    district VARCHAR(100) NOT NULL,
    tehsil VARCHAR(100) DEFAULT NULL,
    area_name VARCHAR(200) NOT NULL,
    area_type ENUM('residential', 'commercial', 'agricultural', 'industrial') DEFAULT 'residential',
    rate_per_sqft DECIMAL(12,2) NOT NULL,
    rate_per_sqm DECIMAL(12,2) DEFAULT NULL,
    rate_per_acre DECIMAL(15,2) DEFAULT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    source VARCHAR(100) DEFAULT 'govt_notification',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_state_district (state_code, district),
    KEY idx_active (is_active),
    KEY idx_area (area_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "circle_rates table created/verified\n";

// Sample circle rates for Gorakhpur (UP) - indicative
$circleRates = [
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Civil Lines', 'residential', 12000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Civil Lines', 'commercial', 25000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Gorakhnath', 'residential', 8000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Gorakhnath', 'commercial', 18000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Rustampur', 'residential', 7000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Rustampur', 'commercial', 15000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Medical College Road', 'residential', 9000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Medical College Road', 'commercial', 20000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'University Road', 'residential', 10000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'University Road', 'commercial', 22000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Pipraich Road', 'residential', 6000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Pipraich Road', 'commercial', 12000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Deoria Road', 'residential', 6500, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Deoria Road', 'commercial', 14000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Kushmi', 'residential', 5000, null, null],
    ['UP', 'Gorakhpur', 'Gorakhpur', 'Kushmi', 'commercial', 10000, null, null],
    
    // Lucknow
    ['UP', 'Lucknow', 'Lucknow', 'Hazratganj', 'residential', 25000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Hazratganj', 'commercial', 50000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Gomti Nagar', 'residential', 15000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Gomti Nagar', 'commercial', 35000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Aliganj', 'residential', 12000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Aliganj', 'commercial', 28000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Indira Nagar', 'residential', 13000, null, null],
    ['UP', 'Lucknow', 'Lucknow', 'Indira Nagar', 'commercial', 30000, null, null],
    
    // Delhi
    ['DL', 'New Delhi', 'Connaught Place', 'Connaught Place', 'residential', 45000, null, null],
    ['DL', 'New Delhi', 'Connaught Place', 'Connaught Place', 'commercial', 100000, null, null],
    ['DL', 'South Delhi', 'Saket', 'Saket', 'residential', 35000, null, null],
    ['DL', 'South Delhi', 'Saket', 'Saket', 'commercial', 80000, null, null],
    ['DL', 'West Delhi', 'Rajouri Garden', 'Rajouri Garden', 'residential', 25000, null, null],
    ['DL', 'West Delhi', 'Rajouri Garden', 'Rajouri Garden', 'commercial', 55000, null, null],
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
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted circle rates\n";

// Create stamp_duty_calculations table for history
$pdo->exec("
CREATE TABLE IF NOT EXISTS stamp_duty_calculations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    booking_id INT UNSIGNED DEFAULT NULL,
    state_code VARCHAR(10) NOT NULL,
    district VARCHAR(100) DEFAULT NULL,
    area_name VARCHAR(200) DEFAULT NULL,
    area_type ENUM('residential', 'commercial', 'agricultural', 'industrial') DEFAULT 'residential',
    property_value DECIMAL(15,2) NOT NULL COMMENT 'Agreement value',
    circle_rate_value DECIMAL(15,2) DEFAULT NULL COMMENT 'Value based on circle rate',
    higher_value DECIMAL(15,2) NOT NULL COMMENT 'Max of property_value and circle_rate_value',
    buyer_gender ENUM('male', 'female', 'joint') DEFAULT 'male',
    stamp_duty_rate DECIMAL(5,2) NOT NULL,
    stamp_duty_amount DECIMAL(15,2) NOT NULL,
    registration_fee_rate DECIMAL(5,2) NOT NULL,
    registration_fee_amount DECIMAL(15,2) NOT NULL,
    surcharge_amount DECIMAL(15,2) DEFAULT 0,
    cess_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    calculation_data JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_booking (booking_id),
    KEY idx_state (state_code),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "stamp_duty_calculations table created/verified\n";

echo "\n=== All Stamp Duty tables and data created successfully ===\n";?>