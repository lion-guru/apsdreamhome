<?php

namespace App\Http\Controllers\Api;


// NOTE (S449): this file is namespaced, so a bare PDO:: resolves to
// <this-namespace>\PDO -> "Class App\...\PDO not found" fatal. The
// custom autoloader does not fall back to the global PDO class.
use PDO;
use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class EmployeeController extends BaseController
{
    use TenantAwareTrait;
    
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function punchIn()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO employee_attendance (user_id, punch_in, status, tenant_id) VALUES (?, NOW(), 'present', ?)");
            $stmt->execute([$userId, $tid]);
            $this->jsonResponse(['success' => true, 'message' => 'Punched in successfully']);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to punch in: ' . $e->getMessage(), 500);
        }
    }

    public function punchOut()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("UPDATE employee_attendance SET punch_out = NOW(), status = 'present' WHERE user_id = ? AND punch_out IS NULL{$tidSql} ORDER BY punch_in DESC LIMIT 1");
            $stmt->execute($params);
            $this->jsonResponse(['success' => true, 'message' => 'Punched out successfully']);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to punch out: ' . $e->getMessage(), 500);
        }
    }

    public function attendanceStatus()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("SELECT * FROM employee_attendance WHERE user_id = ?{$tidSql} ORDER BY punch_in DESC LIMIT 1");
            $stmt->execute($params);
            $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->jsonResponse(['success' => true, 'data' => $attendance]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch attendance: ' . $e->getMessage(), 500);
        }
    }

    public function dashboard()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_attendance FROM employee_attendance WHERE user_id = ?{$tidSql}");
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->jsonResponse(['success' => true, 'data' => $total]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch dashboard: ' . $e->getMessage(), 500);
        }
    }

    public function tasks()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("SELECT * FROM tasks WHERE assigned_to = ?{$tidSql} ORDER BY created_at DESC LIMIT 20");
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->jsonResponse(['success' => true, 'data' => $tasks]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch tasks: ' . $e->getMessage(), 500);
        }
    }

    public function updateTask($id = 0)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $taskId = (int) $id;
            if ($taskId <= 0) {
                $this->jsonError('Invalid task id', 400);
                return;
            }

            // Accept JSON body without assuming content-type header is present
            $raw = file_get_contents('php://input');
            $body = json_decode((string) $raw, true);
            if (!is_array($body)) {
                $body = $_POST;
            }
            $status = (string) ($body['status'] ?? '');
            // Advisory whitelist - HR decides, the record notes the request
            $allowed = ['pending', 'in_progress', 'completed', 'cancelled'];
            if (!in_array($status, $allowed, true)) {
                $this->jsonError('Status must be one of: ' . implode(', ', $allowed), 400);
                return;
            }
            $notes = isset($body['notes']) ? trim((string) $body['notes']) : null;

            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';

            // Verify ownership FIRST so the client never sees a silent 0-row "success:false" for no reason
            $chkParams = [$taskId, $userId];
            if ($tid > 1) $chkParams[] = $tid;
            $chk = $this->db->prepare("SELECT id FROM tasks WHERE id = ? AND assigned_to = ?{$tidSql}");
            $chk->execute($chkParams);
            if (!$chk->fetch()) {
                $this->jsonResponse(['success' => false, 'error' => 'Task not found or not assigned to you']);
                return;
            }

            $params = [$status, $status, $taskId, $userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("UPDATE tasks SET status = ?, completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END, updated_at = NOW() WHERE id = ? AND assigned_to = ?{$tidSql}");
            $stmt->execute($params);

            if ($notes !== null) {
                $this->db->prepare("UPDATE tasks SET notes = CONCAT(COALESCE(notes,''), IF(COALESCE(notes,'') = '', '', '\n'), '[', NOW(), '] ', ?) WHERE id = ? AND assigned_to = ?")
                    ->execute([$notes, $taskId, $userId]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Task updated', 'status' => $status]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to update task: ' . $e->getMessage(), 500);
        }
    }

    public function attendance()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->db->prepare("SELECT * FROM employee_attendance WHERE user_id = ?{$tidSql} ORDER BY punch_in DESC LIMIT 30");
            $stmt->execute($params);
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->jsonResponse(['success' => true, 'data' => $attendance]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch attendance: ' . $e->getMessage(), 500);
        }
    }
}
