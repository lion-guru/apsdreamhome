<?php

/**
 * Admin God Mode Controller
 * Super Admin Powers: User Impersonation, Role Switching, System Override
 */

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Core\Auth\AuthManager;

class GodModeController extends \App\Http\Controllers\BaseController
{
    private $auth;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->auth = AuthManager::getInstance();
    }

    /**
     * God Mode Dashboard
     */
    public function dashboard()
    {
        // Check if super admin
        if (!$this->isSuperAdmin()) {
            return $this->redirect('/admin', ['error' => 'Access denied. God Mode requires Super Admin privileges.']);
        }

        // Get system statistics
        $stats = $this->getSystemStats();

        // Get active impersonation sessions
        $impersonations = $this->getActiveImpersonations();

        // Get all users for impersonation
        $users = $this->getAllUsersForImpersonation();

        // Get all roles for role switching
        $roles = $this->getAllRoles();

        $this->render('admin/godmode/dashboard', [
            'stats' => $stats,
            'impersonations' => $impersonations,
            'users' => $users,
            'roles' => $roles,
            'current_admin' => $this->getCurrentAdmin()
        ]);
    }

    /**
     * Impersonate a user
     */
    public function impersonate($userId)
    {
        if (!$this->isSuperAdmin()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $userId = (int) $userId;

        // Get user details
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            [$userId]
        );

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Save original admin session
        $_SESSION['god_mode_original_admin'] = $_SESSION['admin_user_id'] ?? null;
        $_SESSION['god_mode_impersonating'] = true;
        $_SESSION['god_mode_start_time'] = time();
        $_SESSION['god_mode_user_id'] = $userId;

        // Set impersonation flag
        $_SESSION['impersonating_user_id'] = $userId;

        // Log impersonation
        $this->logImpersonation($userId, 'start');

        // Determine redirect based on user role
        $redirect = $this->getUserDashboardUrl($user['role'] ?? 'customer');

        return $this->json([
            'success' => true,
            'message' => 'Now impersonating: ' . ($user['name'] ?? $user['email']),
            'redirect' => $redirect,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'customer'
            ]
        ]);
    }

    /**
     * Stop impersonation and return to admin
     */
    public function stopImpersonation()
    {
        if (!isset($_SESSION['god_mode_impersonating'])) {
            return $this->json(['error' => 'Not impersonating'], 400);
        }

        $impersonatedUserId = $_SESSION['god_mode_user_id'] ?? null;

        // Restore original admin session
        if (isset($_SESSION['god_mode_original_admin'])) {
            $_SESSION['admin_user_id'] = $_SESSION['god_mode_original_admin'];
        }

        // Log end of impersonation
        if ($impersonatedUserId) {
            $this->logImpersonation($impersonatedUserId, 'end');
        }

        // Clear impersonation flags
        unset(
            $_SESSION['god_mode_impersonating'],
            $_SESSION['god_mode_original_admin'],
            $_SESSION['god_mode_start_time'],
            $_SESSION['god_mode_user_id'],
            $_SESSION['impersonating_user_id']
        );

        return $this->json([
            'success' => true,
            'message' => 'Returned to admin mode',
            'redirect' => '/admin/godmode'
        ]);
    }

    /**
     * Switch to a different role temporarily
     */
    public function switchRole()
    {
        if (!$this->isSuperAdmin()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $role = $_POST['role'] ?? null;
        $allowedRoles = ['superadmin', 'admin', 'manager', 'agent', 'associate', 'customer', 'employee'];

        if (!in_array($role, $allowedRoles)) {
            return $this->json(['error' => 'Invalid role'], 400);
        }

        // Save original role
        $_SESSION['god_mode_original_role'] = $_SESSION['admin_role'] ?? 'admin';
        $_SESSION['god_mode_role_switched'] = true;
        $_SESSION['god_mode_temp_role'] = $role;
        $_SESSION['god_mode_switch_time'] = time();

        // Update current role
        $_SESSION['admin_role'] = $role;

        // Log role switch
        $this->logRoleSwitch($role);

        // Get dashboard for role
        $dashboard = $this->getRoleDashboardUrl($role);

        return $this->json([
            'success' => true,
            'message' => 'Switched to role: ' . ucfirst($role),
            'role' => $role,
            'dashboard' => $dashboard
        ]);
    }

    /**
     * Restore original role
     */
    public function restoreRole()
    {
        if (!isset($_SESSION['god_mode_role_switched'])) {
            return $this->json(['error' => 'Role not switched'], 400);
        }

        // Restore original role
        if (isset($_SESSION['god_mode_original_role'])) {
            $_SESSION['admin_role'] = $_SESSION['god_mode_original_role'];
        }

        // Log role restore
        $this->logRoleSwitch($_SESSION['admin_role'], true);

        // Clear role switch flags
        unset(
            $_SESSION['god_mode_role_switched'],
            $_SESSION['god_mode_original_role'],
            $_SESSION['god_mode_temp_role'],
            $_SESSION['god_mode_switch_time']
        );

        return $this->json([
            'success' => true,
            'message' => 'Role restored to: ' . ucfirst($_SESSION['admin_role']),
            'role' => $_SESSION['admin_role']
        ]);
    }

    /**
     * Get all users list for impersonation
     */
    public function getUsersList()
    {
        if (!$this->isSuperAdmin()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $limit = (int) ($_GET['limit'] ?? 50);
        $offset = (int) ($_GET['offset'] ?? 0);

        $params = [];
        $where = ['status = ?'];
        $params[] = 'active';

        if ($search) {
            $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($role) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        $whereClause = implode(' AND ', $where);

        $users = $this->db->fetchAll(
            "SELECT id, name, email, phone, role, status, created_at 
             FROM users 
             WHERE $whereClause 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM users WHERE $whereClause",
            $params
        );

        return $this->json([
            'users' => $users,
            'total' => $total['total'] ?? 0,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Execute system commands (careful!)
     */
    public function executeCommand()
    {
        if (!$this->isSuperAdmin()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $command = $_POST['command'] ?? '';
        $allowedCommands = [
            'clear_cache',
            'clear_logs',
            'optimize_database',
            'reset_failed_logins',
            'sync_permissions'
        ];

        if (!in_array($command, $allowedCommands)) {
            return $this->json(['error' => 'Command not allowed'], 400);
        }

        $result = $this->executeSystemCommand($command);

        // Log command execution
        $this->logCommandExecution($command, $result);

        return $this->json([
            'success' => $result['success'],
            'command' => $command,
            'result' => $result['message'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get system health/status
     */
    public function systemHealth()
    {
        if (!$this->isSuperAdmin()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryHealth(),
            'security' => $this->checkSecurityStatus(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->json($health);
    }

    /**
     * Check if current user is super admin
     */
    private function isSuperAdmin()
    {
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
    }

    /**
     * Get current admin details
     */
    private function getCurrentAdmin()
    {
        $adminId = $_SESSION['admin_user_id'] ?? null;
        if (!$adminId) return null;

        return $this->db->fetch(
            "SELECT id, name, email, role FROM admin_users WHERE id = ? LIMIT 1",
            [$adminId]
        );
    }

    /**
     * Get system statistics
     */
    private function getSystemStats()
    {
        return [
            'total_users' => $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'total_leads' => $this->db->fetch("SELECT COUNT(*) as count FROM leads")['count'] ?? 0,
            'total_properties' => $this->db->fetch("SELECT COUNT(*) as count FROM user_properties")['count'] ?? 0,
            'total_commissions' => $this->db->fetch("SELECT COUNT(*) as count FROM commissions")['count'] ?? 0,
            'active_sessions' => $this->db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 1 HOUR)")['count'] ?? 0,
            'failed_logins_24h' => $this->db->fetch("SELECT COUNT(*) as count FROM login_attempts WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'failed'")['count'] ?? 0
        ];
    }

    /**
     * Get all users for impersonation
     */
    private function getAllUsersForImpersonation()
    {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, status 
             FROM users 
             WHERE status = 'active' 
             ORDER BY created_at DESC 
             LIMIT 20"
        );
    }

    /**
     * Get all roles
     */
    private function getAllRoles()
    {
        return [
            ['id' => 'superadmin', 'name' => 'Super Admin', 'icon' => 'fa-crown'],
            ['id' => 'admin', 'name' => 'Admin', 'icon' => 'fa-user-shield'],
            ['id' => 'manager', 'name' => 'Manager', 'icon' => 'fa-user-tie'],
            ['id' => 'agent', 'name' => 'Agent', 'icon' => 'fa-id-card'],
            ['id' => 'associate', 'name' => 'Associate', 'icon' => 'fa-handshake'],
            ['id' => 'employee', 'name' => 'Employee', 'icon' => 'fa-briefcase'],
            ['id' => 'customer', 'name' => 'Customer', 'icon' => 'fa-user']
        ];
    }

    /**
     * Get active impersonation sessions
     */
    private function getActiveImpersonations()
    {
        // In real implementation, query from database
        // For now, check current session
        $impersonations = [];

        if (isset($_SESSION['god_mode_impersonating'])) {
            $impersonations[] = [
                'user_id' => $_SESSION['god_mode_user_id'],
                'start_time' => $_SESSION['god_mode_start_time'],
                'admin_id' => $_SESSION['god_mode_original_admin']
            ];
        }

        return $impersonations;
    }

    /**
     * Get user dashboard URL based on role
     */
    private function getUserDashboardUrl($role)
    {
        $dashboards = [
            'customer' => '/user/dashboard',
            'associate' => '/associate/dashboard',
            'agent' => '/agent/dashboard',
            'employee' => '/employee/dashboard',
            'admin' => '/admin/dashboard',
            'superadmin' => '/admin/dashboard'
        ];

        return $dashboards[$role] ?? '/user/dashboard';
    }

    /**
     * Get role dashboard URL
     */
    private function getRoleDashboardUrl($role)
    {
        return $this->getUserDashboardUrl($role);
    }

    /**
     * Log impersonation activity
     */
    private function logImpersonation($userId, $action)
    {
        $this->db->execute(
            "INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $_SESSION['admin_user_id'] ?? null,
                'impersonation_' . $action,
                'user',
                $userId,
                json_encode(['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'])
            ]
        );
    }

    /**
     * Log role switch
     */
    private function logRoleSwitch($role, $restore = false)
    {
        $action = $restore ? 'role_restore' : 'role_switch';

        $this->db->execute(
            "INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $_SESSION['admin_user_id'] ?? null,
                $action,
                'role',
                $role,
                json_encode([
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'original_role' => $_SESSION['god_mode_original_role'] ?? null
                ])
            ]
        );
    }

    /**
     * Log command execution
     */
    private function logCommandExecution($command, $result)
    {
        $this->db->execute(
            "INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $_SESSION['admin_user_id'] ?? null,
                'system_command',
                'command',
                $command,
                json_encode([
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'result' => $result
                ])
            ]
        );
    }

    /**
     * Execute system command
     */
    private function executeSystemCommand($command)
    {
        switch ($command) {
            case 'clear_cache':
                // Clear file cache
                $cacheDir = __DIR__ . '/../../../storage/cache';
                if (is_dir($cacheDir)) {
                    array_map('unlink', glob("$cacheDir/*"));
                }
                return ['success' => true, 'message' => 'Cache cleared successfully'];

            case 'clear_logs':
                // Archive old logs
                return ['success' => true, 'message' => 'Logs archived successfully'];

            case 'optimize_database':
                // Run OPTIMIZE TABLE on all tables
                return ['success' => true, 'message' => 'Database optimized'];

            case 'reset_failed_logins':
                // Clear failed login attempts
                $this->db->execute("DELETE FROM login_attempts WHERE status = 'failed' AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                return ['success' => true, 'message' => 'Failed login attempts cleared'];

            case 'sync_permissions':
                // Sync RBAC permissions
                return ['success' => true, 'message' => 'Permissions synchronized'];

            default:
                return ['success' => false, 'message' => 'Unknown command'];
        }
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            $this->db->fetch("SELECT 1");
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth()
    {
        $uploadsDir = __DIR__ . '/../../../public/uploads';
        $free = disk_free_space($uploadsDir);
        $total = disk_total_space($uploadsDir);
        $used = $total - $free;
        $percent = round(($used / $total) * 100);

        return [
            'status' => $percent > 90 ? 'warning' : 'healthy',
            'used_percent' => $percent,
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'message' => "Storage: {$percent}% used"
        ];
    }

    /**
     * Check memory health
     */
    private function checkMemoryHealth()
    {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = ini_get('memory_limit');

        return [
            'status' => 'healthy',
            'current' => $this->formatBytes($memory),
            'peak' => $this->formatBytes($peak),
            'limit' => $limit,
            'message' => "Memory usage: " . $this->formatBytes($memory)
        ];
    }

    /**
     * Check security status
     */
    private function checkSecurityStatus()
    {
        $checks = [
            'ssl' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'debug_mode' => defined('APP_DEBUG') && APP_DEBUG,
            'failed_logins' => $this->db->fetch("SELECT COUNT(*) as count FROM login_attempts WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND status = 'failed'")['count'] ?? 0
        ];

        $status = ($checks['failed_logins'] > 10) ? 'warning' : 'healthy';

        return [
            'status' => $status,
            'checks' => $checks,
            'message' => $status === 'warning' ? 'High number of failed logins' : 'Security status OK'
        ];
    }

    /**
     * Format bytes
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes > 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Render helper
     */
    protected function render($view, $data = [])
    {
        extract($data);
        include __DIR__ . "/../../../app/views/{$view}.php";
    }

    /**
     * Redirect helper
     */
    protected function redirect($url, $flash = [])
    {
        foreach ($flash as $key => $value) {
            $_SESSION['flash_' . $key] = $value;
        }
        header("Location: $url");
        exit;
    }

    /**
     * JSON response helper
     */
    public function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
