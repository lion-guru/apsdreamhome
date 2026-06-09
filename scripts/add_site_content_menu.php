<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $pdo->exec("INSERT IGNORE INTO admin_menu_items (name, url, icon, section, order_index, is_active, permission_key) VALUES ('Site Content', '/admin/site-content', 'fa-edit', 'settings', 95, 1, 'admin')");
    echo "Menu item added\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
