<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Insert stamp duty config
$rates = [
    ['UP', 'Uttar Pradesh', 7.0, 6.0, 6.5, 1.0, 0, 0],
    ['DL', 'Delhi', 6.0, 4.0, 5.0, 1.0, 0, 0],
    ['MH', 'Maharashtra', 6.0, 5.0, 5.5, 1.0, 1.0, 0],
    ['KA', 'Karnataka', 5.6, 5.6, 5.6, 1.0, 0, 0],
    ['HR', 'Haryana', 7.0, 5.0, 6.0, 1.0, 0, 0],
    ['RJ', 'Rajasthan', 6.0, 5.0, 5.5, 1.0, 0, 0],
    ['GJ', 'Gujarat', 4.9, 4.9, 4.9, 1.0, 0, 0],
    ['TN', 'Tamil Nadu', 7.0, 7.0, 7.0, 1.0, 0, 0],
    ['WB', 'West Bengal', 6.0, 6.0, 6.0, 1.0, 1.0, 0],
    ['PB', 'Punjab', 6.0, 5.0, 5.5, 1.0, 0, 0],
];

$stmt = $pdo->prepare("
    INSERT INTO stamp_duty_config 
    (state_code, state_name, male_rate, female_rate, joint_rate, registration_rate, surcharge, cess, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
        state_name = VALUES(state_name),
        male_rate = VALUES(male_rate),
        female_rate = VALUES(female_rate),
        joint_rate = VALUES(joint_rate),
        registration_rate = VALUES(registration_rate),
        surcharge = VALUES(surcharge),
        cess = VALUES(cess),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($rates as $rate) {
    try {
        $stmt->execute($rate);
        $inserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted/Updated $inserted stamp duty rates\n";

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM stamp_duty_config WHERE is_active = 1")->fetchColumn();
echo "Total active states: $count\n";