<?php
require 'vendor/autoload.php';
$db = new App\Core\Database();
$pdo = $db->getPdo();
$stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
$stmt->execute(['/admin/live-chat']);
if (!$stmt->fetch()) {
    $pdo->exec("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at) VALUES ('Live Chat', 'fa-comments', '/admin/live-chat', 0, 'crm', 85, 1, NOW())");
    echo "Live Chat menu added\n";
} else {
    echo "Live Chat already in menu\n";
}?>