<?php
/**
 * CoreAuthController — Single unified auth endpoint for all roles.
 *
 * Features:
 * - Unified register (role cards → UserRegistrationService)
 * - Unified login (email/phone → password → role-based redirect)
 * - Smart Registration integration (phone-first OTP)
 * - Role selection step for Smart Registration
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;
use App\Services\WalletService;
use App\Services\ReferralService;

class CoreAuthController extends BaseController
{
    private UserRegistrationService $regService;
    private WalletService $walletService;
    private ReferralService $referralService;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new UserRegistrationService();
        $this->walletService = new WalletService();
        $this->referralService = new ReferralService();
    }

    // ============================================================
    // Unified Registration
    // ============================================================

    /**
     * Show unified register page with role selection cards
     */
    public function showRegister()
    {
        @session_start();
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['role'] ?? 'customer');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['success']);

        $ref = trim($_GET['ref'] ?? $old['referral_code'] ?? '');
        $selectedRole = trim($_GET['role'] ?? $old['role'] ?? 'customer');

        include __DIR__ . '/../../../views/auth/core_register.php';
    }

    /**
     * Handle unified registration (POST)
     */
    public function handleRegister()
    {
        @session_start();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $role = trim($_POST['role'] ?? 'customer');
        $referral = trim($_POST['referral_code'] ?? $_GET['ref'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Valid 10-digit phone required';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
        if ($password !== $confirm) $errors[] = 'Passwords do not match';
        if (!in_array($role, ['customer', 'associate', 'agent'], true)) $errors[] = 'Invalid role selected';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        try {
            $result = $this->regService->createUser($role, [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $referral,
                'registration_method' => 'web',
            ]);

            if (!$result['success']) {
                $_SESSION['errors'] = [$result['message']];
                $_SESSION['old_input'] = $_POST;
                header('Location: ' . BASE_URL . '/auth/register');
                exit;
            }

            // Auto-login
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['customer_id'] = $result['user']['customer_id'];
            $_SESSION['user_name'] = $result['user']['name'];
            $_SESSION['user_email'] = $result['user']['email'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['logged_in'] = true;

            // Mark visitor as converted
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($result['user_id']);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }

            // ── Send welcome notifications ──
            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $loginNotifier->sendWelcomeNotifications(
                    (int)$result['user_id'], $name, $email, $phone, $role, $isMobile
                );
            } catch (\Throwable $e) {
                error_log("[CoreAuth] Welcome notification failed: " . $e->getMessage());
            }

            $_SESSION['success'] = $result['message'];
            $this->redirectToDashboard($role);
            exit;
        } catch (\Exception $e) {
            error_log("CoreAuth registration error: " . $e->getMessage());
            $_SESSION['errors'] = ['Registration failed. Please try again.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }
    }

    // ============================================================
    // Unified Login
    // ============================================================

    /**
     * Show unified login page
     */
    public function showLogin()
    {
        @session_start();
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
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE (email = ? OR phone = ?) AND status != 'deleted' LIMIT 1",
                [$identity, $identity]
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
                    $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ? LIMIT 1", [$user['id']]);
                    if ($ass) {
                        $_SESSION['associate_id'] = (int)$ass['id'];
                        if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                    }
                } catch (\Throwable $e) { error_log("CoreAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
            } elseif ($role === 'employee' || $role === 'telecaller') {
                try {
                    $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ? LIMIT 1", [$user['id']]);
                    if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
                } catch (\Throwable $e) { error_log("CoreAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
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
            } catch (\Throwable $e) { error_log("CoreAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }

            // ── Send login alert notifications ──
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
                error_log("[CoreAuth] Login notification failed: " . $e->getMessage());
            }

            $this->redirectToDashboard($role);
            exit;
        } catch (\Exception $e) {
            error_log("CoreAuth login error: " . $e->getMessage());
            $_SESSION['errors'] = ['Login failed. Please try again.'];
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    // ============================================================
    // Smart Registration — Role Selection
    // ============================================================

    /**
     * Show role selection page for Smart Registration
     * Called after OTP verification, before profile completion
     */
    public function showRoleSelection()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }

        try {
            $db = Database::getInstance();
            $session = $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ? LIMIT 1",
                [$token]
            );
            if (!$session || !$session['user_id']) {
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }

            $csrf_token = $this->getCsrfToken();
            include __DIR__ . '/../../../views/auth/smart_register_role.php';
        } catch (\Exception $e) {
            error_log("Role selection error: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
    }

    /**
     * Save role selection from Smart Registration (POST)
     */
    public function saveRoleSelection()
    {
        @session_start();
        $token = $_POST['token'] ?? '';
        $role = trim($_POST['role'] ?? 'customer');

        if (!in_array($role, ['customer', 'associate', 'agent'], true)) {
            $role = 'customer';
        }

        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }

        try {
            $db = Database::getInstance();
            $session = $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ? LIMIT 1",
                [$token]
            );
            if (!$session || !$session['user_id']) {
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }

            // Update user role
            $db->query(
                "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?",
                [$role, $session['user_id']]
            );

            // If MLM role, create mlm_profiles + network_tree + associates (if not already created)
            if (in_array($role, ['associate', 'agent'], true)) {
                $existingProfile = $db->fetchOne("SELECT id FROM mlm_profiles WHERE user_id = ? LIMIT 1", [$session['user_id']]);
                if (!$existingProfile) {
                    $user = $db->fetchOne("SELECT name, email, phone, referral_code, referred_by FROM users WHERE id = ?", [$session['user_id']]);
                    if ($user) {
                        $this->regService = new UserRegistrationService();
                        // We need to call the private methods... Let's use reflection or just inline
                        $this->createMlmRecordsForExistingUser($session['user_id'], $user, $role);
                    }
                }
            }

            // Update session
            $db->query(
                "UPDATE smart_registration_sessions SET selected_role = ?, registration_status = CASE WHEN ? IN ('associate','agent') THEN 'role_selected' ELSE 'profile_incomplete' END, updated_at = NOW() WHERE id = ?",
                [$role, $role, $session['id']]
            );

            // Update session role
            $_SESSION['role'] = $role;

            header('Location: ' . BASE_URL . '/register/smart/profile-complete?token=' . urlencode($token) . '&role=' . urlencode($role));
            exit;
        } catch (\Exception $e) {
            error_log("Save role selection error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to save role selection';
            header('Location: ' . BASE_URL . '/register/smart/role?token=' . urlencode($token));
            exit;
        }
    }

    // ============================================================
    // Logout
    // ============================================================

    public function logout()
    {
        @session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    // ============================================================
    // Helpers
    // ============================================================

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

    /**
     * Create MLM records for an existing user (Smart Registration upgrade path)
     */
    private function createMlmRecordsForExistingUser(int $userId, array $user, string $role): void
    {
        $db = Database::getInstance();
        $referralCode = $user['referral_code'] ?? ('REF' . $userId . date('ymd'));
        $sponsorId = !empty($user['referred_by']) ? (int)$user['referred_by'] : null;

        try {
            $db->beginTransaction();

            // mlm_profiles
            $existing = $db->fetchOne("SELECT id FROM mlm_profiles WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$existing) {
                $db->insert('mlm_profiles', [
                    'user_id' => $userId,
                    'referral_code' => $referralCode,
                    'sponsor_user_id' => $sponsorId,
                    'user_type' => $role,
                    'current_level' => 'associate',
                    'total_team_size' => 0,
                    'direct_referrals' => 0,
                    'total_commission' => 0.00,
                    'pending_commission' => 0.00,
                    'lifetime_sales' => 0.00,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // network_tree
            $existing = $db->fetchOne("SELECT id FROM network_tree WHERE associate_id = ? LIMIT 1", [$userId]);
            if (!$existing) {
                $rootId = $userId;
                $parentId = $sponsorId;
                $level = 1;
                $position = 'left';

                if ($sponsorId) {
                    $sponsorTree = $db->fetchOne("SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1", [$sponsorId]);
                    if ($sponsorTree) {
                        $rootId = (int)$sponsorTree['root_id'];
                        $parentId = $sponsorId;
                        $level = (int)$sponsorTree['level'] + 1;
                        $leftCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'", [$sponsorId]);
                        $rightCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'", [$sponsorId]);
                        $position = $leftCount <= $rightCount ? 'left' : 'right';
                    }
                }

                $db->insert('network_tree', [
                    'associate_id' => $userId,
                    'root_id' => $rootId,
                    'parent_id' => $parentId,
                    'level' => $level,
                    'position' => $position,
                    'total_left_count' => 0,
                    'total_right_count' => 0,
                    'personal_bv' => 0.00,
                    'is_active' => 1,
                    'joined_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Also insert into mlm_network_tree (used by commission engines)
                $mlmParentId = $parentId ?? $userId;
                $db->insert('mlm_network_tree', [
                    'associate_id' => $userId,
                    'sponsor_id' => $sponsorId,
                    'parent_id' => $mlmParentId,
                    'level' => $level,
                ]);
            }

            // associates
            $existing = $db->fetchOne("SELECT id FROM associates WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$existing) {
                $db->insert('associates', [
                    'user_id' => $userId,
                    'name' => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'referral_code' => $referralCode,
                    'sponsor_id' => $sponsorId,
                    'level' => $role === 'agent' ? 'agent' : 'associate',
                    'status' => 'active',
                    'joining_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Update sponsor's direct_referrals
            if ($sponsorId) {
                $db->query(
                    "UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1, total_team_size = total_team_size + 1, updated_at = NOW() WHERE user_id = ?",
                    [$sponsorId]
                );
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("createMlmRecordsForExistingUser error: " . $e->getMessage());
        }
    }
}
