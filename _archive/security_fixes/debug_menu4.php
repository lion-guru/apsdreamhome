<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id, label, url, parent_id, icon, sort_order FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = "Total rows: " . count($results) . "\n";
foreach ($results as $r) {
    $output .= "ID: " . $r['id'] . " | " . $r['label'] . " | URL: " . $r['url'] . " | Icon: " . $r['icon'] . " | Order: " . $r['sort_order'] . " | Parent: " . $r['parent_id'] . "\n";
}
$output .= "Done\n";

file_put_contents('C:/xampp/htdocs/apsdreamhome/menu_output.txt', $output);
echo "Written to file\n";?>