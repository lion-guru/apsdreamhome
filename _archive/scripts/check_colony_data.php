<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

$rows = $db->fetchAll('SELECT colony_id, COUNT(*) as cnt, SUM(acquisition_cost) as total FROM land_acquisitions GROUP BY colony_id');
print_r($rows);?>