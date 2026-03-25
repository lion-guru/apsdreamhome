<?php

/**
 * Admin Authentication Controller
 * Handles admin-specific login, session management, and role-based redirects.
 * Migrated from legacy admin_login_handler.php.
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use Exception;

class AdminAuthController extends BaseController
{
    /**
     * Show admin login page
     */
    public function adminLogin()
    {
        $this->layout = 'layouts/admin';
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

        // Generate CSRF token
        $csrf_token = $this->getCsrfToken();

        // Generate CAPTCHA
        $captcha = $this->generateCaptcha();
        $captcha_question = $captcha['question'];
        
        $data = [
            'page_title' => 'Admin Login - APS Dream Home',
            'page_description' => 'Secure admin access to APS Dream Home dashboard',
            'active_page' => 'login',
            'csrf_token' => $csrf_token,
            'captcha_question' => $captcha_question
        ];

        $this->render('auth/admin_login', $data);
    }

    /**
     * Handle admin login authentication
     */
    public function authenticateAdmin()
    {
        try {
            // Start session if not started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Validate CSRF token
            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
                throw new \Exception('Invalid CSRF token');
            }

            // Get credentials
            $email = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate captcha
            $submittedCaptcha = $_POST['captcha'] ?? '';
            $sessionCaptcha = $_SESSION['captcha_result'] ?? '';

            if (empty($submittedCaptcha) || empty($sessionCaptcha) || $submittedCaptcha != $sessionCaptcha) {
                throw new \Exception('Invalid security answer. Please try again.');
            }

            // Validate input
            if (empty($email) || empty($password)) {
                throw new \Exception('Please fill in all fields');
            }

            // Authenticate against database
            $this->db = \App\Core\Database\Database::getInstance();

            // Check admin users table first
            $adminQuery = "SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1";
            $adminUser = $this->db->fetchOne($adminQuery, [$email, $email]);

            if ($adminUser && password_verify($password, $adminUser['password_hash'])) {
                // Admin authentication successful
                $_SESSION['admin_id'] = $adminUser['id'];
                $_SESSION['admin_email'] = $adminUser['email'];
                $_SESSION['admin_role'] = $adminUser['role'];
                $_SESSION['admin_name'] = $adminUser['username'];
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = $this->getCsrfToken();

                // Redirect to dashboard
                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            }

            // Check users table for admin roles
            $userQuery = "SELECT * FROM users WHERE (name = ? OR email = ?) AND role IN ('admin', 'super_admin') LIMIT 1";
            $user = $this->db->fetchOne($userQuery, [$email, $email]);

            if ($user && password_verify($password, $user['password'])) {
                // User with admin role authentication successful
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = $this->getCsrfToken();

                // Redirect to dashboard
                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            }

            throw new \Exception('Invalid username or password');
        } catch (\Exception $e) {
            // Log failed attempt
            $this->logLoginAttempt($_POST['email'] ?? '', false, $e->getMessage());

            // Show error and reload login page
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    /**
     * Logout admin
     */
    public function logout()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Destroy session
        session_destroy();

        // Redirect to login
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }

    /**
     * Check if admin is logged in
     */
    public function isLoggedIn(): bool
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Redirect to admin dashboard
     */
    private function redirectToDashboard()
    {
        header('Location: ' . BASE_URL . '/admin/dashboard');
        exit;
    }

    /**
     * Log login attempt for security
     */
    private function logLoginAttempt($email, $success, $message = '')
    {
        $logData = [
            'email' => $email,
            'success' => $success,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        error_log("Admin Login Attempt: " . json_encode($logData));
    }

    /**
     * Generate simple math CAPTCHA
     */
    private function generateCaptcha()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $result = $num1 + $num2;

        $_SESSION['captcha_result'] = $result;

        return [
            'question' => "$num1 + $num2 = ?",
            'result' => $result
        ];
    }


}
