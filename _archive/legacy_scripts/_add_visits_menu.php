<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (['/admin/visits' => 'Site Visits'] as $url => $name) {
    $count = (int)$db->query("SELECT COUNT(*) FROM admin_menu_items WHERE url = '$url'")->fetchColumn();
    if ($count === 0) {
        $section = $url === '/admin/visits' ? 'crm' : 'crm';
        $maxOrder = (int)$db->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE section = '$section'")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES (?, ?, ?, NULL, ?, ?, ?, 1)");
        $stmt->execute([$name, 'fas fa-calendar-check', $url, $section, $maxOrder + 1, 'manage_visits']);
        echo "OK added $name\n";
    } else {
        echo "$name already exists\n";
    }
}?>