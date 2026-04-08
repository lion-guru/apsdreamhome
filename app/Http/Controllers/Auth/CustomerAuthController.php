<?php
/**
 * Customer Authentication Controller
 * Handles customer registration and login
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class CustomerAuthController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Redirect if already logged in
        if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? '') === 'customer') {
            header('Location: ' . BASE_URL . '/customer/dashboard');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error']);
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        // Render standalone
        $this->layout = false;
        ob_start();
        extract(compact('csrf_token', 'error', 'success'));
        $viewPath = __DIR__ . '/../../../views/auth/customer_login.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    public function authenticate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email = trim($_POST['identity'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            $user = $db->fetchOne("SELECT * FROM users WHERE (email = ? OR phone = ?) AND status = 'active' LIMIT 1", [$email, $email]);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'customer';
                $_SESSION['logged_in'] = true;

                header('Location: ' . BASE_URL . '/customer/dashboard');
                exit;
            } else {
                // Also check if user exists but wrong password
                $exists = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
                if (!$exists) {
                    // Auto-register from login if user doesn't exist
                    $_SESSION['errors'] = ["Account not found. Please register first."];
                } else {
                    $_SESSION['errors'] = ["Invalid password"];
                }
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Customer login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function register()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        $this->layout = false;
        ob_start();
        extract(compact('csrf_token', 'errors', 'old'));
        $viewPath = __DIR__ . '/../../../views/auth/customer_register.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    public function handleRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['referral_code'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        try {
            $db = Database::getInstance();

            // Check duplicate
            $exists = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                $_SESSION['errors'] = ["Email already registered. Please login."];
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Find referrer if referral code given
            $referrer_id = null;
            if (!empty($referral)) {
                $ref = $db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referral]);
                if ($ref) $referrer_id = $ref['id'];
            }

            $customer_id = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $db->insert('users', [
                'customer_id' => $customer_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed,
                'referral_code' => $referral_code,
                'referred_by' => $referrer_id,
                'user_type' => 'customer',
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $_SESSION['success'] = "Registration successful! Your Customer ID: $customer_id. Please login.";
            header('Location: ' . BASE_URL . '/login');
            exit;

        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
