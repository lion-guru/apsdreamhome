<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

echo "Testing land_acquisitions view...\n";
$row = $db->fetch('SELECT * FROM land_acquisitions LIMIT 1');
print_r($row);

echo "\nTesting AdminController query...\n";
$stats = $db->fetch('SELECT COUNT(*) AS cnt FROM land_acquisitions');
echo 'Count: ' . $stats['cnt'] . "\n";

echo "\nTesting ColonyPricingService query...\n";
$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 2 AND status = "registered"');
print_r($row);

echo "\nTesting ColonyFeasibilityService query...\n";
$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 2 AND status IN ("registered", "active", "sold", "under_development")');
print_r($row);

echo "\nAll tests passed!\n";