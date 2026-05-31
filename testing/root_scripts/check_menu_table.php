<?php
require 'config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();
$stmt = $pdo->query("SHOW TABLES LIKE 'admin_menu_items'");
$result = $stmt->fetch();

echo $result ? "Table EXISTS\n" : "Table NOT FOUND\n";

if ($result) {
    // Check if has data
    $count = $pdo->query("SELECT COUNT(*) FROM admin_menu_items")->fetchColumn();
    echo "Menu items count: $count\n";
    
    if ($count == 0) {
        echo "Table is EMPTY - needs seed data\n";
    }
}
