<?php

/**
 * Google OAuth Callback Handler
 * Handles Google OAuth callback for social registration with referral campaign integration
 */

// Include required files
require_once __DIR__ . '/../config/google_oauth_config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/ReferralService.php';
require_once __DIR__ . '/../app/Services/Security/SecurityService.php';

use App\Core\Database;
use App\Services\ReferralService;
use App\Services\Security\SecurityService;

class GoogleRegistrationHandler
{
    private PDO $db;
    private ReferralService $referralService;
    private SecurityService $securityService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->referralService = new ReferralService();
        $this->securityService = new SecurityService();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleCallback()
    {
        try {
            // Verify state parameter for CSRF protection
            if (!$this->verifyState()) {
                throw new Exception('Invalid state parameter');
            }

            // Exchange authorization code for access token
            $tokenData = $this->exchangeCodeForToken();
            if (!$tokenData) {
                throw new Exception('Failed to exchange authorization code');
            }

            // Get user profile from Google
            $userProfile = $this->getUserProfile($tokenData['access_token']);
            if (!$userProfile) {
                throw new Exception('Failed to retrieve user profile');
            }

            // Process user registration/login
            $result = $this->processUserRegistration($userProfile);

            // Redirect based on result
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_name'] = $result['name'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_role'] = $result['role'];
                $_SESSION['social_login'] = true;

                // Track successful registration for campaign analytics
                $this->trackRegistration($result['user_id'], 'google', $result['referral_code'] ?? null);

                $redirectUrl = BASE_URL . '/dashboard?welcome=social';
                header("Location: $redirectUrl");
                exit;
            } else {
                throw new Exception($result['message'] ?? 'Registration failed');
            }

        } catch (Exception $e) {
            error_log("Google OAuth Error: " . $e->getMessage());
            
            // Store error in session
            $_SESSION['oauth_error'] = $e->getMessage();
            header("Location: " . BASE_URL . "/register?error=oauth_failed");
            exit;
        }
    }

    /**
     * Verify state parameter
     */
    private function verifyState(): bool
    {
        $state = $_GET['state'] ?? '';
        $sessionState = $_SESSION['oauth_state'] ?? '';
        
        return !empty($state) && !empty($sessionState) && hash_equals($sessionState, $state);
    }

    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken(): ?array
    {
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            return null;
        }

        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $params = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URL,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google token exchange failed with HTTP code: $httpCode");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Get user profile from Google
     */
    private function getUserProfile(string $accessToken): ?array
    {
        $profileUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $profileUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google profile request failed with HTTP code: $httpCode");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Process user registration with referral support
     */
    private function processUserRegistration(array $userProfile): array
    {
        try {
            $email = $this->securityService->sanitize($userProfile['email'] ?? '');
            $name = $this->securityService->sanitize($userProfile['name'] ?? '');
            $googleId = $userProfile['id'] ?? '';
            $avatar = $userProfile['picture'] ?? '';

            if (empty($email) || empty($name)) {
                return ['success' => false, 'message' => 'Invalid user data'];
            }

            // Check if user already exists
            $existingUser = $this->findUserByEmail($email);
            
            if ($existingUser) {
                // User exists, check if they have Google connection
                if (!$this->hasGoogleConnection($existingUser['id'])) {
                    $this->saveGoogleConnection($existingUser['id'], $googleId, $avatar);
                }
                
                return [
                    'success' => true,
                    'user_id' => $existingUser['id'],
                    'name' => $existingUser['name'],
                    'email' => $existingUser['email'],
                    'role' => $existingUser['role'],
                    'message' => 'Login successful'
                ];
            }

            // Get referral code from session or URL
            $referralCode = $this->getReferralCode();
            $sponsorUserId = null;

            if ($referralCode) {
                $sponsorUserId = $this->validateReferralCode($referralCode);
                if (!$sponsorUserId) {
                    error_log("Invalid referral code: $referralCode");
                }
            }

            // Create new user
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash(uniqid() . time(), PASSWORD_DEFAULT),
                'role' => 'customer', // Default role for social registration
                'status' => 'active',
                'referrer_id' => $sponsorUserId,
                'registration_source' => 'google',
                'avatar' => $avatar,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->createUser($userData);
            
            if (!$userId) {
                return ['success' => false, 'message' => 'Failed to create user'];
            }

            // Save Google connection
            $this->saveGoogleConnection($userId, $googleId, $avatar);

            // Process referral if applicable
            if ($sponsorUserId && $referralCode) {
                $this->processReferral($userId, $sponsorUserId, $referralCode);
            }

            // Add to welcome campaign
            $this->addToWelcomeCampaign($userId);

            return [
                'success' => true,
                'user_id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
                'referral_code' => $referralCode,
                'message' => 'Registration successful'
            ];

        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Find user by email
     */
    private function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Check if user has Google connection
     */
    private function hasGoogleConnection(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM social_connections WHERE user_id = :user_id AND provider = 'google' LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Save Google connection
     */
    private function saveGoogleConnection(int $userId, string $googleId, string $avatar): bool
    {
        $sql = "INSERT INTO social_connections (user_id, provider, social_id, avatar_url, created_at)
                VALUES (:user_id, 'google', :social_id, :avatar_url, NOW())
                ON DUPLICATE KEY UPDATE avatar_url = VALUES(avatar_url), updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'social_id' => $googleId,
            'avatar_url' => $avatar
        ]);
    }

    /**
     * Get referral code from session or URL
     */
    private function getReferralCode(): ?string
    {
        // Check URL parameter first
        $urlCode = $_GET['ref'] ?? '';
        if (!empty($urlCode)) {
            return $this->securityService->sanitize($urlCode);
        }

        // Check session
        $sessionCode = $_SESSION['referral_code'] ?? '';
        if (!empty($sessionCode)) {
            return $this->securityService->sanitize($sessionCode);
        }

        return null;
    }

    /**
     * Validate referral code
     */
    private function validateReferralCode(string $referralCode): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id FROM user_referrals WHERE referral_code = :code AND status = 'active' LIMIT 1");
        $stmt->execute(['code' => $referralCode]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['user_id'] : null;
    }

    /**
     * Create new user
     */
    private function createUser(array $userData): ?int
    {
        $sql = "INSERT INTO users (name, email, password, role, status, referrer_id, registration_source, avatar, created_at)
                VALUES (:name, :email, :password, :role, :status, :referrer_id, :registration_source, :avatar, :created_at)";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($userData);

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Process referral
     */
    private function processReferral(int $userId, int $sponsorUserId, string $referralCode): void
    {
        try {
            // Update referral record
            $sql = "UPDATE user_referrals 
                    SET referred_user_id = :referred_user_id, status = 'completed', completed_at = NOW()
                    WHERE referral_code = :code AND user_id = :sponsor_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'referred_user_id' => $userId,
                'code' => $referralCode,
                'sponsor_id' => $sponsorUserId
            ]);

            // Create commission record if sponsor is associate
            $this->createReferralCommission($sponsorUserId, $userId);

            // Send notification to sponsor
            $this->sendReferralNotification($sponsorUserId, $userId);

        } catch (Exception $e) {
            error_log("Referral processing error: " . $e->getMessage());
        }
    }

    /**
     * Create referral commission
     */
    private function createReferralCommission(int $sponsorUserId, int $referredUserId): void
    {
        try {
            // Check if sponsor is associate
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $sponsorUserId]);
            $sponsor = $stmt->fetch();

            if ($sponsor && $sponsor['role'] === 'associate') {
                $sql = "INSERT INTO mlm_commission_ledger 
                        (user_id, commission_type, amount, source_user_id, description, status, created_at)
                        VALUES (:user_id, 'referral', :amount, :source_user_id, :description, 'pending', NOW())";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'user_id' => $sponsorUserId,
                    'amount' => 500.00, // ₹500 referral bonus
                    'source_user_id' => $referredUserId,
                    'description' => 'Referral bonus for new customer registration'
                ]);
            }
        } catch (Exception $e) {
            error_log("Referral commission error: " . $e->getMessage());
        }
    }

    /**
     * Send referral notification
     */
    private function sendReferralNotification(int $sponsorUserId, int $referredUserId): void
    {
        try {
            // Get user details
            $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $referredUserId]);
            $referredUser = $stmt->fetch();

            $stmt->execute(['user_id' => $sponsorUserId]);
            $sponsor = $stmt->fetch();

            if ($referredUser && $sponsor) {
                $subject = "New Referral Registration - APS Dream Home";
                $message = "Congratulations! {$referredUser['name']} has registered using your referral code.";
                
                // Store notification (would be sent via email/SMS)
                $sql = "INSERT INTO notifications (user_id, type, title, message, created_at)
                        VALUES (:user_id, 'referral', :title, :message, NOW())";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'user_id' => $sponsorUserId,
                    'title' => $subject,
                    'message' => $message
                ]);
            }
        } catch (Exception $e) {
            error_log("Referral notification error: " . $e->getMessage());
        }
    }

    /**
     * Add user to welcome campaign
     */
    private function addToWelcomeCampaign(int $userId): void
    {
        try {
            // Add to welcome sequence
            $sql = "INSERT INTO campaign_recipients (campaign_id, user_id, user_type, status, created_at)
                    SELECT c.id, :user_id, 'customer', 'pending', NOW()
                    FROM campaigns c 
                    WHERE c.campaign_type = 'welcome' AND c.status = 'active'
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

        } catch (Exception $e) {
            error_log("Welcome campaign error: " . $e->getMessage());
        }
    }

    /**
     * Track registration for campaign analytics
     */
    private function trackRegistration(int $userId, string $source, ?string $referralCode): void
    {
        try {
            $sql = "INSERT INTO registration_analytics (user_id, registration_source, referral_code, ip_address, user_agent, created_at)
                    VALUES (:user_id, :source, :referral_code, :ip_address, :user_agent, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'source' => $source,
                'referral_code' => $referralCode,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

        } catch (Exception $e) {
            error_log("Registration tracking error: " . $e->getMessage());
        }
    }
}

// Handle the callback
$handler = new GoogleRegistrationHandler();
$handler->handleCallback();
