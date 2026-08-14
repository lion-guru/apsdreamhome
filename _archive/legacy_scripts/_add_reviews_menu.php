<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$count = (int)$db->query("SELECT COUNT(*) FROM admin_menu_items WHERE url = '/admin/reviews'")->fetchColumn();
if ($count === 0) {
    $maxOrder = (int)$db->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE section = 'crm'")->fetchColumn();
    $stmt = $db->prepare("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES (?, ?, ?, NULL, ?, ?, ?, 1)");
    $stmt->execute(['Reviews & Testimonials', 'fas fa-star', '/admin/reviews', 'crm', $maxOrder + 1, 'manage_reviews']);
    echo "OK menu item added\n";
} else {
    echo "Menu item already exists\n";
}?>