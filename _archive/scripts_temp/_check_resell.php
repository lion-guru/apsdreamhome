<?php
define('APS_ROOT', dirname(__DIR__));
define('APS_PUBLIC', APS_ROOT . '/public');
require_once APS_ROOT . '/config/bootstrap.php';

$db = App\Core\Database\Database::getInstance();

$tables = $db->fetchAll("SHOW TABLES LIKE '%resell%'");
echo "Resell tables: " . json_encode($tables) . "\n";

// Check user_properties table
$count = $db->fetchOne("SELECT COUNT(*) as c FROM user_properties");
echo "user_properties count: " . ($count['c'] ?? 0) . "\n";

$cols = $db->fetchAll("DESCRIBE user_properties");
echo "user_properties columns:\n";
foreach ($cols as $col) echo "  " . $col['Field'] . " (" . $col['Type'] . ")\n";?>