<?php

namespace App\Http\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function googleRedirect()
    {
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $redirectUri = 'http://localhost/apsdreamhome/auth/google/callback';
        // $redirectUri = 'https://seasonless-elissa-unwrathfully.ngrok-free.dev/auth/google/callback';

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => bin2hex(random_bytes(16))
        ]);

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback()
    {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            $_SESSION['error'] = 'Authorization failed';
            header('Location: /login');
            exit;
        }

        $clientId = getenv('GOOGLE_CLIENT_ID');
        $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
        $redirectUri = 'http://localhost/apsdreamhome/auth/google/callback';
        // $redirectUri = 'https://seasonless-elissa-unwrathfully.ngrok-free.dev/auth/google/callback';

        // Exchange code for access token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $data = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $response = $this->makeRequest($tokenUrl, $data);
        $tokenData = json_decode($response, true);

        if (!isset($tokenData['access_token'])) {
            $_SESSION['error'] = 'Failed to get access token';
            header('Location: /login');
            exit;
        }

        // Get user info
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $headers = [
            'Authorization: Bearer ' . $tokenData['access_token']
        ];

        $userResponse = $this->makeRequest($userInfoUrl, [], $headers);
        $userData = json_decode($userResponse, true);

        if (!isset($userData['email'])) {
            $_SESSION['error'] = 'Failed to get user information';
            header('Location: /login');
            exit;
        }

        // Check if user exists
        $userModel = new User();
        $user = $userModel->findByEmail($userData['email']);

        if ($user) {
            // Login existing user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_type'] = $user['user_type'] ?? 'customer';
            $_SESSION['success'] = 'Welcome back, ' . $user['name'] . '!';

            // Redirect based on user type
            $redirectUrl = $this->getRedirectUrl($user['user_type'] ?? 'customer');
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            // Store Google user data in session for role selection
            $_SESSION['google_user_data'] = [
                'name' => $userData['name'] ?? 'User',
                'email' => $userData['email'],
                'picture' => $userData['picture'] ?? ''
            ];

            // Redirect to role selection page
            header('Location: /auth/google/role-selection');
            exit;
        }
    }

    /**
     * Get redirect URL based on user type
     */
    private function getRedirectUrl($userType)
    {
        $redirects = [
            'customer' => '/customer/dashboard',
            'associate' => '/associate/dashboard',
            'agent' => '/agent/dashboard',
            'admin' => '/admin/dashboard',
            'employee' => '/employee/dashboard'
        ];

        return $redirects[$userType] ?? '/user/dashboard';
    }

    /**
     * Show role selection page
     */
    public function roleSelection()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['google_user_data'])) {
            header('Location: /login');
            exit;
        }

        $googleUserData = $_SESSION['google_user_data'];
        $companyReferralCode = 'APS2025COMP'; // Company referral code for new associates/agents

        include __DIR__ . '/../../../views/auth/google_role_selection.php';
    }

    /**
     * Complete Google registration with role and referral code
     */
    public function completeRegistration()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['google_user_data'])) {
            echo json_encode(['success' => false, 'message' => 'Session expired']);
            exit;
        }

        $googleUserData = $_SESSION['google_user_data'];
        $role = $_POST['role'] ?? 'customer';
        $referralCode = $_POST['referral_code'] ?? '';
        $phone = $_POST['phone'] ?? '';

        try {
            $db = \App\Core\Database::getInstance();

            // Find referrer if referral code provided
            $referrerId = null;
            if (!empty($referralCode)) {
                $ref = $db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referralCode]);
                if ($ref) $referrerId = $ref['id'];
            }

            // Generate customer_id based on role
            $prefix = strtoupper(substr($role, 0, 3));
            $customerId = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($googleUserData['name'], 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Insert user
            $db->insert('users', [
                'customer_id' => $customerId,
                'name' => $googleUserData['name'],
                'email' => $googleUserData['email'],
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $newReferralCode,
                'referred_by' => $referrerId,
                'user_type' => $role,
                'role' => $role === 'customer' ? 'user' : $role,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $newUserId = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$googleUserData['email']])['id'];

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

            // Handle referral rewards if referral code was used
            if ($referrerId) {
                $referrerWallet = $db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$referrerId]);

                if ($referrerWallet) {
                    // Calculate reward points based on role
                    $rewardPoints = $role === 'customer' ? 100 : ($role === 'associate' ? 200 : 250);

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
                        'description' => "Google signup referral reward for " . ucfirst($role) . ": " . $googleUserData['name'],
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

            // Set session
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $googleUserData['name'];
            $_SESSION['user_email'] = $googleUserData['email'];
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_type'] = $role;
            $_SESSION['success'] = 'Account created successfully! Welcome to APS Dream Home.';

            // Clear Google user data from session
            unset($_SESSION['google_user_data']);

            // Mark visitor as converted
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

    /**
     * Make HTTP request
     */
    private function makeRequest($url, $data = [], $headers = [])
    {
        $ch = curl_init($url);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
