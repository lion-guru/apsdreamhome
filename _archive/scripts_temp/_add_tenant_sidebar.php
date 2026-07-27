<?php
require_once __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

$maxId = $db->query("SELECT MAX(id) FROM admin_menu_items")->fetchColumn();
echo "Max sidebar ID: " . $maxId . "\n";

$check = $db->query("SELECT COUNT(*) FROM admin_menu_items WHERE url = '/admin/tenants'")->fetchColumn();
echo "Existing tenant items: " . $check . "\n";

$items = [
    ['Tenant Dashboard', 'fas fa-cloud', '/admin/tenants/dashboard', 'saas', 264, 'super_admin', 1],
    ['Tenant Management', 'fas fa-building', '/admin/tenants', 'saas', 265, 'super_admin', 1],
];

$stmt = $db->prepare("INSERT INTO admin_menu_items (name, icon, url, section, order_index, permission_key, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($items as $item) {
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM admin_menu_items WHERE url = ?");
    $checkStmt->execute([$item[2]]);
    $existing = $checkStmt->fetchColumn();
    if ($existing == 0) {
        $stmt->execute($item);
        echo "Added: {$item[0]} -> {$item[2]}\n";
    } else {
        echo "Already exists: {$item[0]} -> {$item[2]}\n";
    }
}

echo "\nDone!\n";
