<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id, label, url, parent_id, icon, sort_order, is_active FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as $item) {
    $parent = $item['parent_id'] ? " (parent: {$item['parent_id']})" : '';
    echo "ID: {$item['id']} | {$item['label']} | URL: {$item['url']} | Icon: {$item['icon']} | Order: {$item['sort_order']}{$parent}\n";
}?>