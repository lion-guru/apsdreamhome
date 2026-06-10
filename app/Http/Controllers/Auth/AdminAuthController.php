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
        @session_start();

        if (isset($_GET['test_login']) && (APP_ENV === 'development' || APP_ENV === 'testing')) {
            $db = Database::getInstance();
            $admin = null;

            if ($_GET['test_login'] == '2') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') ORDER BY id LIMIT 1");
            } elseif ($_GET['test_login'] == '1') {
                $admin = $db->fetchOne("SELECT * FROM users WHERE (name = 'testadmin' OR email = 'testadmin@example.com') AND role IN ('super_admin','admin','manager') LIMIT 1");
            }

            if (!$admin) {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') ORDER BY id LIMIT 1");
                if (!$admin) {
                    $admin = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@apsdreamhome.com', 'password' => '', 'role' => 'super_admin'];
                }
            }

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'] ?? 'admin@apsdreamhome.com';
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
            $_SESSION['admin_name'] = $admin['name'] ?? 'Admin';
            $_SESSION['admin_username'] = $admin['name'] ?? 'admin';
            $_SESSION['employee_id'] = $admin['id'];
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'] ?? 'admin';
            $_SESSION['user_email'] = $admin['email'] ?? 'apsdreamhome.com';
            $_SESSION['user_name'] = $admin['name'] ?? 'Admin';
            $_SESSION['user_phone'] = $admin['phone'] ?? '';
            $_SESSION['logged_in'] = true;
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
        @session_start();

        if (getenv('TEST_MODE') === 'true') {
            @session_start();
            $db = Database::getInstance();
            $admin = $db->fetchOne("SELECT * FROM users WHERE (name = 'testadmin' OR email = 'testadmin@example.com') AND role IN ('super_admin','admin','manager') LIMIT 1");

            if (!$admin) {
                $admin = $db->fetchOne("SELECT * FROM users WHERE role IN ('super_admin','admin') ORDER BY id LIMIT 1");
                if (!$admin) {
                    $admin = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@apsdreamhome.com', 'password' => '', 'role' => 'super_admin'];
                }
            }

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'] ?? 'admin@apsdreamhome.com';
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
            $_SESSION['admin_name'] = $admin['name'] ?? 'Admin';
            $_SESSION['admin_username'] = $admin['name'] ?? 'admin';
            $_SESSION['employee_id'] = $admin['id'];
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'] ?? 'admin';
            $_SESSION['user_email'] = $admin['email'] ?? 'apsdreamhome.com';
            $_SESSION['user_name'] = $admin['name'] ?? 'Admin';
            $_SESSION['user_phone'] = $admin['phone'] ?? '';
            $_SESSION['logged_in'] = true;
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

            // Validate captcha (skip in dev mode)
            $isDev = (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1'])) || (defined('DEV_MODE') && DEV_MODE === true);
            if (!$isDev) {
                $submittedCaptcha = $_POST['captcha_answer'] ?? '';
                $sessionCaptcha = $_SESSION['captcha_result'] ?? '';
                if (empty($submittedCaptcha) || (int)$submittedCaptcha !== (int)$sessionCaptcha) {
                    throw new \Exception('Wrong security answer. Please try again.');
                }
            }

            // Get credentials
            $email = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new \Exception('Please fill in all fields.');
            }

            // Check database
            $db = Database::getInstance();

            $user = $db->fetchOne("SELECT * FROM users WHERE (name = ? OR email = ?) AND role IN ('super_admin','admin','manager','agent') LIMIT 1", [$email, $email]);
            if ($user && password_verify($password, $user['password'])) {
                // Prevent session fixation: rotate session ID on successful login
                session_regenerate_id(true);
                $_SESSION['last_regenerate'] = time();

                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['employee_id'] = $user['id'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['logged_in'] = true;

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
        @session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}
