<?php

/**
 * Unified Authentication Controller
 * Handles authentication for all role types (Executive, Management, Departmental, Team Lead, Staff, etc.)
 * This controller replaces individual auth controllers and provides a single entry point
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class UnifiedAuthController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Universal login page - handles all role types
     */
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $redirectUrl = $this->getRedirectUrl($_SESSION['user_type'] ?? 'customer', $_SESSION['user_role'] ?? 'user');
            header('Location: ' . BASE_URL . $redirectUrl);
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error']);
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        // Render universal login page
        $this->layout = false;
        ob_start();
        extract(compact('csrf_token', 'error', 'success'));
        $viewPath = __DIR__ . '/../../../views/auth/universal_login.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    /**
     * Universal authenticate method - handles all role types
     */
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
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['user_type'] = $user['user_type'] ?? 'customer';
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;

                // Role-based redirect
                $redirectUrl = $this->getRedirectUrl($user['user_type'] ?? 'customer', $user['role'] ?? 'user');
                header('Location: ' . BASE_URL . $redirectUrl);
                exit;
            } else {
                $_SESSION['errors'] = ["Invalid email or password"];
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Universal register page - handles all role types
     */
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
        $viewPath = __DIR__ . '/../../../views/auth/universal_register.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    /**
     * Universal register handler - handles all role types
     */
    public function handleRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $user_type = trim($_POST['user_type'] ?? 'customer');
        $referral = trim($_POST['referral_code'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";
        if (!in_array($user_type, ['customer', 'associate', 'agent', 'employee', 'admin'])) {
            $errors[] = "Invalid user type";
        }

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

            // Generate IDs based on user type
            $prefix = strtoupper(substr($user_type, 0, 3));
            $customer_id = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Set role based on user type
            $role = $user_type; // customer, associate, agent, employee, admin

            $db->insert('users', [
                'customer_id' => $customer_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed,
                'referral_code' => $referral_code,
                'referred_by' => $referrer_id,
                'user_type' => $user_type,
                'role' => $role,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $newUserId = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])['id'];

            // Create wallet entry for new user
            $db->insert('wallet_points', [
                'user_id' => $newUserId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Handle referral rewards
            if ($referrer_id) {
                $this->processReferralReward($db, $referrer_id, $newUserId, $name, $user_type, $referral);
            }

            $_SESSION['success'] = "Registration successful! Your ID: $customer_id. Please login.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
    }

    /**
     * Process referral reward
     */
    private function processReferralReward($db, $referrerId, $newUserId, $name, $userType, $referralCode)
    {
        $referrerWallet = $db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$referrerId]);

        if ($referrerWallet) {
            // Calculate reward based on user type
            $rewardPoints = match ($userType) {
                'associate' => 200.00,
                'agent' => 250.00,
                'customer' => 100.00,
                default => 100.00
            };

            $newBalance = $referrerWallet['points_balance'] + $rewardPoints;
            $newTotalEarned = $referrerWallet['total_earned'] + $rewardPoints;
            $newReferralEarnings = $referrerWallet['referral_earnings'] + $rewardPoints;

            $db->query(
                "UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = ? WHERE user_id = ?",
                [$newBalance, $newTotalEarned, $newReferralEarnings, date('Y-m-d H:i:s'), $referrerId]
            );

            $db->insert('wallet_transactions', [
                'user_id' => $referrerId,
                'transaction_type' => 'credit',
                'transaction_category' => 'referral',
                'amount' => $rewardPoints,
                'balance_before' => $referrerWallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => "Referral reward for $userType: $name",
                'reference_id' => $newUserId,
                'reference_type' => 'user',
                'related_user_id' => $newUserId,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $db->insert('referral_rewards', [
                'referrer_id' => $referrerId,
                'referred_id' => $newUserId,
                'reward_amount' => $rewardPoints,
                'reward_type' => 'points',
                'reward_percentage' => 0.00,
                'referral_code' => $referralCode,
                'status' => 'credited',
                'credited_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Universal logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /**
     * Get redirect URL based on user type and role
     * Comprehensive routing for all RBAC roles
     */
    private function getRedirectUrl($userType, $role)
    {
        // Executive Level - All go to admin dashboard
        if (in_array($role, ['super_admin', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro'])) {
            return '/admin/dashboard';
        }

        // Management Level - All go to admin dashboard
        if (in_array($role, ['director', 'sales_director', 'marketing_director', 'construction_director'])) {
            return '/admin/dashboard';
        }

        // Departmental Level - All go to admin dashboard
        if (in_array($role, ['department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager'])) {
            return '/admin/dashboard';
        }

        // Team Lead Level - All go to admin dashboard
        if (in_array($role, ['team_lead', 'telecalling_lead', 'sales_team_lead', 'support_lead'])) {
            return '/admin/dashboard';
        }

        // Senior Staff Level - Go to employee dashboard
        if (in_array($role, ['senior_accountant', 'senior_developer', 'legal_advisor', 'chartered_accountant'])) {
            return '/employee/dashboard';
        }

        // Staff Level - Go to employee dashboard
        if (in_array($role, ['accountant', 'developer', 'content_writer', 'graphic_designer', 'data_entry_operator', 'backoffice_staff'])) {
            return '/employee/dashboard';
        }

        // Telecalling & Support Level - Go to employee dashboard
        if (in_array($role, ['telecaller', 'telecalling_executive', 'support_executive'])) {
            return '/employee/dashboard';
        }

        // User Type Based Redirect
        switch ($userType) {
            case 'admin':
                return '/admin/dashboard';
            case 'associate':
                return '/associate/dashboard';
            case 'agent':
                return '/agent/dashboard';
            case 'employee':
                return '/employee/dashboard';
            case 'customer':
            default:
                return '/customer/dashboard';
        }
    }
}
