<?php

/**
 * Unified Registration Controller
 * Single page registration for Customer / Agent / Associate roles
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class UnifiedRegisterController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function show()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        $role = $_SESSION['old_role'] ?? 'customer';
        unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['old_role']);
        $base = BASE_URL;

        // Preserve referral code from URL
        $ref = trim($_GET['ref'] ?? $old['referral_code'] ?? $old['sponsor_code'] ?? '');

        extract(compact('csrf_token', 'errors', 'old', 'role', 'ref'));
        include __DIR__ . '/../../../views/auth/unified_register.php';
    }

    public function handle()
    {
        @session_start();

        $role = trim($_POST['role'] ?? 'customer');
        $name = trim($_POST['name'] ?? $_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Role-specific fields
        $referral = trim($_POST['referral_code'] ?? $_POST['sponsor_code'] ?? $_GET['ref'] ?? '');
        $experience = $_POST['experience'] ?? '';

        // Common validation
        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        // Role-specific validation - associate sponsor code is REQUIRED for MLM tree integrity
        if ($role === 'agent' && empty($referral)) {
            $errors[] = "Referral code is required for agent registration";
        }

        if ($role === 'associate' && empty($referral)) {
            $errors[] = "Sponsor code is required for associate registration — this connects you to your sponsor's team";
        }

        if (!in_array($role, ['customer', 'agent', 'associate'])) {
            $errors[] = "Invalid role selected";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $_SESSION['old_role'] = $role;
            header('Location: ' . BASE_URL . '/register/unified');
            exit;
        }

        try {
            $db = Database::getInstance();

            // Check duplicate email
            $exists = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                $_SESSION['errors'] = ["Email already registered. Please login."];
                $_SESSION['old_input'] = $_POST;
                $_SESSION['old_role'] = $role;
                header('Location: ' . BASE_URL . '/register/unified');
                exit;
            }

            // Find referrer
            $referrer_id = null;
            if (!empty($referral)) {
                $ref = $db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referral]);
                if ($ref) $referrer_id = $ref['id'];
            }

            // Generate IDs based on role
            $prefix = match($role) {
                'customer' => 'CUS',
                'agent' => 'AGT',
                'associate' => 'ASC',
                default => 'USR'
            };
            $unique_id = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Status logic per role
            $status = 'active';
            $registration_status = 'approved';
            $approved_at = date('Y-m-d H:i:s');

            if ($role === 'agent') {
                $status = 'inactive';
                $registration_status = 'pending';
                $approved_at = null;
            } elseif ($role === 'associate') {
                $hasValidSponsor = !empty($referrer_id);
                $status = $hasValidSponsor ? 'active' : 'inactive';
                $registration_status = $hasValidSponsor ? 'approved' : 'pending';
                $approved_at = $hasValidSponsor ? date('Y-m-d H:i:s') : null;
            }

            // Insert user
            $userData = [
                'customer_id' => $unique_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed,
                'referral_code' => $referral_code,
                'referred_by' => $referrer_id,
                'role' => $role,
                'status' => $status,
                'registration_status' => $registration_status,
                'approved_at' => $approved_at,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Agent gets experience field
            if ($role === 'agent') {
                $userData['experience'] = $experience;
            }

            $db->insert('users', $userData);

            $newUserId = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])['id'];

            // Create wallet entry
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

            // Associate-specific tables
            if ($role === 'associate') {
                $this->createAssociateRecords($db, $newUserId, $name, $email, $phone, $referral_code, $referrer_id, $referral);
            }

            // Referral rewards
            if ($referrer_id) {
                $this->awardReferralReward($db, $referrer_id, $newUserId, $name, $referral, $role);
            }

            // Success message
            $successMessages = [
                'customer' => "Registration successful! Your Customer ID: $unique_id. Please login.",
                'agent' => "Agent registration successful! ID: $unique_id. Your account is pending admin approval. You will be able to login once approved.",
                'associate' => empty($referrer_id)
                    ? "Associate registration successful! ID: $unique_id. Your account is pending admin approval."
                    : "Associate registration successful! ID: $unique_id. You can now login."
            ];

            $_SESSION['success'] = $successMessages[$role];

            // Mark visitor as converted
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($newUserId);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }

            // ── Send welcome notifications (email + SMS + push + WhatsApp) ──
            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $loginNotifier->sendWelcomeNotifications(
                    (int)$newUserId, $name, $email, $phone, $role, $isMobile
                );
            } catch (\Throwable $e) {
                error_log("[UnifiedRegister] Welcome notification failed: " . $e->getMessage());
            }

            // Redirect to appropriate login
            $loginUrls = [
                'customer' => '/login',
                'agent' => '/agent/login',
                'associate' => '/associate/login'
            ];
            header('Location: ' . BASE_URL . $loginUrls[$role]);
            exit;

        } catch (\Exception $e) {
            error_log("Unified registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['old_role'] = $role;
            header('Location: ' . BASE_URL . '/register/unified');
            exit;
        }
    }

    /**
     * Create associate-specific DB records (associates, mlm_profiles, network_tree)
     */
    private function createAssociateRecords($db, $userId, $name, $email, $phone, $referralCode, $referrerId, $sponsorCode)
    {
        // Associates extension record
        $db->insert('associates', [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'referral_code' => $referralCode,
            'sponsor_id' => $referrerId,
            'level' => 'associate',
            'status' => 'active',
            'joining_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // MLM profile
        $db->insert('mlm_profiles', [
            'user_id' => $userId,
            'referral_code' => $referralCode,
            'sponsor_user_id' => $referrerId,
            'sponsor_code' => $sponsorCode ?: null,
            'user_type' => 'associate',
            'current_level' => 'associate',
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

        // Network tree entry
        $rootId = $userId;
        $parentId = null;
        $level = 1;
        $position = 'left';

        if ($referrerId) {
            $referrerTree = $db->fetchOne("SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1", [$referrerId]);
            if ($referrerTree) {
                $rootId = $referrerTree['root_id'];
                $parentId = $referrerId;
                $level = (int)$referrerTree['level'] + 1;
                $leftCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'", [$referrerId]);
                $rightCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'", [$referrerId]);
                $position = $leftCount <= $rightCount ? 'left' : 'right';
            }
        }

        $db->insert('network_tree', [
            'associate_id' => $userId,
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

        // Also insert into mlm_network_tree (used by commission engines)
        $mlmParentId = $parentId ?? $userId;
        $db->insert('mlm_network_tree', [
            'associate_id' => $userId,
            'sponsor_id' => $referrerId,
            'parent_id' => $mlmParentId,
            'level' => $level,
        ]);

        // Update referrer's team counts
        if ($referrerId) {
            $db->query(
                "UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1, total_team_size = total_team_size + 1, updated_at = ? WHERE user_id = ?",
                [date('Y-m-d H:i:s'), $referrerId]
            );
        }
    }

    /**
     * Award referral reward points to the referrer
     */
    private function awardReferralReward($db, $referrerId, $newUserId, $newUserName, $referralCode, $role)
    {
        $rewardPoints = match($role) {
            'customer' => 100.00,
            'agent' => 250.00,
            'associate' => 200.00,
            default => 0.00
        };

        if ($rewardPoints <= 0) return;

        $referrerWallet = $db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$referrerId]);
        if (!$referrerWallet) return;

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
            'description' => "Referral reward for $role: $newUserName",
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
