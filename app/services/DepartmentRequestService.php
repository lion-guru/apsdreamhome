<?php
/**
 * DepartmentRequestService - Cross-Department Request Workflow
 *
 * Enables any user to submit requests to specific departments.
 * Department heads see requests in their dashboard.
 * Full audit trail with status changes and comments.
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;
use PDO;

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

    public function submitRequest(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            $sql = "INSERT INTO department_requests
                (department_id, title, description, priority,
                 requested_by, requested_by_role, requester_name,
                 created_at{$tenantCol})
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(){$tenantVal})";

            $stmt = $this->pdo->prepare($sql);
            $params = [
                $data['department_id'] ?? 1,
                $data['title'],
                $data['description'] ?? '',
                $data['priority'] ?? 'medium',
                $data['requester_id'] ?? $data['requested_by'] ?? 0,
                $data['requester_role'] ?? $data['requested_by_role'] ?? '',
                $data['requester_name'] ?? $data['name'] ?? '',
            ];
            if ($tid > 1) $params[] = $tid;

            $stmt->execute($params);
            $requestId = (int)$this->pdo->lastInsertId();

            $this->logActivity($requestId, 'request_submitted', 'Request submitted');

            return ['success' => true, 'request_id' => $requestId];
        } catch (Exception $e) {
            error_log('DepartmentRequestService::submitRequest error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getRequestsForDepartment(int $departmentId, array $filters = []): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND dr.tenant_id = ?" : "";

        $statusFilter = $filters['status'] ?? null;
        if ($statusFilter) {
            $where .= " AND dr.status = ?";
        }

        $priorityFilter = $filters['priority'] ?? null;
        if ($priorityFilter) {
            $where .= " AND dr.priority = ?";
        }

        $sql = "SELECT dr.*, d.name as department_name,
                u.name as requester_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.id = dr.department_id" . ($tid > 1 ? " AND d.tenant_id = ?" : "") . "
                LEFT JOIN users u ON u.id = dr.requested_by" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE dr.department_id = ?{$where}
                ORDER BY dr.created_at DESC";

        $params = [];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }
        $params[] = $departmentId;
        if ($tid > 1) $params[] = $tid;
        if ($statusFilter) $params[] = $statusFilter;
        if ($priorityFilter) $params[] = $priorityFilter;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestsByUser(int $userId): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND dr.tenant_id = ?" : "";

        $sql = "SELECT dr.*, d.name as department_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.id = dr.department_id" . ($tid > 1 ? " AND d.tenant_id = ?" : "") . "
                WHERE dr.requested_by = ?{$where}
                ORDER BY dr.created_at DESC";

        $params = [$userId];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPending(int $limit = 50): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND dr.tenant_id = ?" : "";

        $sql = "SELECT dr.*, d.name as department_name, u.name as requester_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.id = dr.department_id" . ($tid > 1 ? " AND d.tenant_id = ?" : "") . "
                LEFT JOIN users u ON u.id = dr.requested_by" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE dr.status IN ('open', 'in_progress'){$where}
                ORDER BY dr.created_at DESC
                LIMIT ?";

        $params = [];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }
        $params[] = $limit;
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequest(int $requestId): ?array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND dr.tenant_id = ?" : "";

        $sql = "SELECT dr.*, d.name as department_name, d.head_user_id,
                u.name as requester_name, u.email as requester_email,
                a.name as assignee_name
                FROM department_requests dr
                LEFT JOIN departments d ON d.id = dr.department_id" . ($tid > 1 ? " AND d.tenant_id = ?" : "") . "
                LEFT JOIN users u ON u.id = dr.requested_by" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                LEFT JOIN users a ON a.id = dr.assigned_to" . ($tid > 1 ? " AND a.tenant_id = ?" : "") . "
                WHERE dr.id = ?{$where}";

        $params = [];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; $params[] = $tid; }
        $params[] = $requestId;
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateStatus(int $requestId, string $status, int $userId, string $comment = ''): array
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = ?" : "";

            $sql = "UPDATE department_requests
                    SET status = ?, updated_at = NOW()
                    WHERE id = ?{$where}";

            $params = [$status, $requestId];
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

    public function assign(int $requestId, ?int $userId, ?string $role = null): array
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = ?" : "";

            $sql = "UPDATE department_requests
                    SET assigned_to = ?, status = 'in_progress', updated_at = NOW()
                    WHERE id = ?{$where}";

            $params = [$userId, $requestId];
            if ($tid > 1) $params[] = $tid;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->logActivity($requestId, 'assigned', "Assigned to user ID: " . ($userId ?? 'NULL'));

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addComment(int $requestId, int $userId, string $comment, bool $isInternal = false): bool
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            $userName = $this->getUserName($userId);

            $sql = "INSERT INTO department_request_comments
                    (request_id, user_id, comment, is_internal{$tenantCol})
                    VALUES (?, ?, ?, ?{$tenantVal})";

            $stmt = $this->pdo->prepare($sql);
            $params = [$requestId, $userId, $comment, $isInternal ? 1 : 0];
            if ($tid > 1) $params[] = $tid;

            $stmt->execute($params);
            return true;
        } catch (Exception $e) {
            error_log('DepartmentRequestService::addComment error: ' . $e->getMessage());
            return false;
        }
    }

    public function getComments(int $requestId): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND drc.tenant_id = ?" : "";

        $sql = "SELECT drc.*, u.name as commenter_name
                FROM department_request_comments drc
                LEFT JOIN users u ON u.id = drc.user_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE drc.request_id = ?{$where}
                ORDER BY drc.created_at ASC";

        $params = [$requestId];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(int $departmentId): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = ?" : "";

        $sql = "SELECT status, COUNT(*) as count
                FROM department_requests
                WHERE department_id = ?{$where}
                GROUP BY status";

        $params = [$departmentId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $stats = [
            'total' => 0, 'open' => 0, 'in_progress' => 0,
            'resolved' => 0, 'rejected' => 0, 'closed' => 0
        ];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }

        return $stats;
    }

    public function getPendingCount(int $departmentId): int
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND tenant_id = ?" : "";

        $sql = "SELECT COUNT(*) as count
                FROM department_requests
                WHERE department_id = ? AND status IN ('open', 'in_progress'){$where}";

        $params = [$departmentId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['count'] ?? 0);
    }

    public function getAllDepartmentsWithCounts(): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " AND d.tenant_id = ?" : "";

        $sql = "SELECT d.id, d.code, d.name, d.head_user_id,
                COALESCE(dr.pending_count, 0) as pending_count
                FROM departments d
                LEFT JOIN (
                    SELECT department_id, COUNT(*) as pending_count
                    FROM department_requests
                    WHERE status IN ('open', 'in_progress')" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                    GROUP BY department_id
                ) dr ON dr.department_id = d.id
                WHERE d.status = 'active'{$where}
                ORDER BY d.name";

        $params = [];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function notifyDepartmentHead(int $requestId, int $departmentId): void
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = ?" : "";

            $sql = "SELECT head_user_id FROM departments WHERE id = ?{$where}";
            $stmt = $this->pdo->prepare($sql);
            $params = [$departmentId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dept && $dept['head_user_id']) {
                $notifService = new NotificationService($this->db);
                $notifService->send(
                    (int)$dept['head_user_id'],
                    'in_app',
                    'New Department Request',
                    "A new request has been submitted to your department.",
                    [
                        'event_type' => 'department_request',
                        'request_id' => $requestId,
                        'department_id' => $departmentId,
                        'url' => '/admin/department-requests/' . $requestId
                    ]
                );
            }
        } catch (Exception $e) {
            error_log('DepartmentRequestService::notifyDepartmentHead error: ' . $e->getMessage());
        }
    }

    private function getUserName(int $userId): string
    {
        try {
            $tid = $this->tenantId();
            $where = $tid > 1 ? " AND tenant_id = ?" : "";

            $sql = "SELECT name FROM users WHERE id = ?{$where}";
            $stmt = $this->pdo->prepare($sql);
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user['name'] ?? 'Unknown User';
        } catch (Exception $e) {
            return 'Unknown User';
        }
    }

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

    public function getDepartmentForUser(string $role): ?int
    {
        $map = [
            'admin' => 1, 'super_admin' => 1,
            'sales_director' => 3, 'agent' => 3, 'associate' => 3,
            'finance_manager' => 2, 'finance_head' => 2, 'cfo' => 2,
            'legal_head' => 7, 'legal_advisor' => 7,
            'hr_manager' => 8, 'hr_head' => 8, 'chro' => 8,
            'it_manager' => 9, 'cto' => 9,
            'construction_director' => 6,
            'operations_head' => 5, 'operations_director' => 5,
            'marketing_director' => 4, 'cmo' => 4,
            'land_director' => 10,
            'employee' => 11, 'telecaller' => 11, 'customer_service' => 11,
            'customer' => 11, 'user' => 11,
            'farmer' => 10,
        ];

        return $map[$role] ?? 11;
    }
}
