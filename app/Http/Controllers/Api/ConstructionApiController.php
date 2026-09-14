<?php
namespace App\Http\Controllers\Api;

use App\Traits\TenantAwareTrait;

class ConstructionApiController extends BaseApiController
{
    use TenantAwareTrait;

    public function colonyProgress()
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $st = $db->prepare("SELECT c.id, c.name, c.slug, c.phase, c.total_plots, c.available_plots, c.status as colony_status, d.name as district_name,
                (SELECT COUNT(*) FROM colony_milestones m WHERE m.colony_id=c.id) as total_milestones,
                (SELECT COUNT(*) FROM colony_milestones m WHERE m.colony_id=c.id AND m.status='completed') as completed_milestones,
                (SELECT COALESCE(AVG(m.progress_pct),0) FROM colony_milestones m WHERE m.colony_id=c.id) as avg_progress
                FROM colonies c LEFT JOIN districts d ON c.district_id=d.id WHERE 1=1" . $tenantSql . " ORDER BY c.name ASC");
            $st->execute($tenantParams);
            $colonies = $st->fetchAll(\PDO::FETCH_ASSOC);
            return $this->jsonSuccess(['colonies' => $colonies]);
        } catch (\Exception $e) {
            error_log('ConstructionApiController::colonyProgress error: ' . $e->getMessage());
            return $this->jsonError('Failed', 500);
        }
    }

    public function colonyMilestones($colonyId = null)
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        $colonyId = (int)($colonyId ?? $_GET['colony_id'] ?? 0);
        if (!$colonyId) return $this->jsonError('colony_id required', 400);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $st = $db->prepare("SELECT m.*, c.name as colony_name FROM colony_milestones m LEFT JOIN colonies c ON m.colony_id=c.id WHERE m.colony_id=?" . $tenantSql . " ORDER BY m.category ASC, m.created_at DESC");
            $st->execute(array_merge([$colonyId], $tenantParams));
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            foreach ($rows as &$r) {
                if (!empty($r['site_photo_path'])) $r['site_photo_url'] = $base . '/' . $r['site_photo_path'];
            }
            return $this->jsonSuccess(['milestones' => $rows, 'colony_id' => $colonyId]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed', 500);
        }
    }

    public function materialInventory()
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $cat = trim((string)($_GET['category'] ?? ''));
            $status = trim((string)($_GET['status'] ?? ''));
            $sql = "SELECT * FROM material_inventory WHERE 1=1" . $tenantSql;
            $params = $tenantParams;
            if ($cat !== '') { $sql .= " AND material_category=?"; $params[] = $cat; }
            if ($status !== '') { $sql .= " AND status=?"; $params[] = $status; }
            $sql .= " ORDER BY material_name ASC";
            $st = $db->prepare($sql); $st->execute($params);
            $materials = $st->fetchAll(\PDO::FETCH_ASSOC);
            $total = 0; foreach ($materials as $m) $total += (float)($m['total_value'] ?? 0);
            // Recent usage
            $st = $db->prepare("SELECT l.*, m.material_name, m.material_category, c.name as colony_name FROM material_usage_log l LEFT JOIN material_inventory m ON l.material_id=m.id LEFT JOIN colonies c ON l.colony_id=c.id WHERE 1=1" . $tenantSql . " ORDER BY l.usage_date DESC, l.id DESC LIMIT 20");
            $st->execute($tenantParams);
            $logs = $st->fetchAll(\PDO::FETCH_ASSOC);
            return $this->jsonSuccess(['materials' => $materials, 'total_value' => $total, 'usage_logs' => $logs]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed', 500);
        }
    }

    public function logUsage()
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $input = $this->getJsonInput();
            $materialId = (int)($input['material_id'] ?? 0);
            $colonyId = (int)($input['colony_id'] ?? 0);
            $qty = (float)($input['quantity'] ?? 0);
            if ($materialId <= 0 || $qty <= 0) return $this->jsonError('material_id and quantity required', 400);
            $st = $db->prepare("SELECT * FROM material_inventory WHERE id=?" . $tenantSql);
            $st->execute(array_merge([$materialId], $tenantParams));
            $mat = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$mat) return $this->jsonError('Material not found', 404);
            if ($qty > (float)$mat['current_stock']) return $this->jsonError('Insufficient stock. Available: ' . $mat['current_stock'], 400);
            $db->beginTransaction();
            $tid = (int)$this->tenantId();
            $st = $db->prepare("INSERT INTO material_usage_log (tenant_id, material_id, colony_id, usage_date, quantity, unit, purpose, used_by, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $st->execute([$tid, $materialId, $colonyId ?: null, $input['usage_date'] ?? date('Y-m-d'), $qty, $input['unit'] ?? 'qty', $input['purpose'] ?? '', $input['used_by'] ?? '', $input['notes'] ?? '']);
            $newStock = (float)$mat['current_stock'] - $qty;
            $totalVal = round($newStock * (float)$mat['unit_cost'], 2);
            $newStatus = $newStock <= 0 ? 'out_of_stock' : (($mat['minimum_stock'] !== null && $newStock <= (float)$mat['minimum_stock']) ? 'low_stock' : 'in_stock');
            $st = $db->prepare("UPDATE material_inventory SET current_stock=?, total_value=?, status=?, updated_at=NOW() WHERE id=?" . $tenantSql);
            $st->execute(array_merge([$newStock, $totalVal, $newStatus, $materialId], $tenantParams));
            $db->commit();
            return $this->jsonSuccess(['new_stock' => $newStock, 'status' => $newStatus], 'Usage logged');
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
