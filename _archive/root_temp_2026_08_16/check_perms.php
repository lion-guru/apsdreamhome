<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check admin_role_menu_permissions for CEO/CFO dashboards
$rows = $db->query("SELECT * FROM admin_role_menu_permissions WHERE role IN ('ceo', 'cfo', 'finance_director', 'sales_director', 'builder_director')")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);

// Also check menu items for CEO/CFO dashboards
$rows2 = $db->query("SELECT * FROM admin_menu_items WHERE url LIKE '%ceo-dashboard%' OR url LIKE '%cfo-dashboard%' OR url LIKE '%finance-dashboard%' OR url LIKE '%sales-dashboard%' OR url LIKE '%builder-dashboard%' OR url LIKE '%ceo-dashboard%' OR url LIKE '%cfo-dashboard%'")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows2);