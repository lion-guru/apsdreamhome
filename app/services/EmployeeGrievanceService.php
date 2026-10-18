<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class EmployeeGrievanceService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function submitGrievance(int $employeeId, array $data): array
    {
        $category = $data['category'] ?? 'other';
        $allowedCategories = ['harassment', 'discrimination', 'workplace_safety', 'payroll', 'leave', 'manager', 'colleague', 'policy', 'facilities', 'other'];
        if (!in_array($category, $allowedCategories)) {
            return ['success' => false, 'message' => 'Invalid category'];
        }

        $subject = trim($data['subject'] ?? '');
        if (empty($subject)) {
            return ['success' => false, 'message' => 'Subject is required'];
        }

        $description = trim($data['description'] ?? '');
        if (empty($description)) {
            return ['success' => false, 'message' => 'Description is required'];
        }

        $priority = $data['priority'] ?? 'normal';
        $allowedPriorities = ['low', 'normal', 'high', 'urgent', 'critical'];
        if (!in_array($priority, $allowedPriorities)) $priority = 'normal';

        $isAnonymous = isset($data['is_anonymous']) ? (int)$data['is_anonymous'] : 0;

        $slaHours = 48;
        switch ($priority) {
            case 'critical': $slaHours = 2; break;
            case 'urgent': $slaHours = 4; break;
            case 'high': $slaHours = 8; break;
            case 'normal': $slaHours = 24; break;
            case 'low': $slaHours = 48; break;
        }

        $grievanceNumber = $this->generateGrievanceNumber();

        $this->execute("
            INSERT INTO employee_grievances
            (tenant_id, grievance_number, employee_id, category, sub_category, priority, subject, description, status, sla_hours, is_anonymous, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', ?, ?, NOW())",
            [$this->tenantId(), $grievanceNumber, $employeeId, $category, $data['sub_category'] ?? null, $priority, $subject, $description, $slaHours, $isAnonymous]);

        $grievanceId = $this->db->lastInsertId();

        $this->notifyAssignees($grievanceId, $category);

        return [
            'success' => true,
            'message' => 'Grievance submitted successfully',
            'grievance_id' => $grievanceId,
            'grievance_number' => $grievanceNumber
        ];
    }

    private function generateGrievanceNumber(): string
    {
        $prefix = 'GRV';
        $year = date('Y');
        $month = date('m');
        $count = $this->fetchOne("
            SELECT COUNT(*) as cnt FROM employee_grievances 
            WHERE tenant_id = ? AND grievance_number LIKE ?
        ", [$this->tenantId(), "{$prefix}-{$year}{$month}%"])['cnt'] ?? 0;
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $count + 1);
    }

    private function notifyAssignees(int $grievanceId, string $category): void
    {
        error_log("Grievance $grievanceId submitted for category $category - notification pending");
    }

    public function getGrievance(int $grievanceId): ?array
    {
        return $this->fetchOne("
            SELECT eg.*, u.name as employee_name, e.employee_code, e.department,
                   a.name as assignee_name
            FROM employee_grievances eg
            JOIN employees e ON eg.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN users a ON eg.assigned_to = a.id
            WHERE eg.id = ? AND eg.tenant_id = 1", [$grievanceId]);
    }

    public function getEmployeeGrievances(int $employeeId, string $status = null): array
    {
        $query = "SELECT * FROM employee_grievances WHERE tenant_id = 1 AND employee_id = ?";
        $params = [$employeeId];
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        $query .= " ORDER BY created_at DESC";
        return $this->fetchAll($query, $params);
    }

    public function getAllGrievances(array $filters = []): array
    {
        $query = "
            SELECT eg.*, u.name as employee_name, e.employee_code, e.department,
                   a.name as assignee_name
            FROM employee_grievances eg
            JOIN employees e ON eg.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN users a ON eg.assigned_to = a.id
            WHERE eg.tenant_id = 1";
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query .= " AND eg.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query .= " AND eg.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query .= " AND eg.priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['assignee']) && $filters['assignee'] !== 'all') {
            $query .= " AND eg.assigned_to = ?";
            $params[] = $filters['assignee'];
        }

        $query .= " ORDER BY eg.created_at DESC";
        return $this->fetchAll($query, $params);
    }

    public function updateStatus(int $grievanceId, string $status, int $userId): array
    {
        $allowed = ['open', 'under_review', 'investigation', 'mediation', 'resolved', 'closed', 'reopened'];
        if (!in_array($status, $allowed)) return ['success' => false, 'message' => 'Invalid status'];

        $grievance = $this->getGrievance($grievanceId);
        if (!$grievance) return ['success' => false, 'message' => 'Grievance not found'];

        $updates = ['status = ?'];
        $params = [$status];

        if (in_array($status, ['resolved', 'closed'])) {
            $updates[] = 'resolved_at = NOW()';
            $updates[] = 'resolved_by = ?';
            $params[] = $userId;
        }

        $params[] = $grievanceId;
        $this->execute(
            "UPDATE employee_grievances SET " . implode(', ', $updates) . " WHERE id = ? AND tenant_id = 1",
            $params
        );

        return ['success' => true, 'message' => 'Status updated'];
    }

    public function assignGrievance(int $grievanceId, int $assigneeId, int $assignedBy): array
    {
        $assignee = $this->fetchOne("SELECT id, name FROM users WHERE id = ? AND status = 'active'", [$assigneeId]);
        if (!$assignee) return ['success' => false, 'message' => 'Assignee not found or inactive'];

        $this->execute(
            "UPDATE employee_grievances SET assigned_to = ?, assigned_at = NOW() WHERE id = ? AND tenant_id = 1",
            [$assigneeId, $grievanceId]
        );

        try {
            $notif = new \App\Services\NotificationService($this->db);
            $notif->notify(
                'grievance_assigned',
                "Grievance #{$grievanceId} has been assigned to you for investigation/resolution.",
                $assigneeId,
                "/admin/grievances/{$grievanceId}",
                'New Grievance Assigned'
            );
        } catch (\Throwable $e) {
            error_log("Grievance assign notification error: " . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Grievance assigned'];
    }

    public function getStaffList(): array
    {
        return $this->fetchAll("
            SELECT u.id, u.name, u.role, u.email, e.department, e.designation
            FROM users u
            LEFT JOIN employees e ON e.user_id = u.id
            WHERE u.status = 'active'
              AND u.role IN ('admin', 'super_admin', 'manager', 'employee', 'hr_manager', 'director', 'operations_manager', 'ceo', 'coo')
            ORDER BY u.name ASC
        ");
    }

    public function addComment(int $grievanceId, int $employeeId, string $comment, bool $isInternal = false): array
    {
        $grievance = $this->getGrievance($grievanceId);
        if (!$grievance) return ['success' => false, 'message' => 'Grievance not found'];

        $comment = trim($comment);
        if (empty($comment)) return ['success' => false, 'message' => 'Comment cannot be empty'];

        $this->execute("
            INSERT INTO grievance_comments
            (tenant_id, grievance_id, employee_id, comment, is_internal, created_at)
            VALUES (?, ?, ?, ?, NOW())",
            [$this->tenantId(), $grievanceId, $employeeId, $comment, $isInternal ? 1 : 0]);

        return ['success' => true, 'message' => 'Comment added'];
    }

    public function getComments(int $grievanceId): array
    {
        return $this->fetchAll("
            SELECT gc.*, u.name as author_name, u.role as author_role
            FROM grievance_comments gc
            LEFT JOIN users u ON gc.employee_id = u.id
            WHERE gc.tenant_id = 1 AND gc.grievance_id = ?
            ORDER BY gc.created_at ASC", [$grievanceId]);
    }

    public function getStatistics(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_grievances WHERE tenant_id = 1")['cnt'] ?? 0;
        $open = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_grievances WHERE tenant_id = 1 AND status = 'open'")['cnt'] ?? 0;
        $inProgress = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_grievances WHERE tenant_id = 1 AND status IN ('under_review','investigation','mediation')")['cnt'] ?? 0;
        $resolved = $this->fetchOne("SELECT COUNT(*) as cnt FROM employee_grievances WHERE tenant_id = 1 AND status IN ('resolved','closed')")['cnt'] ?? 0;

        $byCategory = $this->fetchAll("
            SELECT category, COUNT(*) as cnt
            FROM employee_grievances
            WHERE tenant_id = 1
            GROUP BY category");

        $byPriority = $this->fetchAll("
            SELECT priority, COUNT(*) as cnt
            FROM employee_grievances
            WHERE tenant_id = 1
            GROUP BY priority");

        $avgResolution = $this->fetchOne("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
            FROM employee_grievances
            WHERE tenant_id = 1 AND status IN ('resolved','closed') AND resolved_at IS NOT NULL");

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'by_category' => $byCategory,
            'by_priority' => $byPriority,
            'avg_resolution_hours' => round($avgResolution['avg_hours'] ?? 0, 1)
        ];
    }

    public function getSLABreaches(): array
    {
        return $this->fetchAll("
            SELECT eg.*, u.name as employee_name
            FROM employee_grievances eg
            JOIN employees e ON eg.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE eg.tenant_id = 1
              AND eg.status IN ('open','under_review','investigation','mediation')
              AND eg.sla_breached = 0
              AND TIMESTAMPDIFF(HOUR, eg.created_at, NOW()) > eg.sla_hours");
    }

    public function checkAndMarkSLABreaches(): int
    {
        $breaches = $this->getSLABreaches();
        $count = 0;
        foreach ($breaches as $b) {
            $this->execute("UPDATE employee_grievances SET sla_breached = 1 WHERE id = ? AND sla_breached = 0", [$b['id']]);
            $count++;
        }
        return $count;
    }

    /**
     * Close grievance with employee rating
     */
    public function closeWithRating(int $grievanceId, int $employeeId, int $rating, string $feedback = ''): array
    {
        $grievance = $this->fetchOne("SELECT * FROM employee_grievances WHERE id = ? AND employee_id = ?", [$grievanceId, $employeeId]);
        if (!$grievance) {
            return ['success' => false, 'message' => 'Grievance not found or access denied'];
        }

        if (!in_array($grievance['status'], ['resolved', 'under_review', 'investigation', 'mediation'])) {
            return ['success' => false, 'message' => 'Grievance cannot be closed in current status'];
        }

        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }

        try {
            $this->execute("
                UPDATE employee_grievances 
                SET status = 'closed', 
                    employee_rating = ?, 
                    employee_feedback = ?, 
                    closed_at = NOW(),
                    closed_by = ?
                WHERE id = ?", 
                [$rating, $feedback, $employeeId, $grievanceId]
            );

            // Log activity
            $this->logActivity($employeeId, 'grievance_closed', [
                'grievance_id' => $grievanceId,
                'grievance_number' => $grievance['grievance_number'],
                'rating' => $rating
            ]);

            return ['success' => true, 'message' => 'Grievance closed successfully with your rating'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to close grievance: ' . $e->getMessage()];
        }
    }

    private function logActivity(int $userId, string $action, array $data = []): void
    {
        try {
            $user = $this->fetchOne("SELECT email FROM users WHERE id = ?", [$userId]);
            $email = $user['email'] ?? 'employee@apsdreamhome.com';
            $details = json_encode(array_merge(['action' => $action], $data));
            $this->execute(
                "INSERT INTO employee_activity_logs_unified (email, success, details, created_at) VALUES (?, 1, ?, NOW())",
                [$email, $details]
            );
        } catch (\Throwable $e) {
            error_log("Grievance logActivity failed: " . $e->getMessage());
        }
    }

    public function getCategories(): array
    {
        return [
            'harassment' => 'Harassment',
            'discrimination' => 'Discrimination',
            'workplace_safety' => 'Workplace Safety',
            'payroll' => 'Payroll Issues',
            'leave' => 'Leave Related',
            'manager' => 'Manager Conflict',
            'colleague' => 'Colleague Conflict',
            'policy' => 'Policy Violation',
            'facilities' => 'Facilities',
            'other' => 'Other'
        ];
    }

    public function getPriorities(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
            'critical' => 'Critical'
        ];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('EmployeeGrievanceService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeGrievanceService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return (int)$this->db->getConnection()->lastInsertId();
        } catch (\Throwable $e) {
            error_log('EmployeeGrievanceService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}