<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class ExpenseReimbursementService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== EXPENSE CATEGORIES ====================

    public function getCategories(array $filters = []): array
    {
        $where = ['ec.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['is_active'])) {
            $where[] = 'ec.is_active = ?';
            $params[] = (int)$filters['is_active'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(ec.name LIKE ? OR ec.code LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT ec.* FROM expense_categories ec $whereSql ORDER BY ec.sort_order ASC, ec.name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategory(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM expense_categories WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createCategory(array $data): array
    {
        $required = ['name', 'code'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['name', 'code', 'description', 'max_amount_per_claim', 'requires_receipt', 'gst_applicable', 'gst_rate', 'is_active', 'sort_order', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['name'], $data['code'], $data['description'] ?? null,
            $data['max_amount_per_claim'] ?? 0,
            $data['requires_receipt'] ?? true,
            $data['gst_applicable'] ?? false,
            $data['gst_rate'] ?? 0,
            $data['is_active'] ?? true,
            $data['sort_order'] ?? 0,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO expense_categories (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Category created'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Category code already exists'];
            }
            throw $e;
        }
    }

    public function updateCategory(int $id, array $data): array
    {
        $allowed = ['name', 'description', 'max_amount_per_claim', 'requires_receipt', 'gst_applicable', 'gst_rate', 'is_active', 'sort_order'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?"; $params[] = $data[$field];
            }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];
        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE expense_categories SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Updated' : 'Not found'];
    }

    // ==================== EXPENSE CLAIMS ====================

    public function getClaims(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ['ec.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['employee_id'])) { $where[] = 'ec.employee_id = ?'; $params[] = $filters['employee_id']; }
        if (!empty($filters['category_id'])) { $where[] = 'ec.category_id = ?'; $params[] = $filters['category_id']; }
        if (!empty($filters['status'])) { $where[] = 'ec.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['from_date'])) { $where[] = 'ec.claim_date >= ?'; $params[] = $filters['from_date']; }
        if (!empty($filters['to_date'])) { $where[] = 'ec.claim_date <= ?'; $params[] = $filters['to_date']; }
        if (!empty($filters['search'])) { $where[] = '(ec.claim_number LIKE ? OR ec.description LIKE ?)'; $s = '%' . $filters['search'] . '%'; $params[] = $s; $params[] = $s; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT ec.*, ec2.name as category_name, u.name as employee_name, u.email as employee_email
            FROM expense_claims ec
            JOIN expense_categories ec2 ON ec.category_id = ec2.id
            JOIN employees e ON ec.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            $whereSql
            ORDER BY ec.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit; $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClaim(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT ec.*, ec2.name as category_name, u.name as employee_name, u.email as employee_email
            FROM expense_claims ec
            JOIN expense_categories ec2 ON ec.category_id = ec2.id
            JOIN employees e ON ec.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE ec.id = ? AND ec.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        $claim = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($claim) {
            $claim['items'] = $this->getClaimItems($id);
            $claim['workflow'] = $this->getWorkflow($id);
        }
        return $claim ?: null;
    }

    public function getClaimItems(int $claimId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM expense_claim_items WHERE claim_id = ? AND tenant_id = ? ORDER BY expense_date ASC');
        $stmt->execute([$claimId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkflow(int $claimId): array
    {
        $stmt = $this->conn->prepare("
            SELECT eaw.*, u.name as approver_name, u.email as approver_email, u2.name as delegated_name
            FROM expense_approval_workflow eaw
            JOIN users u ON eaw.approver_id = u.id
            LEFT JOIN users u2 ON eaw.delegated_to = u2.id
            WHERE eaw.claim_id = ? AND eaw.tenant_id = ?
            ORDER BY eaw.level ASC
        ");
        $stmt->execute([$claimId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeClaims(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ec.*, ec2.name as category_name
            FROM expense_claims ec
            JOIN expense_categories ec2 ON ec.category_id = ec2.id
            WHERE ec.employee_id = ? AND ec.tenant_id = ?
            ORDER BY ec.created_at DESC
        ");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingApprovals(int $approverId): array
    {
        $stmt = $this->conn->prepare("
            SELECT eaw.*, ec.claim_number, ec.total_amount, ec.claim_date, ec.description,
                   u.name as employee_name, ec2.name as category_name
            FROM expense_approval_workflow eaw
            JOIN expense_claims ec ON eaw.claim_id = ec.id
            JOIN expense_categories ec2 ON ec.category_id = ec2.id
            JOIN employees e ON ec.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE eaw.approver_id = ? AND eaw.status = 'pending' AND eaw.tenant_id = ?
            ORDER BY eaw.level ASC, ec.created_at ASC
        ");
        $stmt->execute([$approverId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function submitClaim(int $employeeId, array $data): array
    {
        $required = ['category_id', 'claim_date', 'items'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }
        if (!is_array($data['items']) || empty($data['items'])) throw new InvalidArgumentException('At least one item required');

        $this->conn->beginTransaction();
        try {
            // Generate claim number
            $claimNumber = 'EXP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Calculate totals
            $totalAmount = 0; $totalGst = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['amount'];
                $totalGst += $item['gst_amount'] ?? 0;
            }

            // Get category for GST rate
            $category = $this->getCategory($data['category_id']);
            $gstRate = $category['gst_rate'] ?? 0;

            // Create claim
            $columns = array_merge(
                ['claim_number', 'employee_id', 'category_id', 'total_amount', 'gst_amount', 'claim_date', 'description', 'status', 'submitted_at', 'created_at'],
                array_keys($this->tenantInsertData())
            );
            $values = array_merge([
                $claimNumber, $employeeId, $data['category_id'], $totalAmount, $totalGst,
                $data['claim_date'], $data['description'] ?? null, 'submitted', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
            ], array_values($this->tenantInsertData()));

            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            $sql = "INSERT INTO expense_claims (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            $claimId = (int)$this->conn->lastInsertId();

            // Insert items
            foreach ($data['items'] as $item) {
                $itemGst = $item['gst_amount'] ?? ($item['amount'] * ($item['gst_rate'] ?? $gstRate) / 100);
                $itemColumns = array_merge(
                    ['claim_id', 'expense_date', 'description', 'amount', 'gst_amount', 'gst_rate', 'hsn_sac_code', 'receipt_path', 'is_billable', 'project_code', 'created_at'],
                    array_keys($this->tenantInsertData())
                );
                $itemValues = array_merge([
                    $claimId, $item['expense_date'], $item['description'], $item['amount'],
                    $itemGst, $item['gst_rate'] ?? $gstRate, $item['hsn_sac_code'] ?? null,
                    $item['receipt_path'] ?? null, $item['is_billable'] ?? false, $item['project_code'] ?? null,
                    date('Y-m-d H:i:s')
                ], array_values($this->tenantInsertData()));
                $itemPlaceholders = str_repeat('?,', count($itemColumns) - 1) . '?';
                $itemSql = "INSERT INTO expense_claim_items (" . implode(', ', $itemColumns) . ") VALUES ($itemPlaceholders)";
                $this->conn->execute($itemSql, $itemValues);
            }

            // Create approval workflow (simple 1-level: manager)
            $managerId = $this->getEmployeeManager($employeeId);
            if ($managerId) {
                $wfColumns = array_merge(['claim_id', 'level', 'approver_id', 'approver_role', 'status', 'created_at'], array_keys($this->tenantInsertData()));
                $wfValues = array_merge([$claimId, 1, $managerId, 'manager', 'pending', date('Y-m-d H:i:s')], array_values($this->tenantInsertData()));
                $wfPlaceholders = str_repeat('?,', count($wfColumns) - 1) . '?';
                $wfSql = "INSERT INTO expense_approval_workflow (" . implode(', ', $wfColumns) . ") VALUES ($wfPlaceholders)";
                $this->conn->execute($wfSql, $wfValues);
            }

            $this->conn->commit();
            return ['success' => true, 'id' => $claimId, 'claim_number' => $claimNumber, 'message' => 'Claim submitted'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function processApproval(int $claimId, int $approverId, string $action, string $comments = ''): array
    {
        $this->conn->beginTransaction();
        try {
            // Get current workflow step
            $wf = $this->conn->fetch("SELECT * FROM expense_approval_workflow WHERE claim_id = ? AND approver_id = ? AND status = 'pending' AND tenant_id = ? ORDER BY level LIMIT 1", [$claimId, $approverId, $this->tenantId()]);
            if (!$wf) return ['success' => false, 'message' => 'No pending approval for you'];

            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            $this->conn->execute("UPDATE expense_approval_workflow SET status = ?, comments = ?, action_at = NOW() WHERE id = ? AND tenant_id = ?", [$newStatus, $comments, $wf['id'], $this->tenantId()]);

            // Check if all workflows at this level are done
            $pendingAtLevel = $this->conn->fetch("SELECT COUNT(*) as c FROM expense_approval_workflow WHERE claim_id = ? AND level = ? AND status = 'pending' AND tenant_id = ?", [$claimId, $wf['level'], $this->tenantId()]);
            $claim = $this->conn->fetch("SELECT * FROM expense_claims WHERE id = ? AND tenant_id = ?", [$claimId, $this->tenantId()]);

            if ($action === 'reject') {
                $this->conn->execute("UPDATE expense_claims SET status = 'rejected', rejection_reason = ?, rejected_at = NOW(), rejected_by = ? WHERE id = ? AND tenant_id = ?", [$comments, $approverId, $claimId, $this->tenantId()]);
            } elseif ($pendingAtLevel['c'] == 0) {
                // Check next level
                $nextLevel = $this->conn->fetch("SELECT * FROM expense_approval_workflow WHERE claim_id = ? AND level > ? AND tenant_id = ? ORDER BY level LIMIT 1", [$claimId, $wf['level'], $this->tenantId()]);
                if ($nextLevel) {
                    $this->conn->execute("UPDATE expense_approval_workflow SET status = 'pending' WHERE id = ? AND tenant_id = ?", [$nextLevel['id'], $this->tenantId()]);
                } else {
                    // All approved
                    $this->conn->execute("UPDATE expense_claims SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ? AND tenant_id = ?", [$approverId, $claimId, $this->tenantId()]);
                }
            }

            $this->conn->commit();
            return ['success' => true, 'message' => ucfirst($action) . 'd successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function getEmployeeManager(int $employeeId): ?int
    {
        // Try to get manager from org chart or employees table
        $mgr = $this->conn->fetch("SELECT manager_id FROM employees WHERE id = ? AND manager_id IS NOT NULL", [$employeeId]);
        if ($mgr) return $mgr['manager_id'];

        // Fallback: org chart parent
        $node = $this->conn->fetch("SELECT parent_id FROM org_chart_nodes WHERE employee_id = ?", [$employeeId]);
        if ($node && $node['parent_id']) {
            $parent = $this->conn->fetch("SELECT employee_id FROM org_chart_nodes WHERE id = ?", [$node['parent_id']]);
            if ($parent) return $parent['employee_id'];
        }
        return null;
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM expense_claims WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats['by_status'][$row['status']] = $row['c'];
        }
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM expense_claims WHERE tenant_id = ?');
        $stmt->execute([$this->tenantId()]);
        $stats['total'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT SUM(total_amount) as total FROM expense_claims WHERE tenant_id = ? AND status IN ("approved","paid")');
        $stmt->execute([$this->tenantId()]);
        $stats['total_approved_amount'] = $stmt->fetchColumn() ?? 0;

        return $stats;
    }
}