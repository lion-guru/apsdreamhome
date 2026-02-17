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
    public function showLogin()
    {
        if (AuthHelper::isLoggedIn('admin')) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->data['page_title'] = 'Admin Login - ' . APP_NAME;
        $this->data['error'] = $this->getFlash('error') ?? ($_GET['error'] ?? '');
        $this->data['success'] = $this->getFlash('success') ?? ($_GET['success'] ?? '');

        // Generate simple CAPTCHA
        $num1 = SecurityHelper::secureRandomInt(1, 10);
        $num2 = SecurityHelper::secureRandomInt(1, 10);
        $_SESSION['captcha_num1_admin'] = $num1;
        $_SESSION['captcha_num2_admin'] = $num2;
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $this->data['captcha_question'] = "$num1 + $num2 = ?";

        // Generate CSRF token if not already in view
        if (!isset($this->data['csrf_token'])) {
            $this->data['csrf_token'] = SecurityHelper::generateCsrfToken();
        }

        return $this->render('admin/login');
    }

    /**
     * Process admin login form submission
     */
    public function processLogin()
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
            $adminModel = new Admin();
            // Fetch admin from database using email or username
            $admin = $adminModel->findByUsernameOrEmail($username);

            if (!$admin) {
                return $this->handleFailedLogin($username);
            }

            // Verify password with support for multiple hash columns and legacy SHA1
            $hash = $admin->apass ?? $admin->password;
            $verified = false;

            if ($hash && password_verify($password, $hash)) {
                $verified = true;
            } elseif ($hash && preg_match('/^[a-f0-9]{40}$/i', $hash) && sha1($password) === $hash) {
                // Legacy SHA1 support
                $verified = true;

                // Rehash password to bcrypt and update database
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $admin->password = $newHash;

                // If using apass column for legacy compatibility, update it too if it exists in fillable
                if ($admin->apass) {
                    $admin->apass = $newHash;
                }

                $admin->save();
            }

            if (!$verified) {
                return $this->handleFailedLogin($username);
            }

            // Check admin status
            if (isset($admin->status) && $admin->status !== 'active') {
                $this->setFlash('error', 'Account is not active');
                $this->redirect('/admin/login');
            }

            // Successful login
            // Check if password needs rehash
            if (password_needs_rehash($admin->apass, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $adminModel->update($admin->aid, ['apass' => $new_hash]);
            }

            // Set session using unified helper
            // Note: the helper uses an array, so we convert the model object to array if needed
            $userData = $admin->toArray();
            // Use static method for unified session handling
            SessionHelpers::setAuthSession($userData, 'admin', $admin->role ?? 'admin');

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Log success
            error_log("[Admin Login Success] User: " . $admin->auser . " IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            $this->setFlash('success', 'Logged in successfully');
            $this->redirect('/admin/dashboard');
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            $this->setFlash('error', 'An unexpected error occurred');
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
