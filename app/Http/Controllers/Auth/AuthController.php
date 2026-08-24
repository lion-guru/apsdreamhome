<?php
/**
 * AuthController — Unified Authentication Controller
 *
 * Consolidates login, logout, registration redirect, password reset,
 * and user session management into a single controller.
 *
 * Merges functionality from:
 * - LoginController         (login, logout, test_login bypass)
 * - AuthenticationController (forgotPassword, resetPassword, changePassword, profile)
 * - AuthController (root)   (forgotPassword GET)
 * - CustomerAuthController  (rate limiting, password rehash, secure logout, audit log)
 *
 * Uses AuthenticationService for clean service-layer auth logic.
 * Delegates registration to RegisterController (separate controller for role-based registration).
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\View;
use App\Core\Flash;
use App\Core\Redirect;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\PasswordOtpService;
use App\Services\Communication\LoginNotificationService;
use App\Core\Middleware\TenantContext;

class AuthController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private const MAX_ATTEMPTS      = 5;
    private const LOCKOUT_WINDOW    = 900;
    private const ATTEMPT_WINDOW    = 900;
    private const THROTTLE_DELAY    = [1, 2, 4, 8, 16];

    private $authService;
    private $otpService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthenticationService();
        $this->otpService  = new PasswordOtpService();
    }

    /* ================================================================
     * LOGIN
     * ================================================================ */

    public function showLogin()
    {
        @session_start();

        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'] ?? 'customer';
            $this->redirectToDashboard($role);
            exit;
        }

        // test_login bypass (dev only)
        if (isset($_GET['test_login'])) {
            if ((defined('APP_ENV') && APP_ENV === 'production') || getenv('APP_ENV') === 'production') {
                http_response_code(403);
                exit('Forbidden in production');
            }
            $this->handleTestLogin($_GET['test_login']);
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['login_error'] ?? $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['login_success'] ?? $_SESSION['success'] ?? null;
        $old_email = $_SESSION['login_old_email'] ?? $_POST['email'] ?? '';
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['login_error'],
              $_SESSION['login_old_email'], $_SESSION['success'], $_SESSION['login_success']);

        include __DIR__ . '/../../../views/auth/core_login.php';
    }

    /**
     * Handle login POST (merged from LoginController + CustomerAuthController security)
     */
    public function authenticate()
    {
        @session_start();

        $identity = trim($_POST['identity'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if (empty($identity) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email/phone and password.';
            $_SESSION['login_old_email'] = $identity;
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $db = Database::getInstance();

        // ── Rate limiting ──
        if ($this->isLockedOut($db, $identity)) {
            $remaining = $this->getLockoutRemaining($db, $identity);
            $_SESSION['login_error'] = "Too many failed attempts. Please try again in {$remaining} minutes.";
            $_SESSION['login_old_email'] = $identity;
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // ── Progressive throttle ──
        $attempts = $this->getRecentAttempts($db, $identity);
        if ($attempts > 0 && $attempts <= count(self::THROTTLE_DELAY)) {
            $requiredDelay = self::THROTTLE_DELAY[$attempts - 1];
            $lastAttempt = (int)($db->fetchOne(
                "SELECT MAX(created_at) as last FROM login_attempts WHERE identifier = ? AND success = 0",
                [$identity]
            )['last'] ?? 0);
            $elapsed = time() - strtotime($lastAttempt);
            if ($elapsed < $requiredDelay) {
                $remaining = $requiredDelay - $elapsed;
                $_SESSION['login_error'] = "Too many attempts. Please wait {$remaining} seconds.";
                $_SESSION['login_old_email'] = $identity;
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }
        }

        try {
            $user = $this->findUser($db, $identity);

            if (!$user || !password_verify($password, $user['password'])) {
                $this->logAttempt($db, $identity, false);
                $_SESSION['login_error'] = 'Invalid email/phone or password.';
                $_SESSION['login_old_email'] = $identity;
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            // Account status checks
            if (($user['status'] ?? 'active') !== 'active') {
                $this->logAttempt($db, $identity, false);
                $_SESSION['login_error'] = 'Invalid email/phone or password.';
                $_SESSION['login_old_email'] = $identity;
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'pending') {
                $this->logAttempt($db, $identity, false);
                $_SESSION['login_error'] = 'Your account is pending approval. You will be notified once approved.';
                $_SESSION['login_old_email'] = $identity;
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'rejected') {
                $this->logAttempt($db, $identity, false);
                $_SESSION['login_error'] = 'Registration has been rejected. Please contact support.';
                $_SESSION['login_old_email'] = $identity;
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }

            // Password rehash for security upgrade
            if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                $db->execute("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']]);
            }

            $this->clearAttempts($db, $identity);

            // 2FA check
            if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                $_SESSION['pending_2fa_user'] = [
                    'id'    => (int)$user['id'],
                    'email' => $user['email'],
                    'role'  => $user['role'] ?? 'customer',
                ];
                $_SESSION['pending_2fa_attempts'] = 0;
                session_regenerate_id(true);
                header('Location: ' . BASE_URL . '/user/two-factor/verify');
                exit;
            }

            // Establish session
            $this->establishSession($user, $identity);

            // Audit log
            try {
                require_once __DIR__ . '/../../../Services/AuditService.php';
                $audit = new \App\Services\AuditService($db);
                $audit->log('login', (int)$user['id'], $user['role'] ?? 'customer', 'user', (int)$user['id'], 'User logged in', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ]);
            } catch (\Throwable $e) { error_log("AuthController::authenticate audit error: " . $e->getMessage()); }

            // Login notifications
            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $notifier = new LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $notifier->sendLoginAlerts(
                    (int)$user['id'], $user['role'] ?? 'customer',
                    $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $isMobile, 'email'
                );
            } catch (\Throwable $e) { error_log("AuthController login notification failed: " . $e->getMessage()); }

            $this->redirectToDashboard($user['role'] ?? 'customer');
            exit;
        } catch (\Exception $e) {
            error_log("AuthController login error: " . $e->getMessage());
            $_SESSION['login_error'] = 'Something went wrong. Please try again.';
            $_SESSION['login_old_email'] = $identity;
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Logout — secure session clearing (merged from CustomerAuthController)
     */
    public function logout()
    {
        @session_start();

        if (!empty($_SESSION['user_id'])) {
            try {
                $db = Database::getInstance();
                require_once __DIR__ . '/../../../Services/AuditService.php';
                $audit = new \App\Services\AuditService($db);
                $audit->log('logout', $_SESSION['user_id'], $_SESSION['role'] ?? 'customer', 'user', $_SESSION['user_id']);
            } catch (\Throwable $e) { error_log("AuthController::logout audit error: " . $e->getMessage()); }
        }

        $_SESSION = [];

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params["path"],
                'domain' => $params["domain"],
                'secure' => $params["secure"],
                'httponly' => $params["httponly"],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /* ================================================================
     * PASSWORD RESET (merged from AuthenticationController + AuthController root)
     * ================================================================ */

    public function showForgotPassword()
    {
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        include __DIR__ . '/../../../views/auth/forgot_password.php';
    }

    public function forgotPassword()
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Please enter a valid email address'];
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        $result = $this->otpService->sendOtp($email, 'password_reset');

        if (isset($result['silent']) && $result['silent']) {
            $_SESSION['success'] = 'If an account exists with this email, a reset link has been sent.';
        } elseif ($result['success']) {
            $_SESSION['success'] = 'If an account exists with this email, a reset link has been sent.';
        } else {
            $_SESSION['errors'] = ['Something went wrong. Please try again.'];
            $_SESSION['old'] = $_POST;
        }

        header('Location: ' . BASE_URL . '/forgot-password');
        exit;
    }

    public function showResetPassword()
    {
        $token = $_GET['token'] ?? $_SESSION['reset_token'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ['Invalid reset token'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $email = $_SESSION['reset_email'] ?? '';

        include __DIR__ . '/../../../views/auth/reset_password.php';
    }

    public function resetPassword()
    {
        $token = $_POST['token'] ?? $_SESSION['reset_token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ['Invalid reset token'];
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        if ($password !== $passwordConfirmation) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['errors'] = ['Password must be at least 6 characters'];
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }

        $result = $this->otpService->resetPasswordWithToken($token, $password);

        if ($result['success']) {
            $_SESSION['success'] = 'Password reset successful. Please log in with your new password.';
            header('Location: ' . BASE_URL . '/login');
        } else {
            $_SESSION['errors'] = [$result['message'] ?? 'Reset failed'];
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
        }
        exit;
    }

    public function showChangePassword()
    {
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $user = $this->authService->getCurrentUser();

        include __DIR__ . '/../../../views/auth/change-password.php';
    }

    public function changePassword()
    {
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->authService->getCurrentUser();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $passwordConfirmation = $_POST['confirm_password'] ?? $_POST['password_confirmation'] ?? '';

        if (strlen($newPassword) < 6) {
            $_SESSION['errors'] = ['Password must be at least 6 characters'];
            header('Location: ' . BASE_URL . '/change-password');
            exit;
        }

        if ($newPassword !== $passwordConfirmation) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            header('Location: ' . BASE_URL . '/change-password');
            exit;
        }

        $result = $this->authService->changePassword($user['id'], $currentPassword, $newPassword);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . BASE_URL . '/user/profile');
        } else {
            $_SESSION['errors'] = [$result['message']];
            header('Location: ' . BASE_URL . '/change-password');
        }
        exit;
    }

    /* ================================================================
     * PROFILE
     * ================================================================ */

    public function showProfile()
    {
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->authService->getCurrentUser();
        include __DIR__ . '/../../../views/auth/profile.php';
    }

    /* ================================================================
     * AJAX ENDPOINTS (from AuthenticationController)
     * ================================================================ */

    public function getAuthStatus()
    {
        header('Content-Type: application/json');
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ]
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
        exit;
    }

    public function checkPermission()
    {
        header('Content-Type: application/json');
        if (!$this->authService->isAuthenticated()) {
            echo json_encode(['has_permission' => false, 'reason' => 'not_authenticated']);
            exit;
        }
        $permission = $_GET['permission'] ?? '';
        echo json_encode([
            'has_permission' => $this->authService->hasPermission($permission),
            'permission' => $permission,
            'user_role' => $this->authService->getUserRole(),
        ]);
        exit;
    }

    public function getCurrentUser()
    {
        header('Content-Type: application/json');
        if ($this->authService->isAuthenticated()) {
            echo json_encode(['success' => true, 'user' => $this->authService->getCurrentUser()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        }
        exit;
    }

    /* ================================================================
     * PRIVATE HELPERS
     * ================================================================ */

    /**
     * Find user by email or phone (with rate limiting)
     */
    private function findUser($db, string $identity): ?array
    {
        [$tSql, $tParams] = $this->getTenantSql();
        $params = array_merge([$identity, $identity], $tParams);
        return $db->fetchOne(
            "SELECT * FROM users WHERE (email = ? OR phone = ?) AND status = 'active' AND registration_status = 'approved'" . $tSql . " LIMIT 1",
            $params
        );
    }

    /**
     * Establish user session after successful login
     */
    private function establishSession(array $user, string $identity): void
    {
        session_regenerate_id(true);
        $_SESSION['last_regenerate'] = time();

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'] ?? '';
        $_SESSION['role'] = $user['role'] ?? 'customer';
        $_SESSION['logged_in'] = true;

        $db = Database::getInstance();
        [$tSql, $tParams] = $this->getTenantSql();

        // Role-specific session IDs
        $role = $_SESSION['role'];
        if (in_array($role, ['agent', 'associate'], true)) {
            try {
                $params = array_merge([(int)$user['id']], $tParams);
                $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tSql . " LIMIT 1", $params);
                if ($ass) {
                    $_SESSION['associate_id'] = (int)$ass['id'];
                    if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                }
            } catch (\Throwable $e) { error_log("AuthController associate lookup error: " . $e->getMessage()); }
        } elseif ($role === 'employee' || $role === 'telecaller') {
            try {
                $params = array_merge([(int)$user['id']], $tParams);
                $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tSql . " LIMIT 1", $params);
                if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
            } catch (\Throwable $e) { error_log("AuthController employee lookup error: " . $e->getMessage()); }
        }

        // Admin-level roles get admin_id
        $adminRoles = [
            'admin', 'super_admin', 'manager', 'employee', 'telecaller',
            'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro',
            'sales_director', 'marketing_director', 'construction_director',
            'finance_director', 'hr_director', 'operations_director',
            'legal_head', 'finance_head', 'hr_head', 'operations_head',
            'department_manager', 'project_manager', 'sales_manager',
            'hr_manager', 'marketing_manager', 'finance_manager',
            'property_manager', 'it_manager', 'operations_manager',
            'legal_advisor', 'chartered_accountant', 'senior_developer',
            'team_lead', 'telecalling_lead', 'sales_team_lead', 'support_lead',
            'senior_accountant', 'developer', 'content_writer', 'graphic_designer',
            'data_entry_operator', 'backoffice_staff', 'telecalling_executive',
            'support_executive', 'senior_associate', 'associate_team_lead',
            'senior_agent', 'franchise_owner', 'premium_customer',
            'verified_customer', 'guest_customer',
        ];

        if (in_array($role, $adminRoles, true)) {
            $_SESSION['admin_id']       = (int)$user['id'];
            $_SESSION['admin_user_id']  = (int)$user['id'];
            $_SESSION['admin_email']    = $user['email'] ?? '';
            $_SESSION['admin_role']     = $role;
            $_SESSION['admin_name']     = $user['name'] ?? 'Admin';
            $_SESSION['admin_username'] = $user['name'] ?? 'admin';
        }
    }

    /**
     * Handle test_login bypass (dev only)
     */
    private function handleTestLogin(string $mode): void
    {
        $db = Database::getInstance();
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $tParams = $tid > 1 ? [$tid] : [];

        $admin = null;
        $loginMode = $mode;

        switch ($loginMode) {
            case '2':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '1':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin','manager') " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '3':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'telecaller' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '4':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'employee' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '5':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'associate' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '6':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'agent' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
            case '7':
                $admin = $db->fetchOne("SELECT * FROM users WHERE role = 'customer' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
                break;
        }

        if (!$admin) {
            $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
            if (!$admin) $admin = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@apsdreamhome.com', 'password' => '', 'role' => 'super_admin'];
        }

        $_SESSION['user_id']     = $admin['id'];
        $_SESSION['role']        = $admin['role'] ?? 'admin';
        $_SESSION['user_email']  = $admin['email'] ?? '';
        $_SESSION['user_name']   = $admin['name'] ?? 'Admin';
        $_SESSION['logged_in']   = true;

        $adminRoles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro',
            'sales_director', 'marketing_director', 'construction_director', 'finance_director',
            'hr_director', 'operations_director', 'legal_head', 'finance_head', 'hr_head',
            'operations_head', 'department_manager', 'project_manager', 'sales_manager', 'hr_manager',
            'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager',
            'legal_advisor', 'chartered_accountant', 'senior_developer'];
        if (in_array($admin['role'], $adminRoles)) {
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_user_id']  = $admin['id'];
            $_SESSION['admin_email']    = $admin['email'] ?? '';
            $_SESSION['admin_role']     = $admin['role'];
            $_SESSION['admin_name']     = $admin['name'] ?? 'Admin';
            $_SESSION['admin_username'] = $admin['name'] ?? 'admin';
        }

        $this->redirectToDashboard($admin['role']);
        exit;
    }

    /**
     * Redirect to role-specific dashboard (merged map from LoginController + CustomerAuthController)
     */
    private function redirectToDashboard(string $role): void
    {
        $map = [
            'admin'                  => '/admin/dashboard',
            'super_admin'            => '/admin/dashboard',
            'manager'                => '/admin/dashboard',
            'employee'               => '/employee/dashboard',
            'telecaller'             => '/employee/dashboard',
            'associate'              => '/associate/dashboard',
            'agent'                  => '/agent/dashboard',
            'customer'               => '/user/dashboard',
            'ceo'                    => '/admin/dashboard/ceo',
            'cfo'                    => '/admin/dashboard/cfo',
            'cto'                    => '/admin/dashboard/cto',
            'coo'                    => '/admin/dashboard/coo',
            'cmo'                    => '/admin/dashboard/cmo',
            'chro'                   => '/admin/dashboard/chro',
            'sales_director'         => '/admin/dashboard/sales',
            'marketing_director'     => '/admin/dashboard/marketing',
            'construction_director'  => '/admin/dashboard/operations',
            'finance_director'       => '/admin/dashboard/finance',
            'hr_director'            => '/admin/dashboard/hr',
            'department_manager'     => '/admin/dashboard/sales',
            'project_manager'        => '/admin/dashboard/operations',
            'sales_manager'          => '/admin/dashboard/sales',
            'hr_manager'             => '/admin/dashboard/hr',
            'marketing_manager'      => '/admin/dashboard/marketing',
            'finance_manager'        => '/admin/dashboard/finance',
            'property_manager'       => '/admin/dashboard/operations',
            'it_manager'             => '/admin/dashboard/it',
            'operations_manager'     => '/admin/dashboard/operations',
            'team_lead'              => '/admin/dashboard',
            'telecalling_lead'       => '/admin/dashboard',
            'sales_team_lead'        => '/admin/dashboard/sales',
            'support_lead'           => '/admin/dashboard',
            'senior_accountant'      => '/admin/dashboard/finance',
            'senior_developer'       => '/admin/dashboard/it',
            'legal_advisor'          => '/admin/dashboard/operations',
            'chartered_accountant'   => '/admin/dashboard/finance',
            'accountant'             => '/admin/dashboard/finance',
            'developer'              => '/admin/dashboard/it',
            'content_writer'         => '/admin/dashboard/marketing',
            'graphic_designer'       => '/admin/dashboard/marketing',
            'data_entry_operator'    => '/admin/dashboard',
            'backoffice_staff'       => '/admin/dashboard',
            'telecalling_executive'  => '/employee/dashboard',
            'support_executive'      => '/employee/dashboard',
            'senior_associate'       => '/associate/dashboard',
            'associate_team_lead'    => '/associate/dashboard',
            'senior_agent'           => '/agent/dashboard',
            'franchise_owner'        => '/admin/dashboard/sales',
            'premium_customer'       => '/user/dashboard',
            'verified_customer'      => '/user/dashboard',
            'guest_customer'         => '/user/dashboard',
        ];
        $redirect = $map[$role] ?? '/admin/dashboard';

        $roleLabels = [
            'admin'       => 'Admin Panel',
            'super_admin' => 'Super Admin Panel',
            'manager'     => 'Manager Dashboard',
            'employee'    => 'Employee Dashboard',
            'telecaller'  => 'Telecaller Dashboard',
            'associate'   => 'Associate Dashboard',
            'agent'       => 'Agent Dashboard',
            'customer'    => 'User Dashboard',
            'ceo'         => 'CEO Dashboard',
            'cfo'         => 'CFO Dashboard',
            'cto'         => 'CTO Dashboard',
            'coo'         => 'COO Dashboard',
            'cmo'         => 'CMO Dashboard',
            'chro'        => 'CHRO Dashboard',
            'sales_director'        => 'Sales Director Dashboard',
            'marketing_director'    => 'Marketing Director Dashboard',
            'construction_director' => 'Construction Director Dashboard',
            'finance_director'      => 'Finance Director Dashboard',
            'hr_director'           => 'HR Director Dashboard',
            'department_manager'    => 'Department Manager Dashboard',
            'project_manager'       => 'Project Manager Dashboard',
            'sales_manager'         => 'Sales Manager Dashboard',
            'hr_manager'            => 'HR Manager Dashboard',
            'marketing_manager'     => 'Marketing Manager Dashboard',
            'finance_manager'       => 'Finance Manager Dashboard',
            'property_manager'      => 'Property Manager Dashboard',
            'it_manager'            => 'IT Manager Dashboard',
            'operations_manager'    => 'Operations Manager Dashboard',
            'team_lead'             => 'Team Dashboard',
            'telecalling_lead'      => 'Telecalling Dashboard',
            'sales_team_lead'       => 'Sales Team Dashboard',
            'support_lead'          => 'Support Dashboard',
            'senior_accountant'     => 'Senior Accountant Dashboard',
            'senior_developer'      => 'Senior Developer Dashboard',
            'legal_advisor'         => 'Legal Advisor Dashboard',
            'chartered_accountant'  => 'Chartered Accountant Dashboard',
            'accountant'            => 'Accountant Dashboard',
            'developer'             => 'Developer Dashboard',
            'content_writer'        => 'Content Writer Dashboard',
            'graphic_designer'      => 'Graphic Designer Dashboard',
            'data_entry_operator'   => 'Data Entry Dashboard',
            'backoffice_staff'      => 'Backoffice Dashboard',
            'telecalling_executive' => 'Telecalling Executive Dashboard',
            'support_executive'     => 'Support Executive Dashboard',
            'senior_associate'      => 'Senior Associate Dashboard',
            'associate_team_lead'   => 'Associate Team Lead Dashboard',
            'senior_agent'          => 'Senior Agent Dashboard',
            'franchise_owner'       => 'Franchise Owner Dashboard',
            'premium_customer'      => 'Premium Customer Dashboard',
            'verified_customer'     => 'Verified Customer Dashboard',
            'guest_customer'        => 'Customer Dashboard',
        ];

        $label = $roleLabels[$role] ?? 'Dashboard';
        $_SESSION['login_success'] = "Welcome! Redirecting to {$label}...";
        header('Location: ' . BASE_URL . $redirect);
    }

    /**
     * Get tenant SQL helper
     */
    private function getTenantSql(): array
    {
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log($e->getMessage()); }
        if ($tid > 1) return [" AND tenant_id = ?", [$tid]];
        return ["", []];
    }

    /* ── Rate Limiting Helpers (from CustomerAuthController) ── */

    private function isLockedOut($db, string $identifier): bool
    {
        $count = $this->getRecentAttempts($db, $identifier);
        return $count >= self::MAX_ATTEMPTS;
    }

    private function getLockoutRemaining($db, string $identifier): int
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $params = array_merge([$identifier], $tParams);
            $row = $db->fetchOne(
                "SELECT MAX(created_at) AS last_fail FROM login_attempts WHERE identifier = ? AND success = 0" . $tSql,
                $params
            );
            if (!$row || !$row['last_fail']) return 15;
            $elapsed = time() - strtotime($row['last_fail']);
            $remaining = max(0, self::LOCKOUT_WINDOW - $elapsed);
            return (int)ceil($remaining / 60);
        } catch (\Throwable $e) { return 15; }
    }

    private function getRecentAttempts($db, string $identifier): int
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $params = array_merge([$identifier, self::ATTEMPT_WINDOW], $tParams);
            $row = $db->fetchOne(
                "SELECT COUNT(*) AS cnt FROM login_attempts WHERE identifier = ? AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)" . $tSql,
                $params
            );
            return (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { return 0; }
    }

    private function logAttempt($db, string $identifier, bool $success): void
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $tenantCol = $tSql ? ", tenant_id" : "";
            $tenantVal = $tSql ? ", ?" : "";
            $params = array_merge([$identifier, $success ? 1 : 0, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)], $tParams);
            $db->execute(
                "INSERT INTO login_attempts (identifier, success, ip_address, user_agent, created_at" . $tenantCol . ") VALUES (?, ?, ?, ?, NOW()" . $tenantVal . ")",
                $params
            );
        } catch (\Throwable $e) {
            error_log("AuthController::logAttempt error: " . $e->getMessage());
        }
    }

    private function clearAttempts($db, string $identifier): void
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $params = array_merge([$identifier], $tParams);
            $db->execute("DELETE FROM login_attempts WHERE identifier = ?" . $tSql, $params);
        } catch (\Throwable $e) { error_log("AuthController::clearAttempts error: " . $e->getMessage()); }
    }

    public function verifyEmail()
    {
        View::render('auth/verify_email');
    }

    public function verifyEmailPost()
    {
        try {
            $db = Database::getInstance();
            $email = trim($_POST['email'] ?? '');
            $token = trim($_POST['token'] ?? '');

            if (empty($email) || empty($token)) {
                Flash::error(__('Email and token are required'));
                View::render('auth/verify_email');
                return;
            }

            [$tSql, $tParams] = $this->getTenantSql();
            $user = $db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND verify_token = ? AND verify_sent_at IS NOT NULL" . $tSql,
                array_merge([$email, $token], $tParams)
            );

            if (!$user) {
                Flash::error(__('Invalid verification link'));
                View::render('auth/verify_email');
                return;
            }

            $db->execute(
                "UPDATE users SET email_verified_at = NOW(), reset_token = NULL, reset_token_expiry = NULL WHERE id = ?",
                [$user['id']]
            );

            Flash::success(__('Email verified successfully'));
            Redirect::to('/login?verified=1');
        } catch (\Throwable $e) {
            error_log("AuthController::verifyEmailPost error: " . $e->getMessage());
            Flash::error(__('Verification failed'));
            View::render('auth/verify_email');
        }
    }
}
