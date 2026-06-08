<?php
require 'app/Core/Database.php';
require 'app/Core/Database/Database.php';

try {
    $db = App\Core\Database\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if admin_menu_items table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'admin_menu_items'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ admin_menu_items table exists\n";
        
        // Check if it has data
        $stmt = $conn->query("SELECT COUNT(*) as count FROM admin_menu_items");
        $count = $stmt->fetch();
        echo "📊 Total menu items: " . $count['count'] . "\n";
        
        // Show first few items
        if ($count['count'] > 0) {
            $stmt = $conn->query("SELECT * FROM admin_menu_items LIMIT 5");
            echo "\n📋 Sample menu items:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  - ID: {$row['id']}, Name: {$row['name']}, URL: {$row['url']}\n";
            }
        } else {
            echo "⚠️ Table exists but has no data\n";
        }
    } else {
        echo "❌ admin_menu_items table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
