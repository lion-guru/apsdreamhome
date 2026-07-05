<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Check if Ad Manager menu item exists
$r = $db->query("SELECT id, name, section, order_index FROM admin_menu_items WHERE url LIKE '%ads%' ORDER BY id");
echo "Ad menu items:\n";
$hasAds = false;
foreach ($r as $row) {
    $hasAds = true;
    echo "  {$row['id']} {$row['name']} ({$row['section']}) order={$row['order_index']}\n";
}
if (!$hasAds) {
    // Add Ad Manager under Marketing section
    $max = $db->query("SELECT MAX(order_index) FROM admin_menu_items WHERE section = 'marketing'")->fetchColumn();
    $order = (int)$max + 1;
    $insert = $db->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, permission_key, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $insert->execute(['Ad Manager', '/admin/ads', 'fas fa-ad', 'marketing', $order, 'marketing.ads']);
    $insert->execute(['AdSense Settings', '/admin/ads/settings', 'fab fa-google', 'marketing', $order + 1, 'marketing.ads']);
    echo "Added Ad Manager + AdSense Settings menu items.\n";
} else {
    echo "Ad menu items already exist.\n";
}
