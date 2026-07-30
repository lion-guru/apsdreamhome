<?php

/**
 * Customer Authentication Controller
 * Handles customer login, logout, and registration.
 *
 * Security: rate limiting, account lockout, password rehash, generic errors,
 * session fixation prevention, audit logging.
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;

class CustomerAuthController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    // ─── Config ───────────────────────────────────────────
    private const MAX_ATTEMPTS      = 5;      // lockout after 5 failures
    private const LOCKOUT_WINDOW    = 900;    // 15 minutes (seconds)
    private const ATTEMPT_WINDOW    = 900;    // track failures within 15 min
    private const THROTTLE_DELAY    = [1, 2, 4, 8, 16]; // progressive delay (seconds)

    // ─── Login Form ───────────────────────────────────────
    public function login()
    {
        @session_start();

        // If user is already logged in, redirect to appropriate dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'] ?? 'customer';
            $redirectMap = [
                'admin' => '/admin/dashboard',
                'super_admin' => '/admin/dashboard',
                'manager' => '/admin/dashboard',
                'employee' => '/employee/dashboard',
                'telecaller' => '/employee/dashboard',
                'associate' => '/associate/dashboard',
                'agent' => '/agent/dashboard',
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
                'department_manager' => '/admin/dashboard/sales',
                'project_manager' => '/admin/dashboard/operations',
                'sales_manager' => '/admin/dashboard/sales',
                'hr_manager' => '/admin/dashboard/hr',
                'marketing_manager' => '/admin/dashboard/marketing',
                'finance_manager' => '/admin/dashboard/finance',
                'property_manager' => '/admin/dashboard/operations',
                'it_manager' => '/admin/dashboard/it',
                'operations_manager' => '/admin/dashboard/operations',
                'team_lead' => '/admin/dashboard',
                'telecalling_lead' => '/admin/dashboard',
                'sales_team_lead' => '/admin/dashboard/sales',
                'support_lead' => '/admin/dashboard',
                'senior_accountant' => '/admin/dashboard/finance',
                'senior_developer' => '/admin/dashboard/it',
                'legal_advisor' => '/admin/dashboard/operations',
                'chartered_accountant' => '/admin/dashboard/finance',
                'accountant' => '/admin/dashboard/finance',
                'developer' => '/admin/dashboard/it',
                'content_writer' => '/admin/dashboard/marketing',
                'graphic_designer' => '/admin/dashboard/marketing',
                'data_entry_operator' => '/admin/dashboard',
                'backoffice_staff' => '/admin/dashboard',
                'telecalling_executive' => '/employee/dashboard',
                'support_executive' => '/employee/dashboard',
                'senior_associate' => '/associate/dashboard',
                'associate_team_lead' => '/associate/dashboard',
                'senior_agent' => '/agent/dashboard',
                'franchise_owner' => '/admin/dashboard/sales',
                'premium_customer' => '/user/dashboard',
                'verified_customer' => '/user/dashboard',
                'guest_customer' => '/user/dashboard',
            ];
            header('Location: ' . BASE_URL . ($redirectMap[$role] ?? '/user/dashboard'));
            exit;
        }

        // Redirect to unified login
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    // ─── Authenticate ─────────────────────────────────────
    public function authenticate()
    {
        @session_start();

        $email    = trim($_POST['identity'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ── Basic validation ──
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email/phone and password.';
            $_SESSION['login_old_email'] = $email;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $db = Database::getInstance();

        // ── Rate limiting: check lockout ──
        if ($this->isLockedOut($db, $email)) {
            $remaining = $this->getLockoutRemaining($db, $email);
            $_SESSION['login_locked'] = "Too many failed attempts. Please try again in {$remaining} minutes.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // ── Progressive throttle delay (non-blocking) ──
        $attempts = $this->getRecentAttempts($db, $email);
        if ($attempts > 0 && $attempts <= count(self::THROTTLE_DELAY)) {
            $requiredDelay = self::THROTTLE_DELAY[$attempts - 1];
            $lastAttempt = (int)($db->fetchOne(
                "SELECT MAX(created_at) as last FROM login_attempts WHERE identifier = ? AND success = 0",
                [$email]
            )['last'] ?? 0);
            $elapsed = time() - strtotime($lastAttempt);
            if ($elapsed < $requiredDelay) {
                $remaining = $requiredDelay - $elapsed;
                $_SESSION['login_error'] = "Too many attempts. Please wait {$remaining} seconds.";
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }

        try {
            $db = Database::getInstance();
            $tid = 1;
            try {
                $tid = \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
            }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$email, $email];
            if ($tid > 1) $params[] = $tid;

            // ── Generic error message (prevents email enumeration) ──
            $genericError = 'Invalid email/phone or password.';

            // ── Find user ──
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE (email = ? OR phone = ?) AND status = 'active' AND registration_status = 'approved'" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$user || !password_verify($password, $user['password'])) {
                // ── Log failed attempt ──
                $this->logAttempt($db, $email, false);

                $_SESSION['login_error'] = $genericError;
                $_SESSION['login_old_email'] = $email;
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            // ── Password rehash check (Argon2id cost upgrade) ──
            if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                $db->execute("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']]);
            }

            // ── Successful attempt: clear failure tracking ──
            $this->clearAttempts($db, $email);

            // ── 2FA check ──
            $twoFactor = $db->fetchOne(
                "SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ?",
                [$user['id']]
            );

            if ($twoFactor && !empty($twoFactor['two_factor_enabled']) && !empty($twoFactor['two_factor_secret'])) {
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

            // ── Session fixation prevention ──
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();

            // ── Set session variables ──
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
            $_SESSION['user_name']   = $user['name'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_phone']  = $user['phone'] ?? '';
            $_SESSION['role']        = $user['role'] ?? 'customer';
            $_SESSION['logged_in']   = true;

            // ── Role-specific session IDs ──
            $role = $_SESSION['role'];
            if ($role === 'employee') {
                $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
            } elseif (in_array($role, ['agent', 'associate'], true)) {
                $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                if ($ass) {
                    $_SESSION['associate_id'] = (int)$ass['id'];
                    if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                }
            }

            // ── Audit log ──
            $this->auditLog($db, 'login', $user['id'], $role, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);

            // ── Send login alert notifications (email + push + SMS on new device) ──
            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $loginNotifier->sendLoginAlerts(
                    (int)$user['id'],
                    $role,
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $isMobile,
                    'email'
                );
            } catch (\Throwable $e) {
                error_log("[CustomerAuth] Login notification failed: " . $e->getMessage());
            }

            // ── Role-based redirect ──
            $redirectMap = [
                'super_admin'  => '/admin/dashboard',
                'admin'        => '/admin/dashboard',
                'manager'      => '/admin/dashboard',
                'employee'     => '/employee/dashboard',
                'telecaller'   => '/employee/dashboard',
                'associate'    => '/associate/dashboard',
                'agent'        => '/agent/dashboard',
                'customer'     => '/user/dashboard',
                'ceo'  => '/admin/dashboard/ceo',
                'cfo'  => '/admin/dashboard/cfo',
                'cto'  => '/admin/dashboard/cto',
                'coo'  => '/admin/dashboard/coo',
                'cmo'  => '/admin/dashboard/cmo',
                'chro' => '/admin/dashboard/chro',
                'sales_director'       => '/admin/dashboard/sales',
                'marketing_director'   => '/admin/dashboard/marketing',
                'construction_director'=> '/admin/dashboard/operations',
                'finance_director'     => '/admin/dashboard/finance',
                'hr_director'          => '/admin/dashboard/hr',
                'department_manager'   => '/admin/dashboard/sales',
                'project_manager'      => '/admin/dashboard/operations',
                'sales_manager'        => '/admin/dashboard/sales',
                'hr_manager'           => '/admin/dashboard/hr',
                'marketing_manager'    => '/admin/dashboard/marketing',
                'finance_manager'      => '/admin/dashboard/finance',
                'property_manager'     => '/admin/dashboard/operations',
                'it_manager'           => '/admin/dashboard/it',
                'operations_manager'   => '/admin/dashboard/operations',
                'team_lead'            => '/admin/dashboard',
                'telecalling_lead'     => '/admin/dashboard',
                'sales_team_lead'      => '/admin/dashboard/sales',
                'support_lead'         => '/admin/dashboard',
                'senior_accountant'    => '/admin/dashboard/finance',
                'senior_developer'     => '/admin/dashboard/it',
                'legal_advisor'        => '/admin/dashboard/operations',
                'chartered_accountant' => '/admin/dashboard/finance',
                'accountant'           => '/admin/dashboard/finance',
                'developer'            => '/admin/dashboard/it',
                'content_writer'       => '/admin/dashboard/marketing',
                'graphic_designer'     => '/admin/dashboard/marketing',
                'data_entry_operator'  => '/admin/dashboard',
                'backoffice_staff'     => '/admin/dashboard',
                'telecalling_executive'=> '/employee/dashboard',
                'support_executive'    => '/employee/dashboard',
                'senior_associate'     => '/associate/dashboard',
                'associate_team_lead'  => '/associate/dashboard',
                'senior_agent'         => '/agent/dashboard',
                'franchise_owner'      => '/admin/dashboard/sales',
                'premium_customer'     => '/user/dashboard',
                'verified_customer'    => '/user/dashboard',
                'guest_customer'       => '/user/dashboard',
            ];
            $redirect = $redirectMap[$role] ?? '/admin/dashboard';
            $roleLabels = [
                'super_admin'  => 'Super Admin Panel',
                'admin'        => 'Admin Panel',
                'manager'      => 'Manager Dashboard',
                'employee'     => 'Employee Dashboard',
                'telecaller'   => 'Telecaller Dashboard',
                'associate'    => 'Associate Dashboard',
                'agent'        => 'Agent Dashboard',
                'customer'     => 'User Dashboard',
                'ceo'  => 'CEO Dashboard',
                'cfo'  => 'CFO Dashboard',
                'cto'  => 'CTO Dashboard',
                'coo'  => 'COO Dashboard',
                'cmo'  => 'CMO Dashboard',
                'chro' => 'CHRO Dashboard',
                'sales_director'       => 'Sales Director Dashboard',
                'marketing_director'   => 'Marketing Director Dashboard',
                'construction_director'=> 'Construction Director Dashboard',
                'finance_director'     => 'Finance Director Dashboard',
                'hr_director'          => 'HR Director Dashboard',
                'department_manager'   => 'Department Dashboard',
                'project_manager'      => 'Projects Dashboard',
                'sales_manager'        => 'Sales Dashboard',
                'hr_manager'           => 'HR Dashboard',
                'marketing_manager'    => 'Marketing Dashboard',
                'finance_manager'      => 'Finance Dashboard',
                'property_manager'     => 'Property Dashboard',
                'it_manager'           => 'IT Dashboard',
                'operations_manager'   => 'Operations Dashboard',
                'team_lead'            => 'Team Dashboard',
                'telecalling_lead'     => 'Telecalling Dashboard',
                'sales_team_lead'      => 'Sales Team Dashboard',
                'support_lead'         => 'Support Dashboard',
                'senior_accountant'    => 'Finance Dashboard',
                'senior_developer'     => 'IT Dashboard',
                'legal_advisor'        => 'Legal Dashboard',
                'chartered_accountant' => 'Finance Dashboard',
                'accountant'           => 'Finance Dashboard',
                'developer'            => 'IT Dashboard',
                'content_writer'       => 'Marketing Dashboard',
                'graphic_designer'     => 'Marketing Dashboard',
                'data_entry_operator'  => 'Admin Dashboard',
                'backoffice_staff'     => 'Admin Dashboard',
                'telecalling_executive'=> 'Telecaller Dashboard',
                'support_executive'    => 'Support Dashboard',
                'senior_associate'     => 'Associate Dashboard',
                'associate_team_lead'  => 'Associate Dashboard',
                'senior_agent'         => 'Agent Dashboard',
                'franchise_owner'      => 'Franchise Dashboard',
                'premium_customer'     => 'User Dashboard',
                'verified_customer'    => 'User Dashboard',
                'guest_customer'       => 'User Dashboard',
            ];
            $label = $roleLabels[$role] ?? 'Dashboard';
            $_SESSION['login_success'] = "Welcome! Redirecting to {$label}...";
            header('Location: ' . BASE_URL . $redirect);
            exit;

        } catch (\Throwable $e) {
            error_log("[CustomerAuth] Login error: " . $e->getMessage());
            $_SESSION['login_error'] = 'Something went wrong. Please try again.';
            $_SESSION['login_old_email'] = $email;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    // ─── Registration ─────────────────────────────────────
    public function register()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        $ref = trim($_GET['ref'] ?? $old['referral_code'] ?? '');
        unset($_SESSION['errors'], $_SESSION['old_input']);

        $this->layout = false;
        ob_start();
        extract(compact('csrf_token', 'errors', 'old', 'ref'));
        $viewPath = __DIR__ . '/../../../views/auth/customer_register.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    public function handleRegister()
    {
        @session_start();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['referral_code'] ?? $_GET['ref'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        try {
            $regService = new UserRegistrationService();
            $result = $regService->createUser('customer', [
                'name' => $name, 'email' => $email, 'phone' => $phone,
                'password' => $password, 'referral_code' => $referral,
                'registration_method' => 'web',
            ]);

            if (!$result['success']) {
                $_SESSION['errors'] = [$result['message']];
                $_SESSION['old_input'] = $_POST;
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // ── Send welcome notifications (email + SMS + push + WhatsApp) ──
            try {
                $newUserId = $result['user_id'] ?? $result['id'] ?? null;
                if ($newUserId) {
                    require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                    $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                    $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                    $loginNotifier->sendWelcomeNotifications(
                        (int)$newUserId, $name, $email, $phone, 'customer', $isMobile
                    );
                }
            } catch (\Throwable $e) {
                error_log("[CustomerAuth] Welcome notification failed: " . $e->getMessage());
            }

            $_SESSION['login_success'] = 'Account created! Please sign in.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } catch (\Throwable $e) {
            error_log("[CustomerAuth] Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed. Please try again."];
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
    }

    // ─── Logout (secure) ──────────────────────────────────
    public function logout()
    {
        @session_start();

        // Audit before destroying
        if (!empty($_SESSION['user_id'])) {
            try {
                $db = Database::getInstance();
                $this->auditLog($db, 'logout', $_SESSION['user_id'], $_SESSION['role'] ?? 'customer');
            } catch (\Throwable $e) { error_log("CustomerAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
        }

        // Clear all session data
        $_SESSION = [];

        // Clear remember-me cookie if present
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }

        // Destroy session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    // ─── Rate Limiting Helpers ─────────────────────────────

    private function getTenantSql(): array
    {
        $tid = 1;
        try {
            $tid = \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
        }
        if ($tid > 1) {
            return [" AND tenant_id = ?", [$tid]];
        }
        return ["", []];
    }

    /**
     * Check if account is locked out due to too many failed attempts.
     */
    private function isLockedOut($db, string $identifier): bool
    {
        $count = $this->getRecentAttempts($db, $identifier);
        return $count >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining lockout time in minutes.
     */
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
        } catch (\Throwable $e) {
            return 15;
        }
    }

    /**
     * Count failed attempts in the lockout window.
     */
    private function getRecentAttempts($db, string $identifier): int
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $params = array_merge([$identifier, self::LOCKOUT_WINDOW], $tParams);
            $row = $db->fetchOne(
                "SELECT COUNT(*) AS cnt FROM login_attempts WHERE identifier = ? AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)" . $tSql,
                $params
            );
            return (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Record a login attempt (success or failure).
     */
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
            // Table may not exist — degrade gracefully
        }
    }

    /**
     * Clear failed attempts after successful login.
     */
    private function clearAttempts($db, string $identifier): void
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $params = array_merge([$identifier], $tParams);
            $db->execute("DELETE FROM login_attempts WHERE identifier = ?" . $tSql, $params);
        } catch (\Throwable $e) { error_log("CustomerAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
    }

    // ─── Audit Logger ──────────────────────────────────────
    private function auditLog($db, string $action, int $userId, string $role, array $context = []): void
    {
        try {
            require_once __DIR__ . '/../../../Services/AuditService.php';
            $audit = new \App\Services\AuditService($db);
            $audit->log($action, $userId, $role, 'user', $userId, ucfirst($action), $context);
        } catch (\Throwable $e) { error_log("CustomerAuthController::" . __FUNCTION__ . " error: " . $e->getMessage()); }
    }

    // ─── Redirect Map ──────────────────────────────────────
    private function getRedirectUrl(string $role): string
    {
        $map = [
            'super_admin'  => '/admin/dashboard',
            'admin'        => '/admin/dashboard',
            'manager'      => '/admin/dashboard',
            'employee'     => '/employee/dashboard',
            'telecaller'   => '/admin/dashboard',
            'associate'    => '/associate/dashboard',
            'agent'        => '/agent/dashboard',
            'customer'     => '/user/dashboard',
        ];
        return $map[$role] ?? '/user/dashboard';
    }
}
