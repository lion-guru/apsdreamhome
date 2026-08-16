<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check if the VisitController's required tables exist
$tables = ['site_visits', 'property_visits', 'leads', 'user_properties', 'users'];
foreach ($tables as $table) {
    $r = $db->query("SHOW TABLES LIKE '$table'")->fetch();
    echo "$table: " . ($r ? 'EXISTS' : 'MISSING') . PHP_EOL;
}

// Check VisitService
$rows = $db->query("SELECT * FROM admin_menu_items WHERE url = '/admin/visits' AND is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);