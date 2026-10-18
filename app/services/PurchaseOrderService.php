<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class PurchaseOrderService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getAll(?string $status = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT po.*, v.name as vendor_name, d.name as department_name
                FROM purchase_orders po
                LEFT JOIN vendors v ON v.id = po.vendor_id
                LEFT JOIN departments d ON d.id = po.department_id
                WHERE 1=1" . ($tid > 1 ? " AND po.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) { $sql .= " AND po.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY po.created_at DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT po.*, v.name as vendor_name, v.contact_person as vendor_contact_person, v.phone as vendor_phone, v.email as vendor_email
                FROM purchase_orders po
                LEFT JOIN vendors v ON v.id = po.vendor_id
                WHERE po.id = ?" . ($tid > 1 ? " AND po.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne($sql, $params);
    }

    public function getItems(int $poId): array
    {
        return $this->db->fetchAll("SELECT * FROM purchase_order_items WHERE purchase_order_id = ? ORDER BY id", [$poId]) ?: [];
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $orderNumber = $data['order_number'] ?? ('PO-' . date('YmdHis'));
        $subtotal = (float)($data['subtotal'] ?? 0);
        $tax = (float)($data['tax_amount'] ?? 0);
        $discount = (float)($data['discount_amount'] ?? 0);
        $total = $subtotal + $tax - $discount;

        $this->db->beginTransaction();
        try {
            $insertData = [
                'tenant_id' => $tid,
                'vendor_id' => !empty($data['vendor_id']) ? (int)$data['vendor_id'] : null,
                'order_number' => $orderNumber,
                'order_date' => $data['order_date'] ?? date('Y-m-d'),
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'vendor_contact' => $data['vendor_contact'] ?? null,
                'vendor_email' => $data['vendor_email'] ?? null,
                'vendor_address' => $data['vendor_address'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'amount' => $total,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'delivery_location' => $data['delivery_location'] ?? null,
                'requested_by' => $_SESSION['user_id'] ?? 1,
                'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
                'colony_id' => !empty($data['colony_id']) ? (int)$data['colony_id'] : null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'status' => 'pending',
            ];
            $this->db->insert('purchase_orders', $insertData);
            $poId = (int)$this->db->lastInsertId();

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (empty($item['item_name'])) continue;
                    $qty = (float)($item['quantity'] ?? 1);
                    $price = (float)($item['unit_price'] ?? 0);
                    $discPct = (float)($item['discount_percent'] ?? 0);
                    $taxPct = (float)($item['tax_percent'] ?? 0);
                    $lineTotal = $qty * $price;
                    $lineTotal -= $lineTotal * $discPct / 100;
                    $lineTotal += $lineTotal * $taxPct / 100;
                    $this->db->insert('purchase_order_items', [
                        'tenant_id' => $tid,
                        'purchase_order_id' => $poId,
                        'item_name' => $item['item_name'],
                        'item_description' => $item['item_description'] ?? null,
                        'unit' => $item['unit'] ?? 'Nos',
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'discount_percent' => $discPct,
                        'tax_percent' => $taxPct,
                        'line_total' => round($lineTotal, 2),
                    ]);
                }
            }

            $this->db->commit();
            return $poId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['vendor_id','order_date','expected_delivery','vendor_name','vendor_contact','vendor_email','vendor_address','notes','terms_conditions','delivery_location','payment_terms','status','department_id','project_id','colony_id'];
        $updateData = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
        }
        if (isset($updateData['vendor_id'])) $updateData['vendor_id'] = !empty($updateData['vendor_id']) ? (int)$updateData['vendor_id'] : null;
        if (isset($updateData['department_id'])) $updateData['department_id'] = !empty($updateData['department_id']) ? (int)$updateData['department_id'] : null;
        if (empty($updateData)) return false;
        return $this->db->update('purchase_orders', $updateData, 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->delete('purchase_order_items', 'purchase_order_id = ?', [$id]);
            $this->db->delete('purchase_orders', 'id = ?', [$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function approve(int $id, int $userId): bool
    {
        return $this->db->update('purchase_orders', [
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function cancel(int $id): bool
    {
        return $this->db->update('purchase_orders', ['status' => 'cancelled'], 'id = ?', [$id]);
    }

    public function receive(int $id, array $data): int
    {
        $tid = $this->tenantId();
        $po = $this->getById($id);
        if (!$po) throw new Exception('PO not found');

        $grnNumber = $data['grn_number'] ?? ('GRN-' . date('YmdHis'));
        $totalItems = 0;
        $totalQty = 0;
        $totalAmount = 0;

        $this->db->beginTransaction();
        try {
            $this->db->insert('grn_records', [
                'tenant_id' => $tid,
                'grn_number' => $grnNumber,
                'purchase_order_id' => $id,
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
            ]);
            $grnId = (int)$this->db->lastInsertId();

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (empty($item['po_item_id'])) continue;
                    $qty = (float)($item['accepted_qty'] ?? 0);
                    $this->db->execute(
                        "UPDATE purchase_order_items SET received_qty = received_qty + ?, accepted_qty = accepted_qty + ? WHERE id = ?",
                        [$qty, $qty, (int)$item['po_item_id']]
                    );
                    $totalItems++;
                    $totalQty += $qty;
                    $lineTotal = (float)($this->db->fetchOne("SELECT line_total, quantity FROM purchase_order_items WHERE id = ?", [(int)$item['po_item_id']])['line_total'] ?? 0);
                    $totalAmount += $lineTotal * ($qty / max(1, (float)$this->db->fetchOne("SELECT quantity FROM purchase_order_items WHERE id = ?", [(int)$item['po_item_id']])['quantity']));
                }
            }

            $this->db->update('grn_records', ['total_items' => $totalItems, 'total_quantity' => $totalQty, 'total_amount' => $totalAmount], 'id = ?', [$grnId]);

            $this->db->update('purchase_orders', [
                'status' => 'received',
                'received_by' => $_SESSION['user_id'] ?? 1,
                'received_at' => date('Y-m-d H:i:s'),
                'grn_number' => $grnNumber,
            ], 'id = ?', [$id]);

            $this->db->commit();
            return $grnId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        return [
            'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM purchase_orders WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'pending' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM purchase_orders WHERE status='pending'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'approved' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM purchase_orders WHERE status='approved'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'received' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM purchase_orders WHERE status='received'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_value' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_amount),0) FROM purchase_orders WHERE status != 'cancelled'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
        ];
    }

    public function getVendors(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name, contact_person, phone, email FROM vendors WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name", $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getDepartments(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name FROM departments WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name", $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getGrnRecords(int $poId): array
    {
        return $this->db->fetchAll("SELECT * FROM grn_records WHERE purchase_order_id = ? ORDER BY created_at DESC", [$poId]) ?: [];
    }
}