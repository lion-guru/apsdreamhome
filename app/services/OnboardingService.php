<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class OnboardingService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get onboarding checklist for an employee
     */
    public function getOnboardingChecklist(int $employeeId): array
    {
        return $this->fetchAll("
            SELECT oc.*, t.name as template_name
            FROM onboarding_checklists oc
            LEFT JOIN onboarding_templates t ON oc.template_id = t.id
            WHERE oc.employee_id = ?{$this->tenantSql()}
            ORDER BY oc.assigned_at ASC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get onboarding progress for an employee
     */
    public function getOnboardingProgress(int $employeeId): array
    {
        $checklist = $this->getOnboardingChecklist($employeeId);
        
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

    /**
     * Assign onboarding template to employee
     */
    public function assignTemplate(int $employeeId, int $templateId, int $assignedBy): array
    {
        // Get template items
        $templateItems = $this->fetchAll("
            SELECT * FROM onboarding_template_items 
            WHERE template_id = ? AND is_active = 1
            ORDER BY sort_order", [$templateId]);

        if (empty($templateItems)) {
            return ['success' => false, 'message' => 'Template has no items'];
        }

        $this->db->beginTransaction();
        try {
            foreach ($templateItems as $item) {
                $this->execute("
                    INSERT IGNORE INTO onboarding_checklists
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
            return ['success' => true, 'message' => 'Onboarding template assigned'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update checklist item status
     */
    public function updateItemStatus(int $itemId, int $employeeId, string $status, array $data = []): array
    {
        $item = $this->fetchOne("SELECT * FROM onboarding_checklists WHERE id = ? AND employee_id = ?", [$itemId, $employeeId]);
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
        if (!empty($data['completed_at_ts'])) {
            $updates[] = 'completed_at_ts = ?';
            $params[] = $data['completed_at_ts'];
        }

        $params[] = $itemId;
        $this->execute(
            "UPDATE onboarding_checklists SET " . implode(', ', $updates) . " WHERE id = ?",
            array_merge($params, [$itemId])
        );

        return ['success' => true, 'message' => 'Status updated'];
    }

    /**
     * Get onboarding templates
     */
    public function getTemplates(): array
    {
        return $this->fetchAll("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM onboarding_template_items WHERE template_id = t.id) as item_count
            FROM onboarding_templates t
            WHERE t.is_active = 1
            ORDER BY t.name");
    }

    /**
     * Create onboarding template
     */
    public function createTemplate(array $data): array
    {
        $this->db->beginTransaction();
        try {
            $this->execute("
                INSERT INTO onboarding_templates (tenant_id, name, description, is_active, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ", [$this->tenantId(), $data['name'], $data['description'] ?? '']);

            $templateId = $this->db->lastInsertId();

            if (!empty($data['items'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO onboarding_template_items
                    (tenant_id, template_id, item_name, description, category, is_mandatory,
                     assigned_to_role, due_days, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($data['items'] as $i => $item) {
                    $stmt->execute([
                        $this->tenantId(), $this->db->lastInsertId(),
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

    /**
     * Get onboarding stats for dashboard
     */
    public function getOnboardingStats(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as c FROM onboarding_checklists WHERE tenant_id = ?", [$this->tenantId()])['c'] ?? 0;
        $completed = $this->fetchOne("SELECT COUNT(*) as c FROM onboarding_checklists WHERE tenant_id = ? AND status = 'completed'", [$this->tenantId()])['c'] ?? 0;
        $pending = $this->fetchOne("SELECT COUNT(*) as c FROM onboarding_checklists WHERE tenant_id = ? AND status = 'pending'", [$this->tenantId()])['c'] ?? 0;
        $inProgress = $this->fetchOne("SELECT COUNT(*) as c FROM onboarding_checklists WHERE tenant_id = ? AND status = 'in_progress'", [$this->tenantId()])['c'] ?? 0;

        return [
            'total_items' => $total,
            'completed' => $completed,
            'in_progress' => $total - $completed - $pending,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($total - $inProgress - $pending) / $total * 100, 1) : 0
        ];
    }
}