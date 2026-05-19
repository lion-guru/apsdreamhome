<?php
// Check RBAC Menu System
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== RBAC Menu System Analysis ===\n\n";

    // Check if admin_menu_items table exists
    $tables = ['admin_menu_items', 'admin_role_menu_permissions', 'admin_user_menu_permissions'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Get row count
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "   Rows: $count\n";
            
            // Get structure for admin_menu_items
            if ($table === 'admin_menu_items') {
                $cols = $db->query("SHOW COLUMNS FROM $table");
                echo "   Columns:\n";
                while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
                    echo "     - {$col['Field']} ({$col['Type']})\n";
                }
                
                // Get sample data
                $items = $db->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                echo "   Sample items:\n";
                foreach ($items as $item) {
                    echo "     - ID: {$item['id']}, Name: {$item['name']}, URL: {$item['url']}, Section: {$item['section']}\n";
                }
            }
        } else {
            echo "❌ Table '$table' does NOT exist\n";
        }
        echo "\n";
    }

    // Check if there are any menu items
    $stmt = $db->query("SELECT COUNT(*) FROM admin_menu_items WHERE is_active = 1");
    $activeCount = $stmt->fetchColumn();
    echo "Active menu items: $activeCount\n";

    if ($activeCount == 0) {
        echo "⚠️  No active menu items found - sidebar will be empty!\n";
        echo "   This is why you're seeing the fallback menu.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>