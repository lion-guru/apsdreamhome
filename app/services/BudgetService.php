<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class BudgetService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    // ============================================================
    // BUDGETS CRUD
    // ============================================================
    public function getAll(?string $status = null, ?string $fiscalYear = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT b.*, d.name as department_name, d.code as department_code
                FROM budgets b
                LEFT JOIN departments d ON d.id = b.department_id
                WHERE 1=1" . ($tid > 1 ? " AND b.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        if ($fiscalYear) {
            $sql .= " AND b.fiscal_year = ?";
            $params[] = $fiscalYear;
        }
        $sql .= " ORDER BY b.fiscal_year DESC, b.period_start DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT b.*, d.name as department_name, d.code as department_code
                FROM budgets b
                LEFT JOIN departments d ON d.id = b.department_id
                WHERE b.id = ?" . ($tid > 1 ? " AND b.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne($sql, $params);
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $insertData = [
            'tenant_id' => $tid,
            'budget_name' => $data['budget_name'],
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'category' => $data['category'] ?? null,
            'fiscal_year' => $data['fiscal_year'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'allocated_amount' => (float)$data['allocated_amount'],
            'spent_amount' => 0.00,
            'committed_amount' => 0.00,
            'status' => $data['status'] ?? 'draft',
            'approved_by' => null,
        ];
        $this->db->insert('budgets', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $budget = $this->getById($id);
        if (!$budget) return false;

        $allowed = ['budget_name', 'department_id', 'category', 'fiscal_year', 'period_start', 'period_end', 'allocated_amount', 'status'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) return false;

        // Cast types
        if (isset($updateData['allocated_amount'])) $updateData['allocated_amount'] = (float)$updateData['allocated_amount'];
        if (isset($updateData['department_id'])) $updateData['department_id'] = !empty($updateData['department_id']) ? (int)$updateData['department_id'] : null;

        return $this->db->update('budgets', $updateData, 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        $tid = $this->tenantId();
        // Check if budget has expenses
        $expenseCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM budget_expenses WHERE budget_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$id, $tid] : [$id]
        );
        if ($expenseCount > 0) {
            throw new Exception("Cannot delete budget with existing expenses. Remove expenses first.");
        }
        return $this->db->delete('budgets', 'id = ?', [$id]);
    }

    public function approve(int $id, int $userId): bool
    {
        return $this->db->update('budgets', [
            'status' => 'approved',
            'approved_by' => $userId
        ], 'id = ?', [$id]);
    }

    public function activate(int $id): bool
    {
        return $this->db->update('budgets', ['status' => 'active'], 'id = ?', [$id]);
    }

    public function close(int $id): bool
    {
        return $this->db->update('budgets', ['status' => 'closed'], 'id = ?', [$id]);
    }

    // ============================================================
    // BUDGET EXPENSES
    // ============================================================
    public function getExpenses(int $budgetId): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM budget_expenses WHERE budget_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY expense_date DESC";
        $params = [$budgetId];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function addExpense(int $budgetId, array $data): int
    {
        $tid = $this->tenantId();
        $budget = $this->getById($budgetId);
        if (!$budget) throw new Exception('Budget not found');

        $insertData = [
            'tenant_id' => $tid,
            'budget_id' => $budgetId,
            'expense_date' => $data['expense_date'],
            'vendor' => $data['vendor'] ?? null,
            'description' => $data['description'] ?? null,
            'amount' => (float)$data['amount'],
            'bill_number' => $data['bill_number'] ?? null,
            'bill_file' => $data['bill_file'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ];

        // Start transaction
        $this->db->beginTransaction();
        try {
            $this->db->insert('budget_expenses', $insertData);
            $expenseId = (int)$this->db->lastInsertId();

            // Update budget spent/committed amounts
            if ($data['status'] === 'approved' || $data['status'] === 'paid') {
                $this->db->execute(
                    "UPDATE budgets SET spent_amount = spent_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    array_merge([$data['amount'], $budgetId], $tid > 1 ? [$tid] : [])
                );
            } else {
                $this->db->execute(
                    "UPDATE budgets SET committed_amount = committed_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    array_merge([$data['amount'], $budgetId], $tid > 1 ? [$tid] : [])
                );
            }

            $this->db->commit();
            return $expenseId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateExpense(int $expenseId, array $data): bool
    {
        $tid = $this->tenantId();
        // Get current expense
        $expense = $this->db->fetchOne(
            "SELECT * FROM budget_expenses WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$expenseId, $tid] : [$expenseId]
        );
        if (!$expense) return false;

        $allowed = ['expense_date', 'vendor', 'description', 'amount', 'bill_number', 'bill_file', 'status'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) return false;

        if (isset($updateData['amount'])) $updateData['amount'] = (float)$updateData['amount'];

        // If status changes, adjust budget amounts
        $oldStatus = $expense['status'];
        $newStatus = $data['status'] ?? $oldStatus;
        $oldAmount = (float)$expense['amount'];
        $newAmount = (float)($data['amount'] ?? $oldAmount);

        $this->db->beginTransaction();
        try {
            $this->db->update('budget_expenses', $updateData, 'id = ?', [$expenseId]);

            // Adjust budget amounts based on status change
            if ($oldStatus !== $newStatus) {
                // Remove old amount
                if ($oldStatus === 'approved' || $oldStatus === 'paid') {
                    $this->db->execute(
                        "UPDATE budgets SET spent_amount = GREATEST(0, spent_amount - ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$oldAmount, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                } else {
                    $this->db->execute(
                        "UPDATE budgets SET committed_amount = GREATEST(0, committed_amount - ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$oldAmount, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                }
                // Add new amount
                if ($newStatus === 'approved' || $newStatus === 'paid') {
                    $this->db->execute(
                        "UPDATE budgets SET spent_amount = spent_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$newAmount, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                } else {
                    $this->db->execute(
                        "UPDATE budgets SET committed_amount = committed_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$newAmount, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                }
            } elseif ($oldAmount !== $newAmount) {
                // Amount changed, adjust
                $diff = $newAmount - $oldAmount;
                if ($newStatus === 'approved' || $newStatus === 'paid') {
                    $this->db->execute(
                        "UPDATE budgets SET spent_amount = spent_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$diff, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                } else {
                    $this->db->execute(
                        "UPDATE budgets SET committed_amount = committed_amount + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                        array_merge([$diff, $expense['budget_id']], $tid > 1 ? [$tid] : [])
                    );
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteExpense(int $expenseId): bool
    {
        $tid = $this->tenantId();
        $expense = $this->db->fetchOne(
            "SELECT * FROM budget_expenses WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$expenseId, $tid] : [$expenseId]
        );
        if (!$expense) return false;

        $this->db->beginTransaction();
        try {
            // Adjust budget amounts
            if ($expense['status'] === 'approved' || $expense['status'] === 'paid') {
                $this->db->execute(
                    "UPDATE budgets SET spent_amount = GREATEST(0, spent_amount - ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    array_merge([$expense['amount'], $expense['budget_id']], $tid > 1 ? [$tid] : [])
                );
            } else {
                $this->db->execute(
                    "UPDATE budgets SET committed_amount = GREATEST(0, committed_amount - ?) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    array_merge([$expense['amount'], $expense['budget_id']], $tid > 1 ? [$tid] : [])
                );
            }
            $this->db->delete('budget_expenses', 'id = ?', [$expenseId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================================
    // VARIANCE ANALYSIS
    // ============================================================
    public function getVarianceReport(?int $budgetId = null, ?string $fiscalYear = null): array
    {
        $tid = $this->tenantId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND b.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($budgetId) {
            $where .= " AND b.id = ?";
            $params[] = $budgetId;
        }
        if ($fiscalYear) {
            $where .= " AND b.fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        $sql = "SELECT 
                    b.id, b.budget_name, b.fiscal_year, b.allocated_amount, b.spent_amount, b.committed_amount, b.remaining_amount,
                    d.name as department_name, d.code as department_code,
                    (b.allocated_amount - b.spent_amount - b.committed_amount) as available_amount,
                    CASE 
                        WHEN b.allocated_amount > 0 THEN ROUND(((b.spent_amount + b.committed_amount) / b.allocated_amount) * 100, 2)
                        ELSE 0 
                    END as utilization_pct
                FROM budgets b
                LEFT JOIN departments d ON d.id = b.department_id
                $where
                ORDER BY b.fiscal_year DESC, b.period_start DESC";

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getDepartmentBudgetSummary(?string $fiscalYear = null): array
    {
        $tid = $this->tenantId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND b.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($fiscalYear) {
            $where .= " AND b.fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        $sql = "SELECT 
                    d.id as department_id,
                    d.name as department_name,
                    d.code as department_code,
                    COUNT(b.id) as budget_count,
                    COALESCE(SUM(b.allocated_amount), 0) as total_allocated,
                    COALESCE(SUM(b.spent_amount), 0) as total_spent,
                    COALESCE(SUM(b.committed_amount), 0) as total_committed,
                    COALESCE(SUM(b.remaining_amount), 0) as total_remaining
                FROM departments d
                LEFT JOIN budgets b ON b.department_id = d.id $where
                WHERE d.status = 'active'
                GROUP BY d.id, d.name, d.code
                ORDER BY d.name";

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    // ============================================================
    // BUDGET PLANNING
    // ============================================================
    public function getPlanning(?string $fiscalYear = null, ?int $departmentId = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT bp.*, d.name as department_name
                FROM budget_planning bp
                LEFT JOIN departments d ON d.id = bp.department_id
                WHERE 1=1" . ($tid > 1 ? " AND bp.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($fiscalYear) {
            $sql .= " AND bp.fiscal_year = ?";
            $params[] = $fiscalYear;
        }
        if ($departmentId) {
            $sql .= " AND bp.department_id = ?";
            $params[] = $departmentId;
        }
        $sql .= " ORDER BY bp.fiscal_year DESC, d.name, bp.line_item";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function savePlanning(array $data): int
    {
        $tid = $this->tenantId();
        $insertData = [
            'tenant_id' => $tid,
            'fiscal_year' => $data['fiscal_year'],
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'line_item' => $data['line_item'],
            'category' => $data['category'] ?? null,
            'q1_amount' => (float)($data['q1_amount'] ?? 0),
            'q2_amount' => (float)($data['q2_amount'] ?? 0),
            'q3_amount' => (float)($data['q3_amount'] ?? 0),
            'q4_amount' => (float)($data['q4_amount'] ?? 0),
            'justification' => $data['justification'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
        $this->db->insert('budget_planning', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function updatePlanning(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $allowed = ['fiscal_year', 'department_id', 'line_item', 'category', 'q1_amount', 'q2_amount', 'q3_amount', 'q4_amount', 'justification', 'status'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = is_numeric($data[$field]) && in_array($field, ['q1_amount','q2_amount','q3_amount','q4_amount']) ? (float)$data[$field] : $data[$field];
            }
        }
        if (empty($updateData)) return false;
        if (isset($updateData['department_id'])) $updateData['department_id'] = (int)$updateData['department_id'];
        return $this->db->update('budget_planning', $updateData, 'id = ?', [$id]);
    }

    public function deletePlanning(int $id): bool
    {
        return $this->db->delete('budget_planning', 'id = ?', [$id]);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    public function getFiscalYears(): array
    {
        return $this->db->fetchAll("SELECT DISTINCT fiscal_year FROM budgets ORDER BY fiscal_year DESC") ?: [];
    }

    public function getDepartments(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll(
            "SELECT id, name, code FROM departments WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name",
            $tid > 1 ? [$tid] : []
        ) ?: [];
    }
}