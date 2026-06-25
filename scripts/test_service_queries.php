<?php
require_once __DIR__ . '/../app\Core\ConfigService.php';
require_once __DIR__ . '/../app\Core\Database\Database.php';
$db = \App\Core\Database\Database::getInstance();

echo "Testing service queries for colony 7...\n";

$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 7 AND status = "registered"');
echo 'Land cost (PricingService) for colony 7: ' . $row['total'] . "\n";

$row = $db->fetch('SELECT COALESCE(SUM(acquisition_cost), 0) AS total FROM land_acquisitions WHERE colony_id = 7 AND status IN ("registered", "active", "sold", "under_development")');
echo 'Land cost (FeasibilityService) for colony 7: ' . $row['total'] . "\n";

echo "\nAll service queries work!\n";