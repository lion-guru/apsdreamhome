<?php
/**
 * Fix land_acquisitions view to have compatible column names
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "Dropping and recreating land_acquisitions view...\n";

$db->execute("DROP VIEW land_acquisitions");

$sql = "CREATE VIEW land_acquisitions AS 
SELECT 
    id,
    land_lead_id,
    colony_id,
    total_area_sqft,
    acquired_area_sqft,
    total_consideration AS acquisition_cost,
    total_consideration,
    advance_paid,
    balance_amount,
    sale_agreement_date,
    sale_agreement_number,
    registration_date,
    registration_number,
    sub_registrar_office,
    stamp_duty_amount,
    registration_fee,
    mutation_status,
    mutation_number,
    mutation_date,
    status,
    created_at,
    updated_at
FROM land_deals";

$db->execute($sql);

echo "View recreated with acquisition_cost alias.\n";

// Test
$row = $db->fetch('SELECT * FROM land_acquisitions LIMIT 1');
print_r($row);

echo "\nTesting ColonyPricingService query...\n";
$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 2 AND status = "registered"');
print_r($row);

echo "\nTesting ColonyFeasibilityService query...\n";
$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 2 AND status IN ("registered", "active", "sold", "under_development")');
print_r($row);

echo "\nAll tests passed!\n";