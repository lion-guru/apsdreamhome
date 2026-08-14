<?php
define('APP_ROOT', __DIR__ . '/../');
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();
use App\Core\Database\Database;
$db = Database::getInstance();

$items = [
    ['Auto Dialer', 'fas fa-phone-volume', '/admin/ai-calling/auto-dialer', 197],
    ['Call Analytics', 'fas fa-chart-pie', '/admin/ai-calling/call-analytics', 198],
];
foreach ($items as [$name, $icon, $url, $order]) {
    $exists = $db->fetch("SELECT id FROM admin_menu_items WHERE url = ?", [$url]);
    if ($exists) { echo "EXISTS: $name\n"; continue; }
    $db->execute(
        "INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, permission_key, is_active)
         VALUES (?, ?, ?, 0, 'technology', ?, NULL, 1)",
        [$name, $icon, $url, $order]
    );
    echo "ADDED: $name (id=" . $db->lastInsertId() . ")\n";
}
echo "Done.\n";?>