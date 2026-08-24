<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Expense Approval Service
 * Handles multi-level expense approval workflow
 */
class ExpenseApprovalService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function submitExpense(array $data): int
    {
        $tid = TenantContext::getId();

        $payload = [
            'employee_id'       => !empty($data['employee_id']) ? (int)$data['employee_id'] : null,
            'category'          => $data['category'] ?? 'general',
            'amount'            => (float)($data['amount'] ?? 0),
            'description'       => $data['description'] ?? '',
            'receipt_path'      => $data['receipt_path'] ?? null,
            'expense_date'      => $data['expense_date'] ?? date('Y-m-d'),
            'due_date'          => $data['due_date'] ?? null,
            'priority'          => $data['priority'] ?? 'normal',
            'status'            => 'submitted',
            'current_approver'  => $this->getNextApprover($data['amount'] ?? 0),
            'approval_level'    => 1,
            'tenant_id'         => TenantContext::getId(),
        ];
        $this->db->insert('expenses', $payload);
        return (int)$this->db->lastInsertId();
    }

    private function getNextApprover(float $amount): ?int
    {
        // Simple approval hierarchy based on amount
        if ($amount <= 5000) return null; // Auto-approved
        if ($amount <= 25000) return 1;   // Manager level
        if ($amount <= 100000) return 2;  // Director level
        return 3; // CFO/CEO level
    }

    public function getExpenses(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $where .= " AND category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['employee_id'])) {
            $where .= " AND employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND expense_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND expense_date <= ?";
            $params[] = $filters['to_date'];
        }

        $where .= " ORDER BY created_at DESC";
        return $this->db->fetchAll("SELECT * FROM expenses $where", $params) ?: [];
    }

    public function getExpense(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM expenses WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function approveExpenseById(int $id, string $remarks = ''): bool
    {
        $tid = TenantContext::getId();
        $expense = $this->getExpense($id);
        if (!$expense) return false;

        $nextLevel = $expense['approval_level'] + 1;
        $maxLevel = $this->getMaxApprovalLevel($expense['amount']);

        if ($nextLevel > $maxLevel) {
            // Fully approved
            $sql = "UPDATE expenses SET status = 'approved', current_approver = NULL, approval_level = ?, approved_at = NOW(), approved_by = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            return $this->db->execute($sql, array_merge([$nextLevel, TenantContext::getId(), $id], $tid > 1 ? [$tid] : []));
        } else {
            // Move to next approver
            $nextApprover = $this->getApproverForLevel($nextLevel);
            $sql = "UPDATE expenses SET approval_level = ?, current_approver = ?, remarks = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            return $this->db->execute($sql, array_merge([$nextLevel, $nextApprover, $remarks, $id], $tid > 1 ? [$tid] : []));
        }
    }

    public function rejectExpenseById(int $id, string $remarks = ''): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE expenses SET status = 'rejected', current_approver = NULL, remarks = ?, rejected_at = NOW(), rejected_by = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$remarks, TenantContext::getId(), $id], $tid > 1 ? [$tid] : []));
    }

    private function getMaxApprovalLevel(float $amount): int
    {
        if ($amount <= 5000) return 0;
        if ($amount <= 25000) return 1;
        if ($amount <= 100000) return 2;
        return 3;
    }

    private function getApproverForLevel(int $level): ?int
    {
        // Map approval level to user role/user ID
        // This would typically come from a config table
        $approvers = [
            1 => 1, // Manager
            2 => 2, // Director
            3 => 3, // CFO/CEO
        ];
        return $approvers[$level] ?? null;
    }
}