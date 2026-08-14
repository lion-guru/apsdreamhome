<?php
define('APP_ROOT', __DIR__ . '/../');
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();
use App\Core\Database\Database;
$db = Database::getInstance();
echo "=== technology section menu items ===\n";
$rows = $db->fetchAll("SELECT id, name, icon, url, section, order_index FROM admin_menu_items WHERE section='technology' ORDER BY order_index");
foreach ($rows as $r) { echo $r['id']." | ".$r['name']." | ".$r['icon']." | ".$r['url']." | ".$r['order_index']."\n"; }
echo "=== calls_log columns ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM calls_log");
foreach ($cols as $c) { echo $c['Field']." ".$c['Type']."\n"; }
echo "=== calling-schedule/calls_log counts ===\n";
echo "schedule: ".(int)$db->fetch("SELECT COUNT(*) c FROM ai_calling_schedule")['c']."\n";
echo "calls_log: ".(int)$db->fetch("SELECT COUNT(*) c FROM calls_log")['c']."\n";?>