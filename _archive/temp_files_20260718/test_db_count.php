<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM admin_menu_items");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "admin_menu_items count: " . $row['cnt'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
