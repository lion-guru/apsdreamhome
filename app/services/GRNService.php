<?php
/**
 * GRNService — Goods Receipt Note management (Gap 6).
 *
 * Wraps the existing grn_records table (created by upgrade_purchase_orders_s268.php)
 * plus the new grn_items table. Provides list/detail/create-from-PO/status-workflow
 * for goods received against purchase orders.
 */
namespace App\Services;

use App\Core\Database\Database;
use Exception;

class GRNService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List all GRNs, optionally filtered by status, with PO/vendor summary.
     */
    public function getAll(?string $status = null): array
    {
        $tid = $this->tenantId();
        $params = [$tid];
        $where = "g.tenant_id = ?";
        if ($status) {
            $where .= " AND g.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT g.*, po.order_number, po.vendor_name AS po_vendor_name,
                       po.total_amount AS po_total_amount, po.status AS po_status
                FROM grn_records g
                LEFT JOIN purchase_orders po ON po.id = g.purchase_order_id
                WHERE $where
                ORDER BY g.created_at DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    /**
     * Single GRN with its items.
     */
    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $grn = $this->db->fetchOne(
            "SELECT g.*, po.order_number, po.vendor_name AS po_vendor_name,
                      po.status AS po_status, po.total_amount AS po_total_amount
             FROM grn_records g
             LEFT JOIN purchase_orders po ON po.id = g.purchase_order_id
             WHERE g.id = ? AND g.tenant_id = ?",
            [$id, $tid]
        );
        if (!$grn) {
            return null;
        }
        $grn['items'] = $this->getItems($id);
        return $grn;
    }

    /**
     * Items for a GRN.
     */
    public function getItems(int $grnId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM grn_items WHERE grn_id = ? ORDER BY id ASC",
            [$grnId]
        ) ?: [];
    }

    /**
     * Create a GRN from an approved/received purchase order.
     * Returns the new GRN id.
     */
    public function createFromPo(int $poId, array $data): int
    {
        $tid = $this->tenantId();
        $po = $this->db->fetchOne(
            "SELECT * FROM purchase_orders WHERE id = ? AND tenant_id = ?",
            [$poId, $tid]
        );
        if (!$po) {
            throw new Exception('Purchase order not found');
        }

        $grnNumber = !empty($data['grn_number'])
            ? $data['grn_number']
            : ('GRN-' . date('YmdHis'));

        $this->db->beginTransaction();
        try {
            $grnId = (int)$this->db->insert('grn_records', [
                'tenant_id' => $tid,
                'grn_number' => $grnNumber,
                'purchase_order_id' => $poId,
                'received_by' => $_SESSION['user_id'] ?? 1,
                'received_at' => date('Y-m-d H:i:s'),
                'vendor_id' => $po['vendor_id'],
                'vendor_name' => $po['vendor_name'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'transport_name' => $data['transport_name'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_phone' => $data['driver_phone'] ?? null,
                'challan_number' => $data['challan_number'] ?? null,
                'challan_date' => $data['challan_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'total_items' => 0,
                'total_quantity' => 0,
                'total_amount' => 0,
            ]);

            // Aggregate from PO items if no explicit items posted
            $postedItems = !empty($data['items']) ? $data['items'] : [];
            if (empty($postedItems)) {
                $poItems = $this->db->fetchAll(
                    "SELECT * FROM purchase_order_items WHERE purchase_order_id = ?",
                    [$poId]
                ) ?: [];
                foreach ($poItems as $item) {
                    $this->db->insert('grn_items', [
                        'tenant_id' => $tid,
                        'grn_id' => $grnId,
                        'purchase_order_item_id' => $item['id'],
                        'item_name' => $item['item_name'],
                        'item_description' => $item['item_description'],
                        'unit' => $item['unit'],
                        'ordered_qty' => $item['quantity'],
                        'received_qty' => $item['received_qty'],
                        'accepted_qty' => $item['accepted_qty'],
                        'rejected_qty' => $item['rejected_qty'],
                        'rejection_reason' => $item['rejection_reason'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                        'quality_remarks' => null,
                    ]);
                }
            } else {
                foreach ($postedItems as $it) {
                    $this->db->insert('grn_items', [
                        'tenant_id' => $tid,
                        'grn_id' => $grnId,
                        'purchase_order_item_id' => $it['purchase_order_item_id'] ?? null,
                        'item_name' => $it['item_name'] ?? '',
                        'item_description' => $it['item_description'] ?? null,
                        'unit' => $it['unit'] ?? 'Nos',
                        'ordered_qty' => (float)($it['ordered_qty'] ?? 0),
                        'received_qty' => (float)($it['received_qty'] ?? 0),
                        'accepted_qty' => (float)($it['accepted_qty'] ?? 0),
                        'rejected_qty' => (float)($it['rejected_qty'] ?? 0),
                        'rejection_reason' => $it['rejection_reason'] ?? null,
                        'unit_price' => (float)($it['unit_price'] ?? 0),
                        'line_total' => (float)($it['line_total'] ?? 0),
                        'quality_remarks' => $it['quality_remarks'] ?? null,
                    ]);
                }
            }

            $this->recomputeTotals($grnId);

            // Mark PO as received
            $this->db->update('purchase_orders', ['status' => 'received', 'received_by' => $_SESSION['user_id'] ?? 1, 'received_at' => date('Y-m-d H:i:s')], ['id' => $poId]);

            $this->db->commit();
            return $grnId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update GRN status (draft -> verified -> accepted/rejected).
     */
    public function updateStatus(int $id, string $status, int $userId): bool
    {
        $allowed = ['draft', 'verified', 'accepted', 'partial', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            throw new Exception('Invalid GRN status');
        }
        $tid = $this->tenantId();
        $grn = $this->db->fetchOne(
            "SELECT id FROM grn_records WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$grn) {
            return false;
        }
        return (bool)$this->db->update('grn_records', ['status' => $status], ['id' => $id]);
    }

    /**
     * Add/update a line item on a GRN.
     */
    public function addItem(int $grnId, array $data): int
    {
        $tid = $this->tenantId();
        $grn = $this->db->fetchOne("SELECT id FROM grn_records WHERE id = ? AND tenant_id = ?", [$grnId, $tid]);
        if (!$grn) {
            throw new Exception('GRN not found');
        }
        $itemId = (int)$this->db->insert('grn_items', [
            'tenant_id' => $tid,
            'grn_id' => $grnId,
            'purchase_order_item_id' => $data['purchase_order_item_id'] ?? null,
            'item_name' => $data['item_name'] ?? '',
            'item_description' => $data['item_description'] ?? null,
            'unit' => $data['unit'] ?? 'Nos',
            'ordered_qty' => (float)($data['ordered_qty'] ?? 0),
            'received_qty' => (float)($data['received_qty'] ?? 0),
            'accepted_qty' => (float)($data['accepted_qty'] ?? 0),
            'rejected_qty' => (float)($data['rejected_qty'] ?? 0),
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'unit_price' => (float)($data['unit_price'] ?? 0),
            'line_total' => (float)($data['line_total'] ?? 0),
            'quality_remarks' => $data['quality_remarks'] ?? null,
        ]);
        $this->recomputeTotals($grnId);
        return $itemId;
    }

    /**
     * Aggregate stats for dashboard.
     */
    public function getStats(): array
    {
        $tid = $this->tenantId();
        $total = (float)$this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount),0) FROM grn_records WHERE tenant_id = ?",
            [$tid]
        )[''] ?? 0;
        $byStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS amt
             FROM grn_records WHERE tenant_id = ? GROUP BY status",
            [$tid]
        ) ?: [];
        return ['total_amount' => $total, 'by_status' => $byStatus];
    }

    private function recomputeTotals(int $grnId): void
    {
        $agg = $this->db->fetchOne(
            "SELECT COUNT(*) AS items, COALESCE(SUM(received_qty),0) AS qty, COALESCE(SUM(line_total),0) AS amt
             FROM grn_items WHERE grn_id = ?",
            [$grnId]
        );
        $this->db->update('grn_records', [
            'total_items' => (int)($agg['items'] ?? 0),
            'total_quantity' => (float)($agg['qty'] ?? 0),
            'total_amount' => (float)($agg['amt'] ?? 0),
        ], ['id' => $grnId]);
    }

    private function tenantId(): int
    {
        $tid = $_SESSION['tenant_id'] ?? 1;
        return (int)$tid;
    }
}
