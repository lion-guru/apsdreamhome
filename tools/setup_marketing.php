<?php
/**
 * Setup Marketing System - Creates tables and menu items
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS marketing_strategies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(500) NULL,
    active TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS marketplace_apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NULL,
    app_url VARCHAR(500) NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Tables created: marketing_strategies, marketplace_apps\n";

// Check if marketing section exists
$check = $pdo->query("SELECT COUNT(*) FROM admin_menu_items WHERE section = 'marketing'")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT IGNORE INTO admin_menu_items (section, label, url, icon, parent_id, order_index, target, permission) VALUES
        ('marketing', 'Strategies', '/admin/marketing/strategies', 'fa-bullhorn', NULL, 5, '_self', NULL),
        ('marketing', 'Marketplace', '/admin/marketing/marketplace', 'fa-store', NULL, 6, '_self', NULL)");
    echo "Sidebar menu items inserted.\n";
} else {
    echo "Marketing section already exists in menu items.\n";
}

echo "All done.\n";
