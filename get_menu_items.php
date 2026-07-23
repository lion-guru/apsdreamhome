<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$stmt = $db->query('SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($items, JSON_PRETTY_PRINT);