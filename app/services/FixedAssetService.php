<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class FixedAssetService
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
        $sql = "SELECT fa.*, d.name as department_name, u.name as assigned_to_name
                FROM fixed_assets fa
                LEFT JOIN departments d ON d.id = fa.department_id
                LEFT JOIN users u ON u.id = fa.assigned_to
                WHERE 1=1" . ($tid > 1 ? " AND fa.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) { $sql .= " AND fa.status = ?"; $params[] = $status; }
        if ($category) { $sql .= " AND fa.category = ?"; $params[] = $category; }
        $sql .= " ORDER BY fa.asset_code";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT fa.*, d.name as department_name, u.name as assigned_to_name
                FROM fixed_assets fa
                LEFT JOIN departments d ON d.id = fa.department_id
                LEFT JOIN users u ON u.id = fa.assigned_to
                WHERE fa.id = ?" . ($tid > 1 ? " AND fa.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne($sql, $params);
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $purchaseCost = (float)$data['purchase_cost'];
        $salvageValue = (float)($data['salvage_value'] ?? 0);
        $usefulLife = (int)($data['useful_life_years'] ?? 5);
        $method = $data['depreciation_method'] ?? 'straight_line';

        $annualRate = $method === 'straight_line'
            ? (($purchaseCost - $salvageValue) / max(1, $usefulLife)) / $purchaseCost * 100
            : (float)($data['depreciation_rate'] ?? 20);

        $insertData = [
            'tenant_id' => $tid,
            'asset_code' => $data['asset_code'],
            'asset_name' => $data['asset_name'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'purchase_date' => $data['purchase_date'],
            'purchase_cost' => $purchaseCost,
            'salvage_value' => $salvageValue,
            'useful_life_years' => $usefulLife,
            'depreciation_method' => $method,
            'depreciation_rate' => $annualRate,
            'current_book_value' => $purchaseCost,
            'location' => $data['location'] ?? null,
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'assigned_to' => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null,
            'status' => $data['status'] ?? 'active',
            'invoice_number' => $data['invoice_number'] ?? null,
            'vendor_name' => $data['vendor_name'] ?? null,
            'warranty_expiry' => $data['warranty_expiry'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
        $this->db->insert('fixed_assets', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['asset_name','category','description','serial_number','location','department_id','assigned_to','status','notes','warranty_expiry'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) $updateData[$field] = $data[$field];
        }
        if (isset($updateData['department_id'])) $updateData['department_id'] = (int)$updateData['department_id'];
        if (isset($updateData['assigned_to'])) $updateData['assigned_to'] = (int)$updateData['assigned_to'];
        if (empty($updateData)) return false;
        return $this->db->update('fixed_assets', $updateData, 'id = ?', [$id]);
    }

    public function dispose(int $id, array $data): bool
    {
        return $this->db->update('fixed_assets', [
            'status' => $data['status'],
            'disposal_date' => $data['disposal_date'],
            'disposal_amount' => (float)($data['disposal_amount'] ?? 0),
        ], 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        $hasSchedules = $this->db->fetchColumn("SELECT COUNT(*) FROM asset_depreciation_schedules WHERE asset_id = ?", [$id]);
        if ($hasSchedules > 0) throw new Exception('Cannot delete asset with depreciation records.');
        return $this->db->delete('fixed_assets', 'id = ?', [$id]);
    }

    public function getDepreciationSchedule(int $assetId): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM asset_depreciation_schedules WHERE asset_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY period_year, period_month";
        $params = [$assetId];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function calculateDepreciation(int $assetId): array
    {
        $asset = $this->getById($assetId);
        if (!$asset) throw new Exception('Asset not found');

        $schedules = [];
        $bookValue = (float)$asset['purchase_cost'];
        $salvageValue = (float)$asset['salvage_value'];
        $usefulLife = (int)$asset['useful_life_years'];
        $method = $asset['depreciation_method'];
        $annualRate = (float)$asset['depreciation_rate'];
        $purchaseYear = (int)date('Y', strtotime($asset['purchase_date']));

        for ($year = 1; $year <= $usefulLife; $year++) {
            $currentYear = $purchaseYear + $year - 1;
            for ($month = 1; $month <= 12; $month++) {
                if ($bookValue <= $salvageValue) break;

                if ($method === 'straight_line') {
                    $monthlyDep = (($asset['purchase_cost'] - $salvageValue) / max(1, $usefulLife)) / 12;
                } else {
                    $monthlyDep = ($bookValue * $annualRate / 100) / 12;
                }

                $monthlyDep = min($monthlyDep, $bookValue - $salvageValue);
                $monthlyDep = max(0, $monthlyDep);

                $closingValue = $bookValue - $monthlyDep;
                $cumulative = $asset['purchase_cost'] - $closingValue;

                $schedules[] = [
                    'period_year' => $currentYear,
                    'period_month' => $month,
                    'opening_book_value' => round($bookValue, 2),
                    'depreciation_amount' => round($monthlyDep, 2),
                    'closing_book_value' => round($closingValue, 2),
                    'cumulative_depreciation' => round($cumulative, 2),
                ];

                $bookValue = $closingValue;
            }
            if ($bookValue <= $salvageValue) break;
        }
        return $schedules;
    }

    public function generateDepreciationSchedule(int $assetId, int $userId): int
    {
        $asset = $this->getById($assetId);
        if (!$asset) throw new Exception('Asset not found');

        $this->db->beginTransaction();
        try {
            $this->db->delete('asset_depreciation_schedules', 'asset_id = ?', [$assetId]);
            $schedules = $this->calculateDepreciation($assetId);
            $count = 0;
            foreach ($schedules as $s) {
                $this->db->insert('asset_depreciation_schedules', [
                    'tenant_id' => $asset['tenant_id'],
                    'asset_id' => $assetId,
                    'period_year' => $s['period_year'],
                    'period_month' => $s['period_month'],
                    'opening_book_value' => $s['opening_book_value'],
                    'depreciation_amount' => $s['depreciation_amount'],
                    'closing_book_value' => $s['closing_book_value'],
                    'cumulative_depreciation' => $s['cumulative_depreciation'],
                    'is_posted' => 0,
                ]);
                $count++;
            }
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function postDepreciation(int $assetId, int $year, int $month, int $userId): bool
    {
        $tid = $this->tenantId();
        $schedule = $this->db->fetchOne(
            "SELECT * FROM asset_depreciation_schedules WHERE asset_id = ? AND period_year = ? AND period_month = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$assetId, $year, $month], $tid > 1 ? [$tid] : [])
        );
        if (!$schedule) throw new Exception('Depreciation schedule not found for this period');
        if ($schedule['is_posted']) throw new Exception('Already posted for this period');

        $this->db->beginTransaction();
        try {
            $this->db->update('asset_depreciation_schedules', [
                'is_posted' => 1,
                'posted_at' => date('Y-m-d H:i:s'),
                'posted_by' => $userId,
            ], 'id = ?', [$schedule['id']]);

            $asset = $this->getById($assetId);
            $newBookValue = (float)$asset['current_book_value'] - (float)$schedule['depreciation_amount'];
            $this->db->update('fixed_assets', [
                'current_book_value' => round(max(0, $newBookValue), 2),
            ], 'id = ?', [$assetId]);

            $this->db->commit();
            return true;
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
            'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM fixed_assets WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM fixed_assets WHERE status='active'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_cost' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(purchase_cost),0) FROM fixed_assets WHERE status='active'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_book_value' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(current_book_value),0) FROM fixed_assets WHERE status='active'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'by_category' => $this->db->fetchAll("SELECT category, COUNT(*) as cnt, SUM(purchase_cost) as total_cost, SUM(current_book_value) as book_value FROM fixed_assets WHERE status='active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " GROUP BY category", $tp),
        ];
    }

    public function getDepreciationReport(?int $year = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT ds.*, fa.asset_code, fa.asset_name, fa.category, fa.depreciation_method
                FROM asset_depreciation_schedules ds
                JOIN fixed_assets fa ON fa.id = ds.asset_id
                WHERE 1=1" . ($tid > 1 ? " AND ds.tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];
        if ($year) { $sql .= " AND ds.period_year = ?"; $params[] = $year; }
        $sql .= " ORDER BY fa.asset_code, ds.period_year, ds.period_month";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getDepartments(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name, code FROM departments WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name", $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getEmployees(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('employee','manager','admin')" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name", $tid > 1 ? [$tid] : []) ?: [];
    }
}