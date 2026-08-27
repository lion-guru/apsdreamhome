<?php
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = App\Core\Database\Database::getInstance()->getConnection();
$before = $pdo->query("SELECT COUNT(*) FROM plots")->fetchColumn();
$inserted = $pdo->exec("
INSERT INTO plots (tenant_id, colony_id, plot_number, block, area_sqft, area_sqm, width_ft, length_ft, price_per_sqft, total_price, status, is_active, created_at, updated_at)
SELECT 1, i.colony_id, i.plot_no, i.block_name, i.size_sqft, ROUND(i.size_sqft*0.092903,2), i.width_ft, i.length_ft, i.basic_price, ROUND(i.size_sqft*i.basic_price,2), 'available', 1, NOW(), NOW()
FROM inventory_plots i
LEFT JOIN plots p ON p.colony_id = i.colony_id AND p.plot_number = i.plot_no
WHERE p.id IS NULL AND i.colony_id IS NOT NULL
");
$after = $pdo->query("SELECT COUNT(*) FROM plots")->fetchColumn();
echo "Inserted $inserted rows: $before -> $after plots\n";
$c2 = $pdo->query("SELECT COUNT(*) FROM plots WHERE colony_id=2")->fetchColumn();
echo "Colony 2 plots now: $c2\n";
