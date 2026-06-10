<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$menuItems = [
    ['Site Settings', '/admin/site-settings', 'fa-cog', 'settings', 1, 1],
];

$stmt = $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, is_active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE url = VALUES(url), icon = VALUES(icon)");
$count = 0;
foreach ($menuItems as $item) {
    $stmt->execute($item);
    if ($stmt->rowCount() > 0) $count++;
}
echo "✓ {$count} menu items added\n";
