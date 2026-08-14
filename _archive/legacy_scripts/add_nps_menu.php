<?php
require 'vendor/autoload.php';
$db = new App\Core\Database();
$pdo = $db->getPdo();
$stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
$stmt->execute(['/admin/nps']);
if (!$stmt->fetch()) {
    $pdo->exec("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at) VALUES ('NPS Surveys', 'fa-chart-pie', '/admin/nps', 0, 'crm', 88, 1, NOW())");
    echo "NPS Surveys menu added\n";
} else {
    echo "NPS Surveys already in menu\n";
}?>