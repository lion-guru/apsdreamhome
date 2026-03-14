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
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

        // Generate CSRF token
        $csrf_token = $this->getCsrfToken();

        // Generate CAPTCHA
        $captcha = $this->generateCaptcha();
        $captcha_question = $captcha['question'];

        // Include admin login view
        include_once __DIR__ . '/../../../views/auth/admin_login.php';
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

            // Validate input
            if (empty($email) || empty($password)) {
                throw new \Exception('Please fill in all fields');
            }

            // Simple admin authentication (for demo)
            if ($email === 'admin@apsdreamhome.com' && $password === 'admin123') {
                // Store admin data in session
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_email'] = 'admin@apsdreamhome.com';
                $_SESSION['admin_role'] = 'admin';
                $_SESSION['admin_name'] = 'Administrator';
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = $this->getCsrfToken();

                // Redirect to dashboard
                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            } else {
                throw new \Exception('Invalid username or password');
            }
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

    /**
     * Logout admin
     */
    public function logout()
    {
        // Clear session
        session_destroy();

        // Redirect to login
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken()
    {
        // Implement CSRF token generation logic here
        return 'csrf-token';
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken($token)
    {
        // Implement CSRF token validation logic here
        return true;
    }
}
