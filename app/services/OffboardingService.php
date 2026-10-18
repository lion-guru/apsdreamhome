<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class OffboardingService
{
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
        $employee = $this->fetchOne("SELECT * FROM employees WHERE id = ?", [$employeeId]);
        if (!$employee) return ['success' => false, 'message' => 'Employee not found'];
        $existing = $this->fetchOne("SELECT id FROM offboarding_checklists WHERE employee_id = ? AND status != 'completed'", [$employeeId]);
        if ($existing) return ['success' => false, 'message' => 'Offboarding already in progress'];
        $template = $this->fetchOne("SELECT id FROM offboarding_templates WHERE is_active = 1 ORDER BY id LIMIT 1");
        if ($template) {
            $result = $this->assignTemplate($employeeId, $template['id'], $_SESSION['admin_id'] ?? 1);
            if (!$result['success']) return $result;
        }
        $this->execute("UPDATE employees SET status = 'terminated', offboarded_at = NOW() WHERE id = ?", [$employeeId]);
        $this->revokeAccess($employeeId);
        return ['success' => true, 'message' => 'Offboarding initiated'];
    }

    private function revokeAccess(int $employeeId): void
    {
        $this->execute("UPDATE users SET status = 'inactive' WHERE id = (SELECT user_id FROM employees WHERE id = ?)", [$employeeId]);
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

    public function getAllOffboardingStatus(): array
    {
        return $this->fetchAll("
            SELECT e.id, e.name, e.employee_code, e.department, e.designation, e.status,
                   CASE 
                       WHEN oc.employee_id IS NOT NULL THEN 'in_progress'
                       WHEN e.status = 'terminated' THEN 'completed'
                       ELSE 'not_started'
                   END as offboarding_status,
                   COUNT(oc.id) as total_items,
                   SUM(CASE WHEN oc.status = 'completed' THEN 1 ELSE 0 END) as completed_items
            FROM employees e
            LEFT JOIN offboarding_checklists oc ON oc.employee_id = e.id
            WHERE e.status IN ('active', 'terminated', 'offboarded')
            GROUP BY e.id
            ORDER BY e.name");
    }
}