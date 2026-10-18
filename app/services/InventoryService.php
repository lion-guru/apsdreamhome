<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class InventoryService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getAll(?string $status = null, ?string $category = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM material_inventory WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
        if ($category) { $sql .= " AND material_category = ?"; $params[] = $category; }
        $sql .= " ORDER BY material_category, material_name";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        return $this->db->fetchOne("SELECT * FROM material_inventory WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$id, $tid] : [$id]);
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $stock = (float)($data['current_stock'] ?? 0);
        $unitCost = (float)($data['unit_cost'] ?? 0);
        $minStock = (float)($data['minimum_stock'] ?? 0);

        $status = $stock <= 0 ? 'out_of_stock' : ($stock <= $minStock ? 'low_stock' : 'in_stock');

        $insertData = [
            'tenant_id' => $tid,
            'material_name' => $data['material_name'],
            'material_category' => $data['material_category'],
            'sku' => $data['sku'] ?? null,
            'unit' => $data['unit'] ?? 'Nos',
            'current_stock' => $stock,
            'minimum_stock' => $minStock,
            'maximum_stock' => (float)($data['maximum_stock'] ?? 0),
            'unit_cost' => $unitCost,
            'total_value' => round($stock * $unitCost, 2),
            'location' => $data['location'] ?? null,
            'supplier_name' => $data['supplier_name'] ?? null,
            'supplier_contact' => $data['supplier_contact'] ?? null,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ];
        $this->db->insert('material_inventory', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['material_name','material_category','sku','unit','minimum_stock','maximum_stock','unit_cost','location','supplier_name','supplier_contact','status','notes'];
        $updateData = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
        }
        if (empty($updateData)) return false;
        return $this->db->update('material_inventory', $updateData, 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('material_inventory', 'id = ?', [$id]);
    }

    public function stockIn(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $qty = (float)($data['quantity'] ?? 0);
        $unitCost = (float)($data['unit_cost'] ?? 0);
        if ($qty <= 0) throw new Exception('Quantity must be greater than 0');

        $this->db->beginTransaction();
        try {
            $material = $this->getById($id);
            if (!$material) throw new Exception('Material not found');

            $newStock = (float)$material['current_stock'] + $qty;
            $newUnitCost = $unitCost > 0 ? $unitCost : (float)$material['unit_cost'];
            $totalValue = round($newStock * $newUnitCost, 2);
            $minStock = (float)$material['minimum_stock'];
            $status = $newStock <= $minStock ? 'low_stock' : 'in_stock';

            $this->db->update('material_inventory', [
                'current_stock' => $newStock,
                'unit_cost' => $newUnitCost,
                'total_value' => $totalValue,
                'status' => $status,
                'last_restocked_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function stockOut(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $qty = (float)($data['quantity'] ?? 0);
        if ($qty <= 0) throw new Exception('Quantity must be greater than 0');

        $this->db->beginTransaction();
        try {
            $material = $this->getById($id);
            if (!$material) throw new Exception('Material not found');

            $currentStock = (float)$material['current_stock'];
            if ($qty > $currentStock) throw new Exception('Insufficient stock. Available: ' . $currentStock);

            $newStock = $currentStock - $qty;
            $totalValue = round($newStock * (float)$material['unit_cost'], 2);
            $minStock = (float)$material['minimum_stock'];
            $status = $newStock <= 0 ? 'out_of_stock' : ($newStock <= $minStock ? 'low_stock' : 'in_stock');

            $this->db->update('material_inventory', [
                'current_stock' => $newStock,
                'total_value' => $totalValue,
                'status' => $status,
            ], 'id = ?', [$id]);

            $this->db->insert('material_usage_log', [
                'tenant_id' => $tid,
                'material_id' => $id,
                'colony_id' => !empty($data['colony_id']) ? (int)$data['colony_id'] : null,
                'plot_id' => !empty($data['plot_id']) ? (int)$data['plot_id'] : null,
                'usage_date' => $data['usage_date'] ?? date('Y-m-d'),
                'quantity' => $qty,
                'unit' => $data['unit'] ?? $material['unit'],
                'purpose' => $data['purpose'] ?? null,
                'used_by' => $data['used_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getUsageLog(?int $materialId = null, ?int $colonyId = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT mul.*, mi.material_name, mi.material_category, mi.unit as material_unit
                FROM material_usage_log mul
                LEFT JOIN material_inventory mi ON mi.id = mul.material_id
                WHERE 1=1" . ($tid > 1 ? " AND mul.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($materialId) { $sql .= " AND mul.material_id = ?"; $params[] = $materialId; }
        if ($colonyId) { $sql .= " AND mul.colony_id = ?"; $params[] = $colonyId; }
        $sql .= " ORDER BY mul.usage_date DESC, mul.id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        return [
            'total_materials' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM material_inventory WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'in_stock' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM material_inventory WHERE status='in_stock'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'low_stock' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM material_inventory WHERE status='low_stock'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'out_of_stock' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM material_inventory WHERE status='out_of_stock'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_value' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_value),0) FROM material_inventory WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'by_category' => $this->db->fetchAll("SELECT material_category, COUNT(*) as cnt, SUM(total_value) as total_value FROM material_inventory WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " GROUP BY material_category", $tp),
        ];
    }

    public function getCategories(): array
    {
        return ['cement','steel','sand','bricks','tiles','paint','plumbing','electrical','wood','glass','morang','gitti','damar','soling_stone','electric_pole','transformer','jcb_diesel','other'];
    }

    public function getValuationReport(string $method = 'weighted_average'): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];

        $materials = $this->db->fetchAll(
            "SELECT id, material_name, material_category, unit, current_stock, minimum_stock, unit_cost, total_value, status, location
             FROM material_inventory
             WHERE current_stock > 0" . $tf . " ORDER BY material_category, material_name",
            $tp
        ) ?: [];

        $report = [];
        $grandTotalQty = 0.0;
        $grandTotalValue = 0.0;

        foreach ($materials as $m) {
            $qty = (float)$m['current_stock'];
            $unitCost = (float)$m['unit_cost'];
            $totalValue = (float)$m['total_value'];

            if ($method === 'fifo') {
                $valuedUnitCost = $unitCost;
                $methodLabel = 'FIFO (recent cost)';
            } elseif ($method === 'lifo') {
                $valuedUnitCost = $unitCost;
                $methodLabel = 'LIFO (oldest cost)';
            } else {
                $valuedUnitCost = $unitCost;
                $methodLabel = 'Weighted Average';
            }

            $report[] = [
                'id' => (int)$m['id'],
                'material_name' => $m['material_name'],
                'material_category' => $m['material_category'],
                'unit' => $m['unit'],
                'quantity' => $qty,
                'unit_cost' => round($valuedUnitCost, 4),
                'total_value' => round($qty * $valuedUnitCost, 2),
                'book_value' => round($totalValue, 2),
                'status' => $m['status'],
                'location' => $m['location'],
                'method' => $methodLabel,
            ];

            $grandTotalQty += $qty;
            $grandTotalValue += round($qty * $valuedUnitCost, 2);
        }

        return [
            'method' => $method,
            'method_label' => $methodLabel,
            'materials' => $report,
            'total_quantity' => round($grandTotalQty, 2),
            'total_value' => round($grandTotalValue, 2),
            'count' => count($report),
        ];
    }
}