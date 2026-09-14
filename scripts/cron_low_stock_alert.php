<?php
/**
 * Cron: Low Stock Alert
 * Checks material_inventory where status in (low_stock, out_of_stock) per tenant
 * Creates in-app notification for admin/operations roles (if notifications table exists) and logs
 * Run hourly: 0 * * * * php scripts/cron_low_stock_alert.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

try {
    $db = Database::getInstance()->getConnection();
    $tenants = [1];
    try {
        $st = $db->query("SELECT id FROM tenants WHERE status='active' LIMIT 20");
        $rows = $st->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($rows)) $tenants = array_map('intval', $rows);
    } catch (Exception $e) { $tenants = [1]; }

    foreach ($tenants as $tid) {
        TenantContext::setById((int)$tid);
        $st = $db->prepare("SELECT material_name, material_category, current_stock, minimum_stock, status FROM material_inventory WHERE tenant_id=? AND status IN ('low_stock','out_of_stock') LIMIT 20");
        $st->execute([$tid]);
        $lows = $st->fetchAll(PDO::FETCH_ASSOC);
        if (empty($lows)) { echo "Tenant $tid: no low stock\n"; continue; }
        $names = implode(', ', array_map(fn($r) => $r['material_name'] . ' (' . $r['status'] . ')', $lows));
        $msg = count($lows) . " material(s) low/out of stock: $names";
        echo "Tenant $tid: $msg\n";
        // Try to insert notification for admins (best-effort, ignore if table missing)
        try {
            $admins = $db->prepare("SELECT id FROM users WHERE tenant_id=? AND role IN ('admin','super_admin','operations_manager','construction_director','project_manager') AND status='active' LIMIT 10");
            $admins->execute([$tid]);
            $aids = $admins->fetchAll(PDO::FETCH_COLUMN);
            foreach ($aids as $aid) {
                $db->prepare("INSERT INTO notifications (tenant_id, user_id, title, message, type, is_read, created_at) VALUES (?, ?, 'Low Stock Alert', ?, 'warning', 0, NOW())")->execute([$tid, $aid, $msg]);
            }
        } catch (Exception $e) { error_log("cron_low_stock notification error: " . $e->getMessage()); }
    }
    echo "Low stock check done\n";
} catch (Exception $e) {
    error_log("cron_low_stock fatal: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
