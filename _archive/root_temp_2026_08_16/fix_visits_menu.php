<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Disable the duplicate "Visits Log" menu item (ID 88) in marketing section
$db->exec("UPDATE admin_menu_items SET is_active = 0 WHERE id = 88");
echo "Disabled duplicate 'Visits Log' menu item (ID 88)\n";

// Check if we need to add a proper "Site Visits" menu item pointing to /admin/site-visits
$exists = $db->query("SELECT id FROM admin_menu_items WHERE url = '/admin/site-visits'")->fetch();
if (!$exists) {
    // Find the max order_index in properties section
    $maxOrder = $db->query("SELECT MAX(order_index) FROM admin_menu_items WHERE section = 'properties'")->fetchColumn();
    $newOrder = ($maxOrder ?: 0) + 1;
    
    $db->exec("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) 
        VALUES (1, 'Site Visits', 'fas fa-calendar-check', '/admin/site-visits', NULL, 'properties', $newOrder, 'visits.view', 1, NOW(), NOW())");
    echo "Added 'Site Visits' menu item for /admin/site-visits\n";
} else {
    echo "Site Visits menu item already exists\n";
}

// Also fix the duplicate "Site Visits" menu item (ID 11) - it points to /admin/visits but should be /admin/site-visits
$db->exec("UPDATE admin_menu_items SET url = '/admin/site-visits', name = 'Site Visits' WHERE id = 11");
echo "Updated menu item 11 to point to /admin/site-visits\n";

echo "Done!\n";