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
            $this->redirectBasedOnRole();
        }

        $this->data['page_title'] = 'Admin Login - ' . APP_NAME;
        $this->data['error'] = $this->getFlash('error') ?? ($_GET['error'] ?? '');
        $this->data['success'] = $this->getFlash('success') ?? ($_GET['success'] ?? '');

        return $this->render('admin/login');
    }

    /**
     * Process admin login form submission
     */
    public function processLogin()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Please fill in all fields');
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
            $admin = $adminModel->query()
                ->where('auser', '=', $username)
                ->orWhere('aemail', '=', $username)
                ->first();

            if (!$admin) {
                return $this->handleFailedLogin($username);
            }

            // Check admin status
            if ($admin->status !== 'active') {
                $this->setFlash('error', 'Account is not active');
                $this->redirect('/admin/login');
            }

            // Verify password
            if (!password_verify($password, $admin->apass)) {
                // Fallback for SHA1 if needed (legacy)
                if (preg_match('/^[a-f0-9]{40}$/i', $admin->apass) && sha1($password) === $admin->apass) {
                    // Valid SHA1, will rehash below
                } else {
                    return $this->handleFailedLogin($username);
                }
            }

            // Successful login
            // Check if password needs rehash
            if (password_needs_rehash($admin->apass, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $adminModel->update($admin->aid, ['apass' => $new_hash]);
            }

            // Set session using unified helper
            // Note: the helper uses an array, so we convert the model object to array if needed
            $userData = (array)$admin;
            \App\Services\Legacy\setAuthSession($userData, 'admin', $admin->role ?? 'admin');

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Log success
            error_log("[Admin Login Success] User: " . $admin->auser . " IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            $this->setFlash('success', 'Logged in successfully');
            $this->redirect($this->getDashboardForRole($admin->role ?? 'admin'));

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
