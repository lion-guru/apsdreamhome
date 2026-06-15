<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'], $config['username'], $config['password']);
$rows = $pdo->query("SELECT id, name, url, section, order_index FROM admin_menu_items WHERE url LIKE '%dashboard%' ORDER BY section, order_index")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo $r['id'] . '|' . $r['name'] . '|' . $r['url'] . '|' . $r['section'] . '|' . $r['order_index'] . PHP_EOL;

echo "\n--- RBAC perms for employee dashboards ---\n";
$perms = $pdo->query("SELECT role, menu_item_id FROM admin_role_menu_permissions WHERE role LIKE 'employee_%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($perms as $p) echo $p['role'] . ' → menu_item_id=' . $p['menu_item_id'] . PHP_EOL;
echo "Total perms: " . count($perms) . PHP_EOL;
