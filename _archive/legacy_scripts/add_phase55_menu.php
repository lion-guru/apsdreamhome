<?php
require 'vendor/autoload.php';
$db = new App\Core\Database();
$pdo = $db->getPdo();
$items = [
    ['Property Auctions', 'fa-gavel', '/admin/auctions', 'sales', 95]
];
foreach ($items as $i) {
    $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
    $stmt->execute([$i[2]]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at) VALUES (?,?,?,0,?,?,1,NOW())")
            ->execute($i);
        echo "Added: {$i[0]}\n";
    } else {
        echo "Exists: {$i[0]}\n";
    }
}?>