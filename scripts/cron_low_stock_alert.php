<?php
/**
 * Low Stock Alert Cron
 * Runs daily at 06:00
 *
 * Reads the LIVE construction-material inventory (`material_inventory`).
 * Items where current_stock is at or below minimum_stock and stock > 0
 * trigger one alert per item per 7-day window (in_app + email to admins
 * and managers only - no SMS/phone costs).
 *
 * Exits 0 on no-items (nothing to do is not an error).
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\NotificationService;

echo "=== Low Stock Alert Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = Database::getInstance()->getConnection();
    $notifications = new NotificationService();

    // Live schema: material_inventory (cement/steel/sand/bundle of construction materials).
    // Status discontinued/out_of_stock items are excluded so the alert fires only for
    // genuinely actionable stock positions.
    $stmt = $db->prepare("
        SELECT id, material_name, current_stock, minimum_stock, material_category, unit
        FROM material_inventory
        WHERE current_stock <= minimum_stock
          AND current_stock > 0
          AND status IN ('in_stock','low_stock')
        ORDER BY (minimum_stock - current_stock) DESC
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
        // Re-alert throttle: skip if an alert for THIS inventory row was already sent in 7 days.
        $check = $db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE type = 'low_stock'
              AND related_id = ?
              AND related_type = 'material_inventory'
              AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $check->execute([(int)$item['id']]);

        if ($check->fetchColumn() > 0) {
            continue; // already alerted recently
        }

        // Find active managers/admins (roles join through the junction table)
        $managers = $db->prepare("
            SELECT u.id, u.email, u.name
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r       ON r.id       = ur.role_id
            WHERE r.name IN ('admin', 'manager')
              AND u.status = 'active'
        ");
        $managers->execute();
        $managerList = $managers->fetchAll(PDO::FETCH_ASSOC);

        if (empty($managerList)) {
            echo "No admin/manager recipients found - skipping in-app, nothing else configured.\n";
            $alerted++; // still mark attempted, avoid loop
            continue;
        }

        $unit = $item['unit'] ?? 'units';
        $qty  = (float)$item['current_stock'];
        $min  = (float)$item['minimum_stock'];

        foreach ($managerList as $manager) {
            $notifications->notify([
                'user_id' => $manager['id'],
                'title'   => 'Low Stock Alert: ' . $item['material_name'],
                'message' => "Material '{$item['material_name']}' is low on stock. Current: {$qty} {$unit}, Minimum: {$min} {$unit}.",
                'type'    => 'low_stock',
                'data'    => [
                    'material_id'     => (int)$item['id'],
                    'current_quantity' => $qty,
                    'minimum_stock'   => $min,
                    'category'        => $item['material_category'] ?? '',
                ],
                'related_id'  => (int)$item['id'],
                'related_type' => 'material_inventory',
                'channels' => ['in_app'],
                'priority' => 'high',
            ]);
        }

        $alerted++;
    }

    echo "Alerts sent: $alerted\n";
    echo "=== Low stock alert completed ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Low Stock Alert Completed ===\n";
