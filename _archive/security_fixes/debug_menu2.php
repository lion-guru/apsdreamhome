<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id, label, url, parent_id, icon, sort_order FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total rows: " . count($results) . "\n";
foreach ($results as $r) {
    echo "ID: " . $r['id'] . " | " . $r['label'] . " | URL: " . $r['url'] . " | Icon: " . $r['icon'] . " | Order: " . $r['sort_order'] . " | Parent: " . $r['parent_id'] . "\n";
}
echo "Done\n";