<?php
/**
 * RegisterController — Standard MVC Registration
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;

class RegisterController extends BaseController
{
    private UserRegistrationService $regService;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new UserRegistrationService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

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

        // CAPTCHA validation
        $captcha_code = trim($_POST['captcha_code'] ?? '');
        if (empty($captcha_code)) {
            $errors[] = 'Security code is required';
        } else {
            require_once __DIR__ . '/../../../Helpers/SimpleCaptcha.php';
            if (!\SimpleCaptcha::validate($captcha_code)) {
                $errors[] = 'Invalid or expired security code. Please try again.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/register');
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
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Auto-login
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['customer_id'] = $result['user']['customer_id'];
            $_SESSION['user_name'] = $result['user']['name'];
            $_SESSION['user_email'] = $result['user']['email'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['logged_in'] = true;

            // Load role-specific IDs
            $db = \App\Core\Database\Database::getInstance();
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) {}
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$result['user_id']];
            if ($tid > 1) $params[] = $tid;

            if (in_array($role, ['agent', 'associate'], true)) {
                try {
                    $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                    if ($ass) {
                        $_SESSION['associate_id'] = (int)$ass['id'];
                        if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                    }
                } catch (\Throwable $e) {}
            } elseif ($role === 'employee' || $role === 'telecaller') {
                try {
                    $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tenantSql . " LIMIT 1", $params);
                    if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
                } catch (\Throwable $e) {}
            }

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
                error_log("[RegisterController] Welcome notification failed: " . $e->getMessage());
            }

            $_SESSION['success'] = $result['message'];
            $this->redirectToDashboard($role);
            exit;
        } catch (\Exception $e) {
            error_log("RegisterController registration error: " . $e->getMessage());
            $_SESSION['errors'] = ['Registration failed. Please try again.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
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
}
