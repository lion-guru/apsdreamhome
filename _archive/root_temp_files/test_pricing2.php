<?php
require_once 'C:/xampp/htdocs/apsdreamhome/app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();

// Get land cost for colony 6
$stmt = $pdo->prepare('SELECT SUM(acquisition_cost) as total FROM land_acquisitions WHERE colony_id = ?');
$stmt->execute([6]);
$land = $stmt->fetch();
echo 'Land cost: ' . $land['total'] . PHP_EOL;

// Get dev costs
$stmt = $pdo->prepare('SELECT SUM(amount) as total FROM colony_development_costs WHERE colony_id = ?');
$stmt->execute([6]);
$dev = $stmt->fetch();
echo 'Dev cost: ' . $dev['total'] . PHP_EOL;

// Get saleable area
$stmt = $pdo->prepare('SELECT SUM(area_sqft) as total FROM plots WHERE colony_id = ?');
$stmt->execute([6]);
$area = $stmt->fetch();
echo 'Saleable area: ' . $area['total'] . PHP_EOL;

$totalCost = $land['total'] + $dev['total'];
$rawCost = $totalCost / $area['total'];
echo 'Total cost: ' . $totalCost . PHP_EOL;
echo 'Raw cost/sqft: ' . $rawCost . PHP_EOL;

// Markup: 25% MLM + 5% G&A + 20% Profit = 50%
$markup = 1 / (1 - 0.50);
$basePrice = $rawCost * $markup;
echo 'Base price/sqft: ' . $basePrice . PHP_EOL;