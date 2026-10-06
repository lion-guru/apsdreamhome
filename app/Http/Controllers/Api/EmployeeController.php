<?php

namespace App\Http\Controllers\Api;

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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch tasks: ' . $e->getMessage(), 500);
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
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch attendance: ' . $e->getMessage(), 500);
        }
    }
}
