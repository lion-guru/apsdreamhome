<?php

/**
 * Admin Authentication Controller
 * Handles admin-specific login, session management, and role-based redirects.
 * Migrated from legacy admin_login_handler.php.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Helpers\AuthHelper;
use App\Helpers\SecurityHelper;
use App\Core\App;
use Exception;

use App\Services\Legacy\SessionHelpers;

class AdminAuthController extends BaseController
{
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['processLogin']]);
    }

    /**
     * Display admin login page
     */
    public function adminLogin()
    {
        if (AuthHelper::isLoggedIn('admin')) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->data['page_title'] = 'Admin Login - APS Dream Home';
        $this->data['error'] = $this->getFlash('error') ?? ($_GET['error'] ?? '');
        $this->data['success'] = $this->getFlash('success') ?? ($_GET['success'] ?? '');

        // Generate simple CAPTCHA
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_num1_admin'] = $num1;
        $_SESSION['captcha_num2_admin'] = $num2;
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $this->data['captcha_question'] = "$num1 + $num2 = ?";

        // Generate CSRF token if not already in view
        if (!isset($this->data['csrf_token'])) {
            $this->data['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $this->render('admin/login');
    }

    /**
     * Process admin login form submission
     */
    public function authenticateAdmin()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha = $_POST['captcha_answer'] ?? '';

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Please fill in all fields');
            $this->redirect('/admin/login');
        }

        // Verify CAPTCHA
        if (!isset($_SESSION['captcha_answer']) || (int)$captcha !== (int)$_SESSION['captcha_answer']) {
            $this->setFlash('error', 'Incorrect security answer');
            $this->redirect('/admin/login');
        }

        // Check if account is locked
        if (isset($_SESSION['admin_login_blocked_until']) && $_SESSION['admin_login_blocked_until'] > time()) {
            $this->setFlash('error', 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']));
            $this->redirect('/admin/login');
        }

        try {
            $adminModel = new \App\Models\System\Admin();
            // Fetch admin from database using email or username
            $admin = $adminModel->findByUsernameOrEmail($username);

            if (!$admin) {
                return $this->handleFailedLogin($username);
            }

            // Verify password using Admin model method
            $verified = $adminModel->verifyPassword($admin, $password);

            if (!$verified) {
                return $this->handleFailedLogin($username);
            }

            // Create admin session
            $adminModel->createAdminSession($admin);

            // Clear failed login attempts
            unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_blocked_until']);

            $this->setFlash('success', 'Welcome back, ' . ($admin['name'] ?? $admin['username'] ?? 'Admin') . '!');
            $this->redirect('/admin/dashboard');
            return;
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            $this->setFlash('error', 'Login failed. Please try again.');
            $this->redirect('/admin/login');
        }
    }

    /**
     * Handle failed login attempt
     */
    private function handleFailedLogin($username)
    {
        $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;
        if ($_SESSION['admin_login_attempts'] >= 5) {
            $_SESSION['admin_login_blocked_until'] = time() + 600; // 10 minutes
        }
        error_log("[Admin Login Failed] User: $username IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

        $this->setFlash('error', 'Invalid username or password');
        $this->redirect('/admin/login');
    }

    /**
     * Get dashboard URL based on role
     */
    private function getDashboardForRole($role)
    {
        $role_dashboard_map = [
            'superadmin' => '/admin/superadmin_dashboard.php',
            'admin' => '/admin/dashboard.php',
            'manager' => '/admin/manager_dashboard.php',
            'sales' => '/admin/sales_dashboard.php',
            'hr' => '/admin/hr_dashboard.php',
            'finance' => '/admin/finance_dashboard.php'
        ];

        return $role_dashboard_map[$role] ?? '/admin/dashboard.php';
    }

    /**
     * Log out admin
     */
    public function logout()
    {
        // Use unified logout if available
        if (class_exists('\App\Services\Legacy\session_helpers')) {
            \App\Services\Legacy\session_helpers::destroyAuthSession();
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            session_destroy();
        }

        $this->redirect('/admin/login');
    }
}
