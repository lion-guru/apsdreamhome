<?php
/**
 * Payment Orphan Fix + Seed (S409-Reconciliation)
 * - Links Payment #4 (₹25L, booking 19 → 9028, customer NULL → 3, total 0 → 25L)
 * - Voids Payment #3 (₹0 junk pending)
 * - Adds menu item for reconciliation desk
 * Money-proof: pre/post snapshot, idempotent.
 */
require_once __DIR__ . '/../../config/bootstrap_cli.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

// Pre-state
$pre = $pdo->query("SELECT COUNT(*) c, COALESCE(SUM(amount),0) s FROM payments")->fetch(PDO::FETCH_ASSOC);
echo "Pre: payments={$pre['c']} sum={$pre['s']}\n";

// 1. Fix Payment #4
if (1) {
    $p = $pdo->query("SELECT * FROM payments WHERE id=4")->fetch(PDO::FETCH_ASSOC);
    if ($p) {
        echo "Payment 4 before: booking_id={$p['booking_id']}, customer_id=" . ($p['customer_id'] === null ? 'NULL' : $p['customer_id']) . ", total={$p['total_amount']}\n";
        if ((int)$p['booking_id'] !== 9028) {
            $pdo->prepare("UPDATE payments SET booking_id=9028, customer_id=3, total_amount=? WHERE id=4")->execute([(float)$p['amount']]);
            echo "Payment 4 FIXED: booking 19→9028, customer NULL→3, total 0→{$p['amount']}\n";
        } else {
            echo "Payment 4 already fixed, SKIP\n";
        }
    }
}

// 2. Void Payment #3 (zero junk)
if (1) {
    $p = $pdo->query("SELECT id, amount, status FROM payments WHERE id=3")->fetch(PDO::FETCH_ASSOC);
    if ($p && (float)$p['amount'] == 0 && $p['status'] === 'pending') {
        $pdo->exec("UPDATE payments SET status='cancelled' WHERE id=3");
        echo "Payment 3 VOIDED (zero-amount junk)\n";
    } elseif ($p) {
        echo "Payment 3 SKIP (status={$p['status']})\n";
    }
}

// 3. Fix total_amount for Payment #4 already done; leave 1/2/8 (tax-inclusive totals, orphan booking honest)

$pdo->exec("INSERT INTO admin_menu_items (name, url, section, permission_key, icon, order_index, is_active)
            SELECT 'Payment Reconciliation', '/admin/payments/reconciliation', 'finance', 'financial.view', 'fas fa-balance-scale', 99, 1
            WHERE NOT EXISTS (SELECT 1 FROM admin_menu_items WHERE url='/admin/payments/reconciliation')");

// perms
foreach (['admin','super_admin'] as $role) {
    $id = $pdo->query("SELECT id FROM admin_menu_items WHERE url='/admin/payments/reconciliation' LIMIT 1")->fetchColumn();
    if ($id) {
        $ex = $pdo->prepare("SELECT 1 FROM admin_role_menu_permissions WHERE menu_item_id=? AND role=? LIMIT 1");
        $ex->execute([$id, $role]);
        if (!$ex->fetch()) {
            $pdo->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, ?, ?, 1, 1, 1, 0)")->execute([$role, $id]);
            echo "Perm added: $role menu $id\n";
        }
    }
}

// Post-state
$post = $pdo->query("SELECT COUNT(*) c, COALESCE(SUM(amount),0) s FROM payments")->fetch(PDO::FETCH_ASSOC);
$fixed = $pdo->query("SELECT id, booking_id, customer_id, amount, total_amount, status FROM payments WHERE id IN (3,4) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Post: payments={$post['c']} sum={$post['s']}\n";
foreach ($fixed as $r) echo "  Payment #{$r['id']}: booking={$r['booking_id']}, cust=" . ($r['customer_id'] ?? 'NULL') . ", amt={$r['amount']}, total={$r['total_amount']}, status={$r['status']}\n";
echo "\nNote: Payments 1/2/8 NULL-booking rakhe (tax-inclusive totals honest, koi booking exist nahi). Desk me Keep action hai.\n";
echo "Cleanup report (owner approval pending, not deleted): 69 test users, 5 fake leads live, bookings 9602+ zero-amount pending (parallel churn, hands-off).\n";
echo "Done.\n";
