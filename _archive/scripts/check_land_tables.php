<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== Checking current tables ===\n";
$tables = ['land_acquisitions', 'land_deals', 'land_leads', 'land_deal_payments', 'colony_development_costs', 'colony_layouts', 'colonies', 'plots', 'land_brokers'];
foreach ($tables as $t) {
    $row = $db->fetch("SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$t]);
    echo $t . ': ' . ($row['cnt'] ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== Checking FKs on land_deals ===\n";
$fks = $db->fetchAll("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'land_deals' AND REFERENCED_TABLE_NAME IS NOT NULL");
print_r($fks);

echo "\n=== Checking FKs on land_leads ===\n";
$fks = $db->fetchAll("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'land_leads' AND REFERENCED_TABLE_NAME IS NOT NULL");
print_r($fks);

echo "\n=== Checking data in land_acquisitions vs land_deals ===\n";
$count1 = $db->fetch("SELECT COUNT(*) as c FROM land_acquisitions");
$count2 = $db->fetch("SELECT COUNT(*) as c FROM land_deals");
echo "land_acquisitions: " . $count1['c'] . " rows\n";
echo "land_deals: " . $count2['c'] . " rows\n";

echo "\n=== Sample land_acquisitions columns ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM land_acquisitions");
foreach ($cols as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";

echo "\n=== Sample land_deals columns ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM land_deals");
foreach ($cols as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";?>