<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Menu items containing 'bookings' or 'mlm' ===\n";
$stmt = $pdo->query("SELECT id, name, url, section FROM admin_menu_items WHERE url LIKE '%bookings%' OR url LIKE '%mlm%' OR url LIKE '%settings%' OR url LIKE '%godmode%'");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  id={$r['id']} url={$r['url']} name={$r['name']} section={$r['section']}\n";
}

echo "\n=== RBAC permissions for telecaller role ===\n";
$stmt = $pdo->query("SELECT rpm.menu_item_id, ami.url, ami.name 
    FROM admin_role_menu_permissions rpm 
    JOIN admin_menu_items ami ON ami.id = rpm.menu_item_id 
    WHERE rpm.role = 'telecaller' 
    ORDER BY ami.url");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total permissions: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  {$r['url']} ({$r['name']})\n";
}

echo "\n=== RBAC for employee role ===\n";
$stmt = $pdo->query("SELECT rpm.menu_item_id, ami.url, ami.name 
    FROM admin_role_menu_permissions rpm 
    JOIN admin_menu_items ami ON ami.id = rpm.menu_item_id 
    WHERE rpm.role = 'employee' 
    ORDER BY ami.url");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total permissions: " . count($rows) . "\n";

echo "\n=== Check if 'bookings' URL is in admin_menu_items ===\n";
$stmt = $pdo->query("SELECT id, url, name FROM admin_menu_items WHERE url = '/admin/bookings'");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo $r ? "  FOUND: id={$r['id']} url={$r['url']}" : "  NOT FOUND";
echo "\n";

echo "\n=== Check 'mlm' URL ===\n";
$stmt = $pdo->query("SELECT id, url, name FROM admin_menu_items WHERE url = '/admin/mlm'");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo $r ? "  FOUND: id={$r['id']} url={$r['url']}" : "  NOT FOUND";
echo "\n";?>