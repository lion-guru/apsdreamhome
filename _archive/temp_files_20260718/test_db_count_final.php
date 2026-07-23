<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM admin_menu_items WHERE is_active = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "active_menu_count=" . $row['cnt'] . "\n";
    
    $stmt2 = $pdo->query("SELECT COUNT(*) as cnt FROM admin_role_menu_permissions");
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "permissions_count=" . $row2['cnt'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
