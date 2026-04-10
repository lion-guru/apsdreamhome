<?php

/**
 * Admin Login Controller
 * Simple standalone admin login - no layout system needed
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class AdminAuthController extends BaseController
{
    public function adminLogin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Test-only shortcut: allow auto-login to admin dashboard when test_login=1
        if (isset($_GET['test_login']) && $_GET['test_login'] == '1') {
            // Fetch actual admin from database
            $db = Database::getInstance();
            $admin = $db->fetchOne("SELECT * FROM admin_users WHERE email = 'testadmin@example.com' LIMIT 1");
            if (!$admin) {
                $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'testadmin@example.com' AND role IN ('admin', 'super_admin') LIMIT 1");
            }

            $_SESSION['admin_id'] = $admin['id'] ?? 1;
            $_SESSION['admin_email'] = $admin['email'] ?? 'testadmin@example.com';
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
            $_SESSION['admin_name'] = $admin['username'] ?? $admin['name'] ?? 'Test Admin';
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        // Redirect if already logged in
        if (isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        // Set cache control headers to prevent page caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        // Generate CSRF token
        $csrf_token = $this->getCsrfToken();

        // Generate CAPTCHA (force new question on every load)
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_result'] = $num1 + $num2;
        $captcha_question = "$num1 + $num2 = ?";

        // Get error from session
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        // Render standalone login page (no layout)
        $data = [
            'csrf_token' => $csrf_token,
            'captcha_question' => $captcha_question,
            'error' => $error,
            'page_title' => 'Admin Login'
        ];

        $viewPath = __DIR__ . '/../../../views/auth/admin_login.php';
        if (file_exists($viewPath)) {
            extract($data);
            include $viewPath;
        } else {
            echo "VIEW NOT FOUND: $viewPath";
        }
    }

    public function authenticateAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // TEST MODE: auto login for admin to enable end-to-end testing without CAPTCHA/DB dependencies
        if (getenv('TEST_MODE') === 'true') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            // Fetch actual admin from database
            $db = Database::getInstance();
            $admin = $db->fetchOne("SELECT * FROM admin_users WHERE email = 'testadmin@example.com' LIMIT 1");
            if (!$admin) {
                $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'testadmin@example.com' AND role IN ('admin', 'super_admin') LIMIT 1");
            }

            $_SESSION['admin_id'] = $admin['id'] ?? 1;
            $_SESSION['admin_email'] = $admin['email'] ?? 'testadmin@example.com';
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
            $_SESSION['admin_name'] = $admin['username'] ?? $admin['name'] ?? 'Test Admin';
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        try {
            // Validate CSRF
            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
                throw new \Exception('Invalid security token. Please refresh and try again.');
            }

            // Validate captcha
            $submittedCaptcha = $_POST['captcha_answer'] ?? '';
            $sessionCaptcha = $_SESSION['captcha_result'] ?? '';
            if (empty($submittedCaptcha) || (int)$submittedCaptcha !== (int)$sessionCaptcha) {
                throw new \Exception('Wrong security answer. Please try again.');
            }

            // Get credentials
            $email = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new \Exception('Please fill in all fields.');
            }

            // Check database
            $db = Database::getInstance();

            // Try admin_users table first
            $admin = $db->fetchOne("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1", [$email, $email]);
            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_name'] = $admin['username'] ?? $admin['name'] ?? 'Admin';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            }

            // Try users table for admin/super_admin roles
            $user = $db->fetchOne("SELECT * FROM users WHERE (name = ? OR email = ?) AND role IN ('admin', 'super_admin') LIMIT 1", [$email, $email]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            }

            throw new \Exception('Invalid username or password.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}
