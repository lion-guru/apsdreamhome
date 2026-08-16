<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// URLs to exclude (C-level dashboards)
$excludedUrls = [
    '/admin/ceo-dashboard',
    '/admin/cfo-dashboard',
    '/admin/finance-dashboard',
    '/admin/sales-dashboard',
    '/admin/builder-dashboard',
    '/admin/financial-reports',
];

// Get all menu items
$stmt = $db->prepare("SELECT id, url, name FROM admin_menu_items WHERE is_active = 1");
$stmt->execute();
$menuItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$inserted = 0;
$skipped = 0;

foreach ($menuItems as $item) {
    $url = $item['url'];
    
    // Skip C-level dashboards
    $skip = false;
    foreach ($excludedUrls as $excluded) {
        if ($url === $excluded || strpos($url, $excluded . '/') === 0) {
            $skip = true;
            break;
        }
    }
    
    if ($skip) {
        echo "SKIPPED: {$item['name']} ({$url})\n";
        $skipped++;
        continue;
    }
    
    // Check if permission already exists
    $check = $db->prepare("SELECT id FROM admin_role_menu_permissions WHERE role = 'admin' AND menu_item_id = ?");
    $check->execute([$item['id']]);
    $existing = $check->fetch();
    
    if ($existing) {
        // Update existing
        $db->exec("UPDATE admin_role_menu_permissions SET can_view=1, can_create=1, can_edit=1, can_delete=1 WHERE role='admin' AND menu_item_id={$item['id']}");
        echo "UPDATED: {$item['name']} ({$url})\n";
    } else {
        // Insert new
        $insert = $db->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'admin', ?, 1, 1, 1, 1)");
        $insert->execute([$item['id']]);
        $inserted++;
        echo "INSERTED: {$item['name']} ({$url})\n";
    }
}

echo "\nDone! Inserted: $inserted, Skipped: $skipped\n";