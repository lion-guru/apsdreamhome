<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class EmployeeAssetService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get employee's asset assignments
     */
    public function getEmployeeAssets(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT * FROM employee_asset_assignments
            WHERE employee_id = ?{$this->tenantSql()}
            ORDER BY assigned_date DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get active asset assignments
     */
    public function getActiveAssets(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT * FROM employee_asset_assignments
            WHERE employee_id = ? AND status = 'active'{$this->tenantSql()}
            ORDER BY assigned_date DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Assign asset to employee (admin/HR)
     */
    public function assignAsset(int $employeeId, array $data): array
    {
        // Validate employee
        $emp = $this->fetchOne("SELECT id, name FROM employees WHERE id = ? AND status = 'active'", [$employeeId]);
        if (!$emp) return ['success' => false, 'message' => 'Employee not found or inactive'];

        // Check if asset tag already assigned
        if (!empty($data['asset_tag'])) {
            $existing = $this->fetchOne("
                SELECT id FROM employee_asset_assignments
                WHERE asset_tag = ? AND status = 'active'{$this->tenantSql()}
            ", [$data['asset_tag']]);
            if ($existing) return ['success' => false, 'message' => 'Asset tag already assigned'];
        }

        $this->execute("
            INSERT INTO employee_asset_assignments
            (tenant_id, employee_id, asset_type, asset_name, asset_serial, asset_tag, assigned_date,
             condition_issued, value, condition_notes, issued_by, received_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ", [
            $this->tenantId(), $employeeId,
            $data['asset_type'] ?? 'other',
            $data['asset_name'] ?? '',
            $data['asset_serial'] ?? null,
            $data['asset_tag'] ?? null,
            $data['assigned_date'] ?? date('Y-m-d'),
            $data['condition_issued'] ?? 'good',
            $data['value'] ?? 0,
            $data['condition_notes'] ?? null,
            $data['issued_by'] ?? null,
            $data['received_by'] ?? $employeeId
        ]);

        return ['success' => true, 'message' => 'Asset assigned successfully', 'id' => $this->db->lastInsertId()];
    }

    /**
     * Return asset
     */
    public function returnAsset(int $assignmentId, int $employeeId, array $data): array
    {
        $assignment = $this->fetchOne("SELECT * FROM employee_asset_assignments WHERE id = ? AND employee_id = ?", [$assignmentId, $employeeId]);
        if (!$assignment) return ['success' => false, 'message' => 'Assignment not found'];
        if ($assignment['status'] !== 'active') return ['success' => false, 'message' => 'Asset not currently assigned'];

        $this->execute("
            UPDATE employee_asset_assignments
            SET status = 'returned', returned_date = CURDATE(), condition_returned = ?, condition_notes = CONCAT(IFNULL(condition_notes,''), '\nReturn: ', ?), returned_to = ?
            WHERE id = ?
        ", [$data['condition_returned'] ?? 'good', $data['return_notes'] ?? '', $data['returned_to'] ?? null, $assignmentId]);

        return ['success' => true, 'message' => 'Asset returned successfully'];
    }

    /**
     * Admin: Return asset (force)
     */
    public function adminReturnAsset(int $assignmentId, array $data): array
    {
        $assignment = $this->fetchOne("SELECT * FROM employee_asset_assignments WHERE id = ?", [$assignmentId]);
        if (!$assignment) return ['success' => false, 'message' => 'Assignment not found'];
        if ($assignment['status'] !== 'active') return ['success' => false, 'message' => 'Asset not currently assigned'];

        $this->execute("
            UPDATE employee_asset_assignments
            SET status = 'returned', returned_date = CURDATE(), condition_returned = ?, condition_notes = CONCAT(IFNULL(condition_notes,''), '\nAdmin Return: ', ?), returned_to = ?, status = 'returned'
            WHERE id = ?
        ", [$data['condition_returned'] ?? 'good', $data['return_notes'] ?? '', $data['returned_to'] ?? null, $assignmentId]);

        return ['success' => true, 'message' => 'Asset returned by admin'];
    }

    /**
     * Transfer asset to another employee
     */
    public function transferAsset(int $assignmentId, int $newEmployeeId, int $transferredBy): array
    {
        $assignment = $this->fetchOne("SELECT * FROM employee_asset_assignments WHERE id = ?", [$assignmentId]);
        if (!$assignment) return ['success' => false, 'message' => 'Assignment not found'];
        if ($assignment['status'] !== 'active') return ['success' => false, 'message' => 'Asset not currently assigned'];

        // End current assignment
        $this->execute("
            UPDATE employee_asset_assignments
            SET status = 'transferred', returned_date = CURDATE(), condition_returned = 'transferred', returned_to = ?
            WHERE id = ?
        ", [$newEmployeeId, $assignmentId]);

        // Create new assignment
        $this->execute("
            INSERT INTO employee_asset_assignments
            (tenant_id, employee_id, asset_type, asset_name, asset_serial, asset_tag, assigned_date,
             condition_issued, value, condition_notes, issued_by, received_by, status)
            VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'active')
        ", [
            $this->tenantId(), $newEmployeeId,
            $assignment['asset_type'], $assignment['asset_name'],
            $assignment['asset_serial'], $assignment['asset_tag'],
            $assignment['condition_issued'],
            $assignment['value'],
            'Transferred from employee ' . $assignment['employee_id'] . ' on ' . date('Y-m-d'),
            $transferredBy, $newEmployeeId, 'active'
        ]);

        return ['success' => true, 'message' => 'Asset transferred successfully', 'new_assignment_id' => $this->db->lastInsertId()];
    }

    /**
     * Get all active assets (admin)
     */
    public function getAllActiveAssets(): array
    {
        return $this->db->fetchAll("
            SELECT eaa.*, e.name as employee_name, e.employee_code, e.department
            FROM employee_asset_assignments eaa
            JOIN employees e ON eaa.employee_id = e.id
            WHERE eaa.status = 'active' AND eaa.tenant_id = 1
            ORDER BY eaa.assigned_date DESC
        ");
    }

    /**
     * Get assets by type
     */
    public function getAssetsByType(string $type): array
    {
        return $this->db->fetchAll("
            SELECT eaa.*, e.name as employee_name, e.employee_code, e.department
            FROM employee_asset_assignments eaa
            JOIN employees e ON eaa.employee_id = e.id
            WHERE eaa.asset_type = ? AND eaa.status = 'active' AND eaa.tenant_id = 1
            ORDER BY eaa.assigned_date DESC
        ", [$type]);
    }

    /**
     * Get asset by tag
     */
    public function getAssetByTag(string $tag): ?array
    {
        return $this->db->fetchOne("
            SELECT eaa.*, e.name as employee_name, e.employee_code, e.department
            FROM employee_asset_assignments eaa
            JOIN employees e ON eaa.employee_id = e.id
            WHERE eaa.asset_tag = ? AND eaa.status = 'active' AND eaa.tenant_id = 1
        ", [$tag]);
    }

    /**
     * Report lost/damaged asset
     */
    public function reportIssue(int $assignmentId, int $employeeId, string $issueType, string $notes): array
    {
        $assignment = $this->fetchOne("SELECT * FROM employee_asset_assignments WHERE id = ? AND employee_id = ?", [$assignmentId, $employeeId]);
        if (!$assignment) return ['success' => false, 'message' => 'Assignment not found'];

        $newStatus = $issueType === 'lost' ? 'lost' : 'damaged';
        $this->execute("
            UPDATE employee_asset_assignments
            SET status = ?, condition_notes = CONCAT(IFNULL(condition_notes,''), '\n', ?, ': ', ?)
            WHERE id = ?
        ", [$newStatus, ucfirst($issueType), $notes, $assignmentId]);

        return ['success' => true, 'message' => ucfirst($issueType) . ' reported'];
    }

    /**
     * Get asset summary for employee
     */
    public function getAssetSummary(int $employeeId): array
    {
        $assets = $this->getEmployeeAssets($employeeId);
        $summary = [
            'total' => count($assets),
            'active' => 0,
            'returned' => 0,
            'lost' => 0,
            'damaged' => 0,
            'by_type' => [],
            'total_value' => 0
        ];

        foreach ($assets as $a) {
            $summary['total_value'] += (float)$a['value'];
            $summary[$a['status']] = ($summary[$a['status']] ?? 0) + 1;
            $type = $a['asset_type'];
            $summary['by_type'][$type] = ($summary['by_type'][$type] ?? 0) + 1;
        }

        return $summary;
    }

    /**
     * Admin: Get all asset assignments with filters
     */
    public function getAllAssignments(array $filters = []): array
    {
        $query = "
            SELECT eaa.*, e.name as employee_name, e.employee_code, e.department, e.designation
            FROM employee_asset_assignments eaa
            JOIN employees e ON eaa.employee_id = e.id
            WHERE eaa.tenant_id = 1
        ";
        $params = [1];

        if (!empty($filters['status'])) {
            $query .= " AND eaa.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['asset_type'])) {
            $query .= " AND eaa.asset_type = ?";
            $params[] = $filters['asset_type'];
        }
        if (!empty($filters['employee_id'])) {
            $query .= " AND eaa.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['department'])) {
            $query .= " AND e.department = ?";
            $params[] = $filters['department'];
        }

        $query .= " ORDER BY eaa.assigned_date DESC";
        return $this->fetchAll($query, $params);
    }

    /**
     * Get asset statistics
     */
    public function getAssetStatistics(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_asset_assignments WHERE tenant_id = 1")['cnt'] ?? 0;
        $active = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_asset_assignments WHERE tenant_id = 1 AND status = 'active'")['cnt'] ?? 0;
        $returned = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_asset_assignments WHERE tenant_id = 1 AND status = 'returned'")['cnt'] ?? 0;
        $lost = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_asset_assignments WHERE tenant_id = 1 AND status = 'lost'")['cnt'] ?? 0;
        $damaged = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_asset_assignments WHERE tenant_id = 1 AND status = 'damaged'")['cnt'] ?? 0;

        $byType = $this->fetchAll("
            SELECT asset_type, COUNT(*) as cnt, SUM(value) as total_value
            FROM employee_asset_assignments
            WHERE tenant_id = 1 AND status = 'active'
            GROUP BY asset_type
        ");

        return [
            'total' => $total,
            'active' => $active,
            'returned' => $returned,
            'lost' => $lost,
            'damaged' => $damaged,
            'by_type' => $byType
        ];
    }
}