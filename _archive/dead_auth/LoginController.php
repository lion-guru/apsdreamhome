<?php
/**
 * LoginController â€” Standard MVC Login
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class LoginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show unified login page
     */
    public function showLogin()
    {
        @session_start();

        // Block test_login bypass in production
        if (isset($_GET['test_login'])) {
            if ((defined('APP_ENV') && APP_ENV === 'production') || (getenv('APP_ENV') === 'production')) {
                http_response_code(403);
                exit('Forbidden in production');
            }
            
            // test_login branch entered
            $db = Database::getInstance();
            $admin = null;
            $loginMode = $_GET['test_login'];
            
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tParams = $tid > 1 ? [$tid] : [];

            if ($loginMode == '2') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '1') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin','manager') $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '3') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'telecaller' $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '4') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'employee' $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '5') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'associate' $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '6') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'agent' $tenantSql ORDER BY id LIMIT 1", $tParams);
            } elseif ($loginMode == '7') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'customer' $tenantSql ORDER BY id LIMIT 1", $tParams);
            }

            if (!$admin) {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') $tenantSql ORDER BY id LIMIT 1", $tParams);
                if (!$admin) $admin = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@apsdreamhome.com', 'password' => '', 'role' => 'super_admin'];
            }

            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'] ?? 'admin';
            $_SESSION['user_email'] = $admin['email'] ?? '';
            $_SESSION['user_name'] = $admin['name'] ?? 'Admin';
            $_SESSION['logged_in'] = true;

            $adminRoles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'sales_director', 'marketing_director', 'construction_director', 'finance_director', 'hr_director', 'operations_director', 'legal_head', 'finance_head', 'hr_head', 'operations_head', 'department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager', 'legal_advisor', 'chartered_accountant', 'senior_developer'];
            if (in_array($admin['role'], $adminRoles)) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_role'] = $admin['role'];
            }

            $this->redirectToDashboard($admin['role']);
            exit;
        }

        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'] ?? 'customer';
            // If this is an admin role, redirect to admin dashboard
            $adminRoles = ['admin', 'super_admin', 'manager', 'employee', 'telecaller', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro',
                'sales_director', 'marketing_director', 'construction_director', 'finance_director', 'hr_director', 'operations_director',
                'legal_head', 'finance_head', 'hr_head', 'operations_head',
                'department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager',
                'finance_manager', 'property_manager', 'it_manager', 'operations_manager',
                'legal_advisor', 'chartered_accountant', 'senior_developer'];
            if (in_array($role, $adminRoles)) {
                header('Location: ' . BASE_URL . '/admin/dashboard');
            } else {
                $this->redirectToDashboard($role);
            }
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? $_SESSION['login_success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success'], $_SESSION['login_success']);

        include __DIR__ . '/../../../views/auth/core_login.php';
    }

    /**
     * Handle login (POST)
     */
    public function authenticate()
    {
        @session_start();

        $identity = trim($_POST['identity'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($identity) || empty($password)) {
            $_SESSION['errors'] = ['Email/Phone and password are required'];
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            $tid = 1;
            try {
                $tid = \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) { error_log($e->getMessage()); }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$identity, $identity];
            if ($tid > 1) $params[] = $tid;
            
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE (email = ? OR phone = ?) AND status != 'deleted'" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['errors'] = ['Invalid credentials'];
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            // Check status
            if (($user['status'] ?? 'active') !== 'active') {
                $_SESSION['errors'] = ['Account is ' . ($user['status'] ?? 'inactive') . '. Please contact support.'];
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'rejected') {
                $_SESSION['errors'] = ['Registration has been rejected.'];
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'pending') {
                $_SESSION['errors'] = ['Account pending approval. You will be notified once approved.'];
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            // Check 2FA
            if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                $_SESSION['pending_2fa_user'] = [
                    'id' => (int)$user['id'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'customer',
                ];
                $_SESSION['pending_2fa_attempts'] = 0;
                unset($_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role']);
                session_regenerate_id(true);
                header('Location: ' . BASE_URL . '/user/two-factor/verify');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();

            $role = $user['role'] ?? 'customer';
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;

            // All admin-level roles that get admin_id session
            $adminRoles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro',
                'sales_director', 'marketing_director', 'construction_director', 'finance_director', 'hr_director', 'operations_director',
                'legal_head', 'finance_head', 'hr_head', 'operations_head',
                'department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager',
                'finance_manager', 'property_manager', 'it_manager', 'operations_manager',
                'legal_advisor', 'chartered_accountant', 'senior_developer'];

            // Load role-specific IDs
            if (in_array($role, ['agent', 'associate'], true)) {
                try {
                    $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                    if ($ass) {
                        $_SESSION['associate_id'] = (int)$ass['id'];
                        if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                    }
                } catch (\Throwable $e) { error_log("LoginController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
            } elseif ($role === 'employee' || $role === 'telecaller') {
                try {
                    $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                    if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
                } catch (\Throwable $e) { error_log("LoginController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
            }

            // Set admin session for ALL admin-level roles
            if (in_array($role, $adminRoles, true)) {
                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_user_id'] = (int)$user['id'];
                $_SESSION['admin_email'] = $user['email'] ?? '';
                $_SESSION['admin_role'] = $role;
                $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
                $_SESSION['admin_username'] = $user['name'] ?? 'admin';
            }

            // Audit log
            try {
                require_once __DIR__ . '/../../../Services/AuditService.php';
                (new \App\Services\AuditService($db))->log('login', (int)$user['id'], $role, 'user', (int)$user['id'], 'User logged in');
            } catch (\Throwable $e) { error_log("LoginController::" . __FUNCTION__ . " error: " . $e->getMessage()); }

            // â”€â”€ Send login alert notifications â”€â”€
            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $loginNotifier->sendLoginAlerts(
                    (int)$user['id'], $role,
                    $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $isMobile, 'email'
                );
            } catch (\Throwable $e) {
                error_log("[LoginController] Login notification failed: " . $e->getMessage());
            }

            $this->redirectToDashboard($role);
            exit;
        } catch (\Exception $e) {
            error_log("LoginController login error: " . $e->getMessage());
            $_SESSION['errors'] = ['Login failed. Please try again.'];
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    public function logout()
    {
        @session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    /**
     * Redirect to role-specific dashboard
     */
    private function redirectToDashboard(string $role): void
    {
        $map = [
            'admin' => '/admin/dashboard',
            'super_admin' => '/admin/dashboard',
            'manager' => '/admin/dashboard',
            'employee' => '/employee/dashboard',
            'associate' => '/associate/dashboard',
            'agent' => '/agent/dashboard',
            'telecaller' => '/employee/dashboard',
            'customer' => '/user/dashboard',
            'ceo' => '/admin/dashboard/ceo',
            'cfo' => '/admin/dashboard/cfo',
            'cto' => '/admin/dashboard/cto',
            'coo' => '/admin/dashboard/coo',
            'cmo' => '/admin/dashboard/cmo',
            'chro' => '/admin/dashboard/chro',
            'sales_director' => '/admin/dashboard/sales',
            'marketing_director' => '/admin/dashboard/marketing',
            'construction_director' => '/admin/dashboard/operations',
            'finance_director' => '/admin/dashboard/finance',
            'hr_director' => '/admin/dashboard/hr',
            'department_manager' => '/admin/dashboard',
            'project_manager' => '/admin/dashboard',
            'sales_manager' => '/admin/dashboard/sales',
            'hr_manager' => '/admin/dashboard/hr',
            'marketing_manager' => '/admin/dashboard/marketing',
            'finance_manager' => '/admin/dashboard/finance',
            'property_manager' => '/admin/dashboard',
            'it_manager' => '/admin/dashboard',
            'operations_manager' => '/admin/dashboard/operations',
            'legal_head' => '/admin/dashboard',
            'finance_head' => '/admin/dashboard/finance',
            'hr_head' => '/admin/dashboard/hr',
            'operations_head' => '/admin/dashboard/operations',
            'operations_director' => '/admin/dashboard/operations',
            'legal_advisor' => '/admin/dashboard',
            'chartered_accountant' => '/admin/dashboard/finance',
            'senior_developer' => '/admin/dashboard/it',
        ];
        $redirect = $map[$role] ?? '/admin/dashboard';
        $roleLabels = [
            'admin' => 'Admin Panel',
            'super_admin' => 'Super Admin Panel',
            'manager' => 'Manager Dashboard',
            'employee' => 'Employee Dashboard',
            'associate' => 'Associate Dashboard',
            'agent' => 'Agent Dashboard',
            'telecaller' => 'Telecaller Dashboard',
            'customer' => 'User Dashboard',
            'ceo' => 'CEO Dashboard',
            'cfo' => 'CFO Dashboard',
            'cto' => 'CTO Dashboard',
            'coo' => 'COO Dashboard',
            'cmo' => 'CMO Dashboard',
            'chro' => 'CHRO Dashboard',
        ];
        $label = $roleLabels[$role] ?? 'Dashboard';
        $_SESSION['login_success'] = "Welcome! Redirecting to {$label}...";
        header('Location: ' . BASE_URL . $redirect);
    }
}?>