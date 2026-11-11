<?php
/**
 * Low Stock Alert Cron
 * Runs daily at 06:00
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\Inventory\InventoryService;
use App\Services\NotificationService;

echo "=== Low Stock Alert Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = Database::getInstance()->getConnection();
    $inventory = new InventoryService();
    $notifications = new NotificationService();
    
    // Get low stock items (quantity <= reorder_level)
    $stmt = $db->prepare("
        SELECT i.*, p.name as product_name, p.reorder_level
        FROM inventory i
        JOIN products p ON p.id = i.product_id
        WHERE i.quantity <= p.reorder_level
        AND p.status = 'active'
        AND i.quantity > 0
    ");
    $stmt->execute();
    $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($lowStock)) {
        echo "No low stock items found.\n";
        exit(0);
    }
    
    echo "Found " . count($lowStock) . " low stock items\n";
    
    $alerted = 0;
    foreach ($lowStock as $item) {
        // Check if alert already sent recently (last 7 days)
        $check = $db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE type = 'low_stock' 
            AND related_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $check->execute([$item['product_id']]);
        
        if ($check->fetchColumn() > 0) {
            continue; // Already alerted recently
        }
        
        // Send notification to inventory managers
        $managers = $db->prepare("
            SELECT u.id, u.email, u.name 
            FROM users u 
            JOIN user_roles ur ON ur.user_id = u.id 
            WHERE ur.role IN ('admin', 'manager', 'inventory_manager')
            AND u.status = 'active'
        ");
        $managers->execute();
        $managerList = $managers->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($managerList as $manager) {
            $notifications->notify([
                'user_id' => $manager['id'],
                'title' => 'Low Stock Alert',
                'message' => "Product {$item['product_name']} is low on stock. Current: {$item['quantity']}, Reorder level: {$item['reorder_level']}",
                'type' => 'low_stock',
                'data' => [
                    'product_id' => $item['product_id'],
                    'current_quantity' => $item['quantity'],
                    'reorder_level' => $item['reorder_level'],
                ],
                'channels' => ['in_app', 'email'],
                'priority' => 'high',
            ]);
        }
        
        $alerted++;
    }
    
    echo "Alerts sent: $alerted\n";
    echo "✅ Low stock alert completed\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Low Stock Alert Completed ===\n";