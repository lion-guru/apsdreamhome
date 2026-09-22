<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$sample = $pdo->query("SELECT id, name, section, url, parent_id FROM admin_menu_items LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "Sample admin_menu_items:\n";
print_r($sample);

$parentCounts = $pdo->query("SELECT parent_id, count(*) as cnt FROM admin_menu_items GROUP BY parent_id")->fetchAll(PDO::FETCH_ASSOC);
echo "parent_id distribution:\n";
print_r($parentCounts);
