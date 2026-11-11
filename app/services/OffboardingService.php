<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class OffboardingService
{
    /**
     * Query runner: fetch a single row.
     *
     * NOTE (S451): this service calls $this->fetchOne($sql, $params) but the
     * helper never existed -> "Call to undefined method <Service>::fetchOne()".
     */
    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[' . __CLASS__ . '::fetchOne] ' . $e->getMessage());
            return null;
        }
    }

    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getOffboardingChecklist(int $employeeId): array
    {
        return $this->fetchAll("
            SELECT oc.*, t.name as template_name
            FROM offboarding_checklists oc
            LEFT JOIN offboarding_templates t ON oc.template_id = t.id
            WHERE oc.employee_id = ?{$this->tenantSql()}
            ORDER BY oc.assigned_at ASC
        ", array_merge([$employeeId], $this->tVal()));
    }

    public function getOffboardingProgress(int $employeeId): array
    {
        $checklist = $this->getOffboardingChecklist($employeeId);
        $total = count($checklist);
        $completed = count(array_filter($checklist, fn($c) => $c['status'] === 'completed'));
        $inProgress = count(array_filter($checklist, fn($c) => $c['status'] === 'in_progress'));
        $pending = count(array_filter($checklist, fn($c) => $c['status'] === 'pending'));
        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'items' => $checklist
        ];
    }

    public function assignTemplate(int $employeeId, int $templateId, int $assignedBy): array
    {
        $templateItems = $this->fetchAll("
            SELECT * FROM offboarding_template_items 
            WHERE template_id = ? AND is_active = 1
            ORDER BY sort_order", [$templateId]);
        if (empty($templateItems)) return ['success' => false, 'message' => 'Template has no items'];

        $this->db->beginTransaction();
        try {
            foreach ($templateItems as $item) {
                $this->execute("
                    INSERT IGNORE INTO offboarding_checklists
                    (tenant_id, employee_id, template_id, item_name, description, category, is_mandatory,
                     assigned_to, due_date, status, assigned_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ", [
                    $this->tenantId(), $employeeId, $templateId,
                    $item['item_name'], $item['description'] ?? null,
                    $item['category'] ?? 'other', $item['is_mandatory'] ?? 1,
                    $item['assigned_to_role'] ?? null,
                    $item['due_days'] ? date('Y-m-d', strtotime("+{$item['due_days']} days")) : null
                ]);
            }
            $this->db->commit();
            return ['success' => true, 'message' => 'Offboarding template assigned'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateItemStatus(int $itemId, int $employeeId, string $status, array $data = []): array
    {
        $item = $this->fetchOne("SELECT * FROM offboarding_checklists WHERE id = ? AND employee_id = ?", [$itemId, $employeeId]);
        if (!$item) return ['success' => false, 'message' => 'Item not found'];
        $updates = ['status = ?'];
        $params = [$status];
        if ($status === 'completed') {
            $updates[] = 'completed_at = NOW()';
            $updates[] = 'completed_by = ?';
            $params[] = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
        }
        if (!empty($data['notes'])) {
            $updates[] = 'notes = ?';
            $params[] = $data['notes'];
        }
        $params[] = $itemId;
        $this->execute("UPDATE offboarding_checklists SET " . implode(', ', $updates) . " WHERE id = ?", array_merge($params, [$itemId]));
        return ['success' => true, 'message' => 'Status updated'];
    }

    public function getTemplates(): array
    {
        return $this->fetchAll("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM offboarding_template_items WHERE template_id = t.id) as item_count
            FROM offboarding_templates t
            WHERE t.is_active = 1
            ORDER BY t.name");
    }

    public function createTemplate(array $data): array
    {
        $this->db->beginTransaction();
        try {
            $this->execute("
                INSERT INTO offboarding_templates (tenant_id, name, description, is_active, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ", [$this->tenantId(), $data['name'], $data['description'] ?? '']);
            $templateId = $this->db->lastInsertId();
            if (!empty($data['items'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO offboarding_template_items
                    (tenant_id, template_id, item_name, description, category, is_mandatory,
                     assigned_to_role, due_days, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($data['items'] as $i => $item) {
                    $stmt->execute([
                        $this->tenantId(), $templateId,
                        $item['item_name'], $item['description'] ?? null,
                        $item['category'] ?? 'other', $item['is_mandatory'] ?? 1,
                        $item['assigned_to_role'] ?? null, $item['due_days'] ?? null, $i
                    ]);
                }
            }
            $this->db->commit();
            return ['success' => true, 'message' => 'Template created'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOffboardingStats(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as c FROM offboarding_checklists WHERE tenant_id = ?", [$this->tenantId()])['c'] ?? 0;
        $completed = $this->fetchOne("SELECT COUNT(*) as c FROM offboarding_checklists WHERE tenant_id = ? AND status = 'completed'", [$this->tenantId()])['c'] ?? 0;
        $pending = $this->fetchOne("SELECT COUNT(*) as c FROM offboarding_checklists WHERE tenant_id = ? AND status = 'pending'", [$this->tenantId()])['c'] ?? 0;
        return [
            'total_items' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($total - $pending) / $total * 100, 1) : 0
        ];
    }

    public function initiateOffboarding(int $employeeId, int $initiatedBy, array $data = []): array
    {
        $employee = $this->fetchOne("SELECT ep.legacy_id AS id, ep.*, u.name FROM employee_profiles ep LEFT JOIN users u ON u.id = ep.user_id WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?) AND ep.tenant_id = ? LIMIT 1", array_merge([$employeeId, $employeeId, $employeeId, $this->tenantId()], $this->tVal()));
        if (!$employee) return ['success' => false, 'message' => 'Employee not found'];
        $existing = $this->fetchOne("SELECT id FROM offboarding_checklists WHERE employee_id = ? AND status != 'completed'", [$employeeId]);
        if ($existing) return ['success' => false, 'message' => 'Offboarding already in progress'];
        $template = $this->fetchOne("SELECT id FROM offboarding_templates WHERE is_active = 1 ORDER BY id LIMIT 1");
        if ($template) {
            $result = $this->assignTemplate($employeeId, $template['id'], $_SESSION['admin_id'] ?? 1);
            if (!$result['success']) return $result;
        }
        $this->execute("UPDATE employee_profiles SET status = 'terminated', updated_at = NOW() WHERE (legacy_id = ? OR user_id = ? OR id = ?) AND tenant_id = ?", array_merge([$employeeId, $employeeId, $employeeId, $this->tenantId()], $this->tVal()));
        $this->revokeAccess($employeeId);
        return ['success' => true, 'message' => 'Offboarding initiated'];
    }

    private function revokeAccess(int $employeeId): void
    {
        $this->execute("UPDATE users SET status = 'inactive' WHERE id = (SELECT user_id FROM employee_profiles WHERE (legacy_id = ? OR user_id = ? OR id = ?) LIMIT 1)", array_merge([$employeeId, $employeeId, $employeeId], $this->tVal()));
    }

    public function completeOffboarding(int $employeeId, array $data = []): array
    {
        $checklist = $this->getOffboardingChecklist($employeeId);
        $pending = array_filter($checklist, fn($c) => $c['status'] !== 'completed');
        if (!empty($pending)) return ['success' => false, 'message' => 'Complete all checklist items first'];
        $this->db->beginTransaction();
        try {
            $this->execute("UPDATE employees SET status = 'offboarded', offboarded_at = NOW() WHERE id = ?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
            if (!empty($data['settlement_amount'])) {
                $this->execute("INSERT INTO employee_fnf_settlements (employee_id, settlement_amount, settlement_date, processed_by, notes, status, tenant_id) VALUES (?, ?, CURDATE(), ?, ?, 'completed', ?)", [$employeeId, $data['settlement_amount'], $data['processed_by'] ?? 1, $data['notes'] ?? '', $this->tenantId()]);
            }
            $this->db->commit();
            return ['success' => true, 'message' => 'Offboarding completed'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Query runner used by this service.
     *
     * NOTE (S451): this service calls $this->fetchAll($sql, $params)
     * internally, but the method NEVER existed -> every caller died with
     * "Call to undefined method <Service>::fetchAll()".
     */
    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[' . __CLASS__ . '::fetchAll] ' . $e->getMessage());
            return [];
        }
    }

    public function getAllOffboardingStatus(): array
    {
        return $this->fetchAll("
            SELECT ep.legacy_id AS id, u.name, ep.employee_code,
                    ep.department AS department,
                    ep.designation AS designation,
                    ep.status AS status,
                    CASE 
                        WHEN oc.employee_id IS NOT NULL THEN 'in_progress'
                        WHEN ep.status = 'terminated' THEN 'completed'
                        ELSE 'not_started'
                    END as offboarding_status,
                    COUNT(oc.id) as total_items,
                    SUM(CASE WHEN oc.status = 'completed' THEN 1 ELSE 0 END) as completed_items
            FROM employee_profiles ep
            LEFT JOIN users u ON u.id = ep.user_id
            LEFT JOIN offboarding_checklists oc ON oc.employee_id = ep.legacy_id
            WHERE ep.status IN ('active', 'terminated', 'offboarded')
            GROUP BY ep.legacy_id, u.name, ep.employee_code, ep.department, ep.designation, ep.status
            ORDER BY u.name");
    }
}