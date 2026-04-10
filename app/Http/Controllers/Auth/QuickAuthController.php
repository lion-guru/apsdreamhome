<?php

namespace App\Http\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;

class QuickAuthController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Quick registration for casual visitors
     */
    public function quickRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $referralCode = $_POST['referral_code'] ?? '';

        try {
            // Validate inputs
            if (empty($name) || empty($email) || empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }

            // Check if user already exists
            $existingUser = $this->db->fetchOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", [$email, $phone]);
            if ($existingUser) {
                echo json_encode(['success' => false, 'message' => 'User already exists with this email or phone']);
                exit;
            }

            // Find referrer if referral code provided
            $referrerId = null;
            if (!empty($referralCode)) {
                $ref = $this->db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referralCode]);
                if ($ref) $referrerId = $ref['id'];
            }

            // Generate customer_id and referral code
            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Insert user (default to customer role)
            $this->db->insert('users', [
                'customer_id' => $customerId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $newReferralCode,
                'referred_by' => $referrerId,
                'user_type' => 'customer',
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $newUserId = $this->db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])['id'];

            // Create wallet entry
            $this->db->insert('wallet_points', [
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

            // Handle referral rewards if referral code was used
            if ($referrerId) {
                $referrerWallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$referrerId]);

                if ($referrerWallet) {
                    $rewardPoints = 100; // 100 points for customer referral

                    $newBalance = $referrerWallet['points_balance'] + $rewardPoints;
                    $newTotalEarned = $referrerWallet['total_earned'] + $rewardPoints;
                    $newReferralEarnings = $referrerWallet['referral_earnings'] + $rewardPoints;

                    $this->db->query("UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = ? WHERE user_id = ?",
                        [$newBalance, $newTotalEarned, $newReferralEarnings, date('Y-m-d H:i:s'), $referrerId]);

                    $this->db->insert('wallet_transactions', [
                        'user_id' => $referrerId,
                        'transaction_type' => 'credit',
                        'transaction_category' => 'referral',
                        'amount' => $rewardPoints,
                        'balance_before' => $referrerWallet['points_balance'],
                        'balance_after' => $newBalance,
                        'description' => "Quick signup referral reward for Customer: $name",
                        'reference_id' => $newUserId,
                        'reference_type' => 'user',
                        'related_user_id' => $newUserId,
                        'status' => 'completed',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $this->db->insert('referral_rewards', [
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

            // Set session
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_type'] = 'customer';
            $_SESSION['success'] = 'Account created successfully! Welcome to APS Dream Home.';

            echo json_encode(['success' => true, 'redirect' => '/customer/dashboard']);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Request referral code for Google search visitors
     */
    public function requestReferralCode()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        try {
            if (empty($name) || empty($email) || empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }

            // Generate company referral code for this request
            $requestId = 'REQ' . date('YmdHis') . rand(100, 999);
            $companyReferralCode = 'APS2025COMP';

            // Save referral request
            $this->db->insert('referral_requests', [
                'request_id' => $requestId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'company_referral_code' => $companyReferralCode,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Create table if not exists
            $tableExists = $this->db->fetchOne("SHOW TABLES LIKE 'referral_requests'");
            if (!$tableExists) {
                $this->db->query("
                    CREATE TABLE referral_requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        request_id VARCHAR(50) NOT NULL,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        phone VARCHAR(20) NOT NULL,
                        company_referral_code VARCHAR(50) NOT NULL,
                        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }

            echo json_encode([
                'success' => true,
                'referral_code' => $companyReferralCode,
                'message' => 'Referral code sent! Use this code to join as Associate/Agent.'
            ]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Request failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Auto-generate user during booking/lead conversion
     */
    public function autoGenerateUser()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $associateId = $_POST['associate_id'] ?? 0;

        try {
            if (empty($name) || empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'Name and phone are required']);
                exit;
            }

            // Check if user already exists by phone
            $existingUser = $this->db->fetchOne("SELECT * FROM users WHERE phone = ? LIMIT 1", [$phone]);

            if ($existingUser) {
                // Update lead with existing user
                echo json_encode([
                    'success' => true,
                    'user_id' => $existingUser['id'],
                    'customer_id' => $existingUser['customer_id'],
                    'message' => 'User already exists'
                ]);
                exit;
            }

            // Get associate referral code
            $associate = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ? LIMIT 1", [$associateId]);
            $referralCode = $associate ? $associate['referral_code'] : '';

            // Generate customer_id
            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Insert user
            $this->db->insert('users', [
                'customer_id' => $customerId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $newReferralCode,
                'referred_by' => $associateId,
                'user_type' => 'customer',
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $newUserId = $this->db->fetchOne("SELECT id FROM users WHERE phone = ? LIMIT 1", [$phone])['id'];

            // Create wallet entry
            $this->db->insert('wallet_points', [
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

            // Credit referral to associate
            if ($associateId && $referralCode) {
                $associateWallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$associateId]);

                if ($associateWallet) {
                    $rewardPoints = 100;
                    $newBalance = $associateWallet['points_balance'] + $rewardPoints;
                    $newTotalEarned = $associateWallet['total_earned'] + $rewardPoints;
                    $newReferralEarnings = $associateWallet['referral_earnings'] + $rewardPoints;

                    $this->db->query("UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = ? WHERE user_id = ?",
                        [$newBalance, $newTotalEarned, $newReferralEarnings, date('Y-m-d H:i:s'), $associateId]);

                    $this->db->insert('wallet_transactions', [
                        'user_id' => $associateId,
                        'transaction_type' => 'credit',
                        'transaction_category' => 'referral',
                        'amount' => $rewardPoints,
                        'balance_before' => $associateWallet['points_balance'],
                        'balance_after' => $newBalance,
                        'description' => "Booking referral reward for Customer: $name",
                        'reference_id' => $newUserId,
                        'reference_type' => 'user',
                        'related_user_id' => $newUserId,
                        'status' => 'completed',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $this->db->insert('referral_rewards', [
                        'referrer_id' => $associateId,
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

            echo json_encode([
                'success' => true,
                'user_id' => $newUserId,
                'customer_id' => $customerId,
                'message' => 'User created successfully'
            ]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Auto-generation failed: ' . $e->getMessage()]);
            exit;
        }
    }
}
