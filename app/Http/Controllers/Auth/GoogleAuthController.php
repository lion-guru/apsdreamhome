<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\GoogleAuthService;

class GoogleAuthController extends BaseController
{
    private GoogleAuthService $googleService;

    public function __construct()
    {
        parent::__construct();
        $this->googleService = new GoogleAuthService();
    }

    public function googleRedirect()
    {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : 'http://localhost/apsdreamhome';
        $redirectUri = $baseUrl . '/google_callback.php';
        header('Location: ' . $this->googleService->getAuthUrl($redirectUri));
        exit;
    }

    public function callback()
    {
        $code = $_GET['code'] ?? null;
        $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/login';

        if (!$code) {
            $_SESSION['error'] = 'Authorization failed';
            header('Location: ' . $loginUrl);
            exit;
        }

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : 'http://localhost/apsdreamhome';
        $redirectUri = $baseUrl . '/google_callback.php';

        $result = $this->googleService->handleCallback($code, $redirectUri);

        if ($result === false) {
            $_SESSION['error'] = 'Google authentication failed. Please try again.';
            header('Location: ' . $loginUrl);
            exit;
        }

        if (isset($result['is_new']) && $result['is_new']) {
            $_SESSION['google_user_data'] = [
                'name' => $result['name'],
                'email' => $result['email'],
                'picture' => $result['picture'] ?? ''
            ];
            header('Location: /auth/google/role-selection');
            exit;
        }

        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_name'] = $result['name'];
        $_SESSION['user_email'] = $result['email'];
        $_SESSION['user_phone'] = $result['phone'] ?? '';
        $_SESSION['role'] = $result['role'] ?? 'customer';
        $_SESSION['logged_in'] = true;
        $_SESSION['success'] = 'Welcome back, ' . $result['name'] . '!';

        header('Location: ' . $this->getRedirectUrl($result['role'] ?? 'customer'));
        exit;
    }

    public function roleSelection()
    {
        @session_start();
        if (!isset($_SESSION['google_user_data'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        include __DIR__ . '/../../../views/auth/google_role_selection.php';
    }

    public function completeRegistration()
    {
        @session_start();
        if (!isset($_SESSION['google_user_data'])) {
            echo json_encode(['success' => false, 'message' => 'Session expired']);
            exit;
        }

        $googleUserData = $_SESSION['google_user_data'];
        $role = $_POST['role'] ?? 'customer';
        $referralCode = $_POST['referral_code'] ?? '';
        $phone = $_POST['phone'] ?? '';

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $referrerId = null;
            if (!empty($referralCode)) {
                $ref = $db->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
                $ref->execute([$referralCode]);
                $refRow = $ref->fetch(\PDO::FETCH_ASSOC);
                if ($refRow) $referrerId = $refRow['id'];
            }

            $prefix = strtoupper(substr($role, 0, 3));
            $customerId = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($googleUserData['name'], 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (customer_id, name, email, phone, password, referral_code, referred_by, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$customerId, $googleUserData['name'], $googleUserData['email'], $phone, $password, $newReferralCode, $referrerId, $role]);

            $idStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $idStmt->execute([$googleUserData['email']]);
            $newUserId = $idStmt->fetch(\PDO::FETCH_ASSOC)['id'];

            $walletStmt = $db->prepare("INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, total_transferred_to_emi, referral_earnings, commission_earnings, bonus_earnings, status, created_at, updated_at) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 'active', NOW(), NOW())");
            $walletStmt->execute([$newUserId]);

            if ($referrerId) {
                $rwStmt = $db->prepare("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1");
                $rwStmt->execute([$referrerId]);
                $referrerWallet = $rwStmt->fetch(\PDO::FETCH_ASSOC);

                if ($referrerWallet) {
                    $rewardPoints = $role === 'customer' ? 100 : ($role === 'associate' ? 200 : 250);
                    $newBalance = $referrerWallet['points_balance'] + $rewardPoints;
                    $newTotalEarned = $referrerWallet['total_earned'] + $rewardPoints;
                    $newReferralEarnings = $referrerWallet['referral_earnings'] + $rewardPoints;

                    $updStmt = $db->prepare("UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = NOW() WHERE user_id = ?");
                    $updStmt->execute([$newBalance, $newTotalEarned, $newReferralEarnings, $referrerId]);

                    $txnStmt = $db->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, transaction_category, amount, balance_before, balance_after, description, reference_id, reference_type, related_user_id, status, created_at) VALUES (?, 'credit', 'referral', ?, ?, ?, ?, ?, 'user', ?, 'completed', NOW())");
                    $txnStmt->execute([$referrerId, $rewardPoints, $referrerWallet['points_balance'], $newBalance, "Google signup referral reward: " . $googleUserData['name'], $newUserId, $newUserId]);

                    $refStmt = $db->prepare("INSERT INTO referral_rewards (referrer_id, referred_id, reward_amount, reward_type, reward_percentage, referral_code, status, credited_at, created_at) VALUES (?, ?, ?, 'points', 0, ?, 'credited', NOW(), NOW())");
                    $refStmt->execute([$referrerId, $newUserId, $rewardPoints, $referralCode]);
                }
            }

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $googleUserData['name'];
            $_SESSION['user_email'] = $googleUserData['email'];
            $_SESSION['user_phone'] = $phone;
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;
            $_SESSION['success'] = 'Account created successfully! Welcome to APS Dream Home.';
            unset($_SESSION['google_user_data']);

            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($newUserId);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'redirect' => $this->getRedirectUrl($role)]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
            exit;
        }
    }

    private function getRedirectUrl($userType)
    {
        $redirects = [
            'customer' => '/user/dashboard',
            'associate' => '/associate/dashboard',
            'agent' => '/agent/dashboard',
            'admin' => '/admin/dashboard',
            'employee' => '/employee/dashboard'
        ];
        return $redirects[$userType] ?? '/user/dashboard';
    }
}
