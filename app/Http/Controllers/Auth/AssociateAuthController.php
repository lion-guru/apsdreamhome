<?php

/**
 * Associate Authentication Controller
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class AssociateAuthController extends BaseController
{
    public function associateRegister()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        $base = BASE_URL;
        extract(compact('csrf_token', 'errors', 'old'));
        include __DIR__ . '/../../../views/auth/associate_register.php';
    }

    public function handleAssociateRegister()
    {
        @session_start();

        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['sponsor_code'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }

        try {
            $db = Database::getInstance();
            $exists = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                $_SESSION['errors'] = ["Email already registered"];
                header('Location: ' . BASE_URL . '/associate/register');
                exit;
            }

            $referrer_id = null;
            if (!empty($referral)) {
                $ref = $db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referral]);
                if ($ref) $referrer_id = $ref['id'];
            }

            $associate_id = 'ASC' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $db->insert('users', [
                'customer_id' => $associate_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed,
                'referral_code' => $referral_code,
                'referred_by' => $referrer_id,
                'role' => 'associate',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $newUserId = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])['id'];

            // Create wallet entry for new associate
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

            // Create associates extension record
            $db->insert('associates', [
                'user_id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'referral_code' => $referral_code,
                'sponsor_id' => $referrer_id,
                'level' => 'associate',
                'status' => 'active',
                'joining_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Create mlm_profile for new associate
            $db->insert('mlm_profiles', [
                'user_id' => $newUserId,
                'referral_code' => $referral_code,
                'sponsor_user_id' => $referrer_id,
                'sponsor_code' => $referral ?: null,
                'user_type' => 'associate',
                'current_level' => 1,
                'total_team_size' => 0,
                'direct_referrals' => 0,
                'total_commission' => 0.00,
                'pending_commission' => 0.00,
                'lifetime_sales' => 0.00,
                'verification_status' => 'pending',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Create network_tree entry
            $rootId = $newUserId;
            $parentId = null;
            $level = 1;
            $position = 'left';

            if ($referrer_id) {
                $referrerTree = $db->fetchOne("SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1", [$referrer_id]);
                if ($referrerTree) {
                    $rootId = $referrerTree['root_id'];
                    $parentId = $referrer_id;
                    $level = (int)$referrerTree['level'] + 1;
                    // Auto-assign position: prefer side with fewer members
                    $leftCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'", [$referrer_id]);
                    $rightCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'", [$referrer_id]);
                    $position = $leftCount <= $rightCount ? 'left' : 'right';
                }
            }

            $db->insert('network_tree', [
                'associate_id' => $newUserId,
                'root_id' => $rootId,
                'parent_id' => $parentId,
                'level' => $level,
                'position' => $position,
                'total_left_count' => 0,
                'total_right_count' => 0,
                'total_left_bv' => 0.00,
                'total_right_bv' => 0.00,
                'personal_bv' => 0.00,
                'is_active' => 1,
                'joined_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Update referrer's direct_referrals count in mlm_profiles
            if ($referrer_id) {
                $db->query("UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1, total_team_size = total_team_size + 1, updated_at = ? WHERE user_id = ?", [date('Y-m-d H:i:s'), $referrer_id]);
            }

            // Handle referral rewards if referral code was used
            if ($referrer_id) {
                // Get referrer's wallet
                $referrerWallet = $db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$referrer_id]);

                if ($referrerWallet) {
                    // Calculate reward points (200 points for associate referral)
                    $rewardPoints = 200.00;

                    // Update referrer's wallet
                    $newBalance = $referrerWallet['points_balance'] + $rewardPoints;
                    $newTotalEarned = $referrerWallet['total_earned'] + $rewardPoints;
                    $newReferralEarnings = $referrerWallet['referral_earnings'] + $rewardPoints;

                    $db->query(
                        "UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = ? WHERE user_id = ?",
                        [$newBalance, $newTotalEarned, $newReferralEarnings, date('Y-m-d H:i:s'), $referrer_id]
                    );

                    // Create transaction record
                    $db->insert('wallet_transactions', [
                        'user_id' => $referrer_id,
                        'transaction_type' => 'credit',
                        'transaction_category' => 'referral',
                        'amount' => $rewardPoints,
                        'balance_before' => $referrerWallet['points_balance'],
                        'balance_after' => $newBalance,
                        'description' => "Referral reward for associate: $name",
                        'reference_id' => $newUserId,
                        'reference_type' => 'user',
                        'related_user_id' => $newUserId,
                        'status' => 'completed',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    // Create referral reward record
                    $db->insert('referral_rewards', [
                        'referrer_id' => $referrer_id,
                        'referred_id' => $newUserId,
                        'reward_amount' => $rewardPoints,
                        'reward_type' => 'points',
                        'reward_percentage' => 0.00,
                        'referral_code' => $referral,
                        'status' => 'credited',
                        'credited_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $_SESSION['success'] = "Associate registration successful! ID: $associate_id. Please login.";

            // Mark visitor as converted
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($newUserId);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }

            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            error_log("Associate registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }
    }

    public function associateLogin()
    {
        @session_start();
        if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'associate') {
            header('Location: ' . BASE_URL . '/associate/dashboard');
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
        $base = BASE_URL;
        extract(compact('csrf_token', 'error', 'success'));
        include __DIR__ . '/../../../views/auth/associate_login.php';
    }

    public function authenticateAssociate()
    {
        @session_start();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            $user = $db->fetchOne("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = 'associate' AND status = 'active' LIMIT 1", [$email, $email]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['role'] = $user['role'] ?? 'associate';
                $_SESSION['referral_code'] = $user['referral_code'] ?? '';
                $_SESSION['associate_logged_in'] = true;
                $_SESSION['logged_in'] = true;

                // Force redirect to associate dashboard when logging in via associate login
                header('Location: ' . BASE_URL . '/associate/dashboard');
                exit;
            }
            $_SESSION['errors'] = ["Invalid email or password"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ["Login failed"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }
    }

    public function logout()
    {
        @session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/associate/login');
        exit;
    }

    /**
     * Get redirect URL based on user type and role
     */
    private function getRedirectUrl($userType, $role)
    {
        // Executive Level
        if (in_array($role, ['super_admin', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro'])) {
            return '/admin/dashboard';
        }

        // Management Level
        if (in_array($role, ['director', 'sales_director', 'marketing_director', 'construction_director'])) {
            return '/admin/dashboard';
        }

        // Departmental Level
        if (in_array($role, ['department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager'])) {
            return '/admin/dashboard';
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
