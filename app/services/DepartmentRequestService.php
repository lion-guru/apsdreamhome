<?php
/**
 * DepartmentRequestService - Cross-Department Request Workflow
 * 
 * Enables any user to submit requests to specific departments.
 * Department heads see requests in their dashboard.
 * Full audit trail with status changes and comments.
 * 
 * Request Types: inquiry, verification, approval, escalation, info_request
 * Departments: SALES, FIN, LEGAL, HR, IT, OPS, MKTG, CONST, LAND, CS, EXEC
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;

class DepartmentRequestService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Submit a new department request
     */
    public function submitRequest(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            $sql = "INSERT INTO department_requests 
                (request_type, department_code, title, description, priority, 
                 requester_id, requester_role, requester_name, related_entity_type, 
                 related_entity_id, due_date, created_at{$tenantCol}) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(){$tenantVal})";

            $stmt = $this->pdo->prepare($sql);
            $params = [
                $data['request_type'] ?? 'inquiry',
                $data['department_code'],
                $data['title'],
                $data['description'] ?? '',
                $data['priority'] ?? 'medium',
                $data['requester_id'],
                $data['requester_role'] ?? '',
                $data['requester_name'] ?? '',
                $data['related_entity_type'] ?? null,
                $data['related_entity_id'] ?? null,
                $data['due_date'] ?? null
            ];
            if ($tid > 1) $params[] = $tid;

            $stmt->execute($params);
            $requestId = $this->pdo->lastInsertId();

            // Log activity
            $this->logActivity($requestId, 'request_submitted', 'Request submitted to ' . $data['department_code']);

            // Send notification to department head
            $this->notifyDepartmentHead($requestId, $data['department_code']);

            return ['success' => true, 'request_id' => $requestId];

        } catch (Exception $e) {
            error_log('DepartmentRequestService::submitRequest error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get requests for a department (for department heads)
     */
    public function getRequestsForDepartment(string $departmentCode, array $filters = []): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";
        $params = [];

        $statusFilter = $filters['status'] ?? null;
        if ($statusFilter) {
            $where .= " AND status = ?";
            $params[] = $statusFilter;
        }

        $priorityFilter = $filters['priority'] ?? null;
        if ($priorityFilter) {
            $where .= " AND priority = ?";
            $params[] = $priorityFilter;
        }

        $sql = "SELECT dr.*, u.name as requester_name_full, 
                assigned.name as assignee_name
                FROM department_requests dr
                LEFT JOIN users u ON u.id = dr.requester_id" . ($tid > 1 ? " AND u.tenant_id = $tid" : "") . "
                LEFT JOIN users assigned ON assigned.id = dr.assigned_to" . ($tid > 1 ? " AND assigned.tenant_id = $tid" : "") . "
                WHERE dr.department_code = ?{$where}
                ORDER BY dr.created_at DESC";

        $params = array_merge([$departmentCode], $params);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get requests submitted by a user
     */
    public function getRequestsByUser(int $userId): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT dr.*, d.name as department_name 
                FROM department_requests dr
                LEFT JOIN departments d ON d.code = dr.department_code" . ($tid > 1 ? " AND d.tenant_id = $tid" : "") . "
                WHERE dr.requester_id = ?{$where}
                ORDER BY dr.created_at DESC";

        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending requests (for admin overview)
     */
    public function getAllPending(int $limit = 50): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT dr.*, d.name as department_name, u.name as requester_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.code = dr.department_code" . ($tid > 1 ? " AND d.tenant_id = $tid" : "") . "
                LEFT JOIN users u ON u.id = dr.requester_id" . ($tid > 1 ? " AND u.tenant_id = $tid" : "") . "
                WHERE dr.status IN ('submitted', 'in_progress', 'review'){$where}
                ORDER BY dr.created_at DESC
                LIMIT ?";

        $params = [$limit];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get request by ID
     */
    public function getRequest(int $requestId): ?array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT dr.*, d.name as department_name, d.head_user_id,
                u.name as requester_name, u.email as requester_email,
                a.name as assignee_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.code = dr.department_code" . ($tid > 1 ? " AND d.tenant_id = $tid" : "") . "
                LEFT JOIN users u ON u.id = dr.requester_id" . ($tid > 1 ? " AND u.tenant_id = $tid" : "") . "
                LEFT JOIN users a ON a.id = dr.assigned_to" . ($tid > 1 ? " AND a.tenant_id = $tid" : "") . "
                WHERE dr.id = ?{$where}";

        $params = [$requestId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update request status
     */
    public function updateStatus(int $requestId, string $status, int $userId, string $comment = ''): array
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = $tid" : "";

            $sql = "UPDATE department_requests 
                    SET status = ?, updated_at = NOW()";
            $params = [$status];

            if ($status === 'completed') {
                $sql .= ", completed_at = NOW()";
            }

            $sql .= " WHERE id = ?{$where}";
            $params[] = $requestId;
            if ($tid > 1) $params[] = $tid;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            if ($comment) {
                $this->addComment($requestId, $userId, $comment, true);
            }

            $this->logActivity($requestId, 'status_changed', "Status changed to: $status");

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Assign request to a user
     */
    public function assign(int $requestId, ?int $userId, ?string $role = null): array
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = $tid" : "";

            $sql = "UPDATE department_requests 
                    SET assigned_to = ?, assigned_to_role = ?, status = 'in_progress', updated_at = NOW()
                    WHERE id = ?{$where}";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $role, $requestId]);

            $this->logActivity($requestId, 'assigned', "Assigned to user ID: " . ($userId ?? 'NULL') . ", role: " . ($role ?? 'NULL'));

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add comment to request
     */
    public function addComment(int $requestId, int $userId, string $comment, bool $isInternal = false): bool
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            // Get user name
            $userName = $this->getUserName($userId);

            $sql = "INSERT INTO department_request_comments 
                    (request_id, commenter_id, commenter_name, comment, is_internal{$tenantCol}) 
                    VALUES (?, ?, ?, ?, ?{$tenantVal})";

            $stmt = $this->pdo->prepare($sql);
            $params = [$requestId, $userId, $userName, $comment, $isInternal ? 1 : 0];
            if ($tid > 1) $params[] = $tid;

            $stmt->execute($params);
            return true;
        } catch (Exception $e) {
            error_log('DepartmentRequestService::addComment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get comments for a request
     */
    public function getComments(int $requestId): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT drc.*, u.name as commenter_name
                FROM department_request_comments drc
                LEFT JOIN users u ON u.id = drc.commenter_id" . ($tid > 1 ? " AND u.tenant_id = $tid" : "") . "
                WHERE drc.request_id = ?{$where}
                ORDER BY drc.created_at ASC";

        $params = [$requestId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get request statistics for a department
     */
    public function getStats(string $departmentCode): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT status, COUNT(*) as count 
                FROM department_requests 
                WHERE department_code = ?{$where}
                GROUP BY status";

        $stmt = $this->pdo->prepare($sql);
        $params = [$departmentCode];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        $stats = [
            'total' => 0,
            'submitted' => 0,
            'in_progress' => 0,
            'review' => 0,
            'approved' => 0,
            'rejected' => 0,
            'completed' => 0
        ];

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }

        return $stats;
    }

    /**
     * Get pending requests count for a department (for sidebar badge)
     */
    public function getPendingCount(string $departmentCode): int
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT COUNT(*) as count 
                FROM department_requests 
                WHERE department_code = ? AND status IN ('submitted', 'in_progress', 'review'){$where}";

        $params = [$departmentCode];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Notify department head about new request
     */
    private function notifyDepartmentHead(int $requestId, string $departmentCode): void
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = $tid" : "";

            // Get department head
            $sql = "SELECT head_user_id FROM departments WHERE code = ?{$where}";
            $stmt = $this->pdo->prepare($sql);
            $params = [$departmentCode];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $dept = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($dept && $dept['head_user_id']) {
                // Create notification
                $notifService = new NotificationService($this->db);
                $notifService->send(
                    (int)$dept['head_user_id'],
                    'in_app',
                    'New Department Request',
                    "A new request has been submitted to your department.",
                    [
                        'event_type' => 'department_request',
                        'request_id' => $requestId,
                        'department_code' => $departmentCode,
                        'url' => '/admin/department-requests/' . $requestId
                    ]
                );
            }
        } catch (Exception $e) {
            error_log('DepartmentRequestService::notifyDepartmentHead error: ' . $e->getMessage());
        }
    }

    /**
     * Get user name by ID
     */
    private function getUserName(int $userId): string
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = $tid" : "";

            $sql = "SELECT name FROM users WHERE id = ?{$where}";
            $stmt = $this->pdo->prepare($sql);
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user['name'] ?? 'Unknown User';
        } catch (Exception $e) {
            return 'Unknown User';
        }
    }

    /**
     * Log activity for audit trail
     */
    private function logActivity(int $requestId, string $action, string $description): void
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            $sql = "INSERT INTO user_activity_logs_unified 
                    (user_id, action, context, ip_address, user_agent{$tenantCol}) 
                    VALUES (?, ?, ?, ?, ?{$tenantVal})";

            $context = json_encode([
                'request_id' => $requestId,
                'description' => $description
            ]);

            $stmt = $this->pdo->prepare($sql);
            $params = [
                $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0,
                'department_request_' . $action,
                $context,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            if ($tid > 1) $params[] = $tid;

            $stmt->execute($params);
        } catch (Exception $e) {
            error_log('DepartmentRequestService::logActivity error: ' . $e->getMessage());
        }
    }

    /**
     * Get department code for a user based on their role
     */
    public function getDepartmentForUser(string $role): ?string
    {
        $map = [
            'admin' => 'EXEC',
            'super_admin' => 'EXEC',
            'sales_director' => 'SALES',
            'finance_manager' => 'FIN',
            'finance_head' => 'FIN',
            'legal_head' => 'LEGAL',
            'legal_advisor' => 'LEGAL',
            'hr_manager' => 'HR',
            'hr_head' => 'HR',
            'it_manager' => 'IT',
            'cto' => 'IT',
            'construction_director' => 'CONST',
            'operations_head' => 'OPS',
            'operations_director' => 'OPS',
            'marketing_director' => 'MKTG',
            'cmo' => 'MKTG',
            'land_director' => 'LAND',
            'employee' => 'CS',
            'telecaller' => 'CS',
            'customer_service' => 'CS',
            'agent' => 'SALES',
            'associate' => 'SALES',
            'customer' => 'CS',
            'user' => 'CS',
            'farmer' => 'LAND',
        ];

        return $map[$role] ?? null;
    }

    /**
     * Get all departments with pending request counts
     */
    public function getAllDepartmentsWithCounts(): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = $tid" : "";

        $sql = "SELECT d.code, d.name, d.head_user_id,
                COALESCE(dr.pending_count, 0) as pending_count
                FROM departments d
                LEFT JOIN (
                    SELECT department_code, COUNT(*) as pending_count
                    FROM department_requests
                    WHERE status IN ('submitted', 'in_progress', 'review'){$where}
                    GROUP BY department_code
                ) dr ON dr.department_code = d.code
                WHERE d.status = 'active'{$where}
                ORDER BY d.name";

        $params = [];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}