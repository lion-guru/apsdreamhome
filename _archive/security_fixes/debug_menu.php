<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT id, name, url, parent_id, section, is_active, order_index FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, order_index");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total active menu items: " . count($items) . "\n\n";
    
    foreach ($items as $item) {
        echo "ID: {$item['id']} | Name: {$item['name']} | URL: {$item['url']} | Parent: {$item['parent_id']} | Section: {$item['section']} | Order: {$item['order_index']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}