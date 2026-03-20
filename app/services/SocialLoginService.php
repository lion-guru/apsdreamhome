<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class SocialLoginService
{
    private $db;
    private $providers = [
        'google' => [
            'client_id' => 'your-google-client-id',
            'client_secret' => 'your-google-client-secret',
            'redirect_uri' => 'http://localhost/apsdreamhome/auth/google/callback',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
        ],
        'facebook' => [
            'client_id' => 'your-facebook-app-id',
            'client_secret' => 'your-facebook-app-secret',
            'redirect_uri' => 'http://localhost/apsdreamhome/auth/facebook/callback',
            'auth_url' => 'https://www.facebook.com/v18.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v18.0/oauth/access_token',
            'user_info_url' => 'https://graph.facebook.com/v18.0/me'
        ],
        'linkedin' => [
            'client_id' => 'your-linkedin-client-id',
            'client_secret' => 'your-linkedin-client-secret',
            'redirect_uri' => 'http://localhost/apsdreamhome/auth/linkedin/callback',
            'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
            'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
            'user_info_url' => 'https://api.linkedin.com/v2/people/~'
        ]
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get authorization URL for social provider
     */
    public function getAuthUrl($provider, $state = null)
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported provider: $provider");
        }

        $config = $this->providers[$provider];
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->getScope($provider),
            'state' => $state ?: $this->generateState()
        ];

        return $config['auth_url'] . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($provider, $code)
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported provider: $provider");
        }

        $config = $this->providers[$provider];
        $params = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $response = $this->makePostRequest($config['token_url'], $params);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception("Token exchange failed: " . $data['error_description'] ?? $data['error']);
        }

        return $data;
    }

    /**
     * Get user information from provider
     */
    public function getUserInfo($provider, $accessToken)
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported provider: $provider");
        }

        $config = $this->providers[$provider];
        $url = $config['user_info_url'];
        
        // Add access token to URL
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $url .= $separator . 'access_token=' . $accessToken;

        if ($provider === 'facebook') {
            $url .= '&fields=id,name,email,picture';
        }

        $response = $this->makeGetRequest($url);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception("Failed to get user info: " . $data['error']['message'] ?? $data['error']);
        }

        return $this->normalizeUserData($provider, $data);
    }

    /**
     * Authenticate or register user with social account
     */
    public function authenticateSocialUser($provider, $userData, $accessToken = null, $refreshToken = null)
    {
        try {
            // Check if social account already exists
            $socialAccount = $this->getSocialAccount($provider, $userData['provider_id']);

            if ($socialAccount) {
                // Update tokens and return existing user
                $this->updateSocialAccount($socialAccount['id'], $accessToken, $refreshToken);
                return $this->getUserById($socialAccount['user_id']);
            }

            // Check if user exists with same email
            if (!empty($userData['email'])) {
                $existingUser = $this->getUserByEmail($userData['email']);
                if ($existingUser) {
                    // Link social account to existing user
                    $this->createSocialAccount($existingUser['id'], $provider, $userData, $accessToken, $refreshToken);
                    return $existingUser;
                }
            }

            // Create new user and social account
            $userId = $this->createUserFromSocialData($userData);
            $this->createSocialAccount($userId, $provider, $userData, $accessToken, $refreshToken);

            return $this->getUserById($userId);

        } catch (Exception $e) {
            error_log("Social authentication error: " . $e->getMessage());
            throw new Exception("Social authentication failed: " . $e->getMessage());
        }
    }

    /**
     * Get social account by provider and provider ID
     */
    private function getSocialAccount($provider, $providerId)
    {
        $query = "SELECT * FROM social_accounts WHERE provider = ? AND provider_id = ?";
        return $this->db->fetch($query, [$provider, $providerId]);
    }

    /**
     * Update social account tokens
     */
    private function updateSocialAccount($socialAccountId, $accessToken, $refreshToken)
    {
        $expiresAt = null;
        if ($accessToken && isset($tokenData['expires_in'])) {
            $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        }

        $query = "UPDATE social_accounts SET access_token = ?, refresh_token = ?, expires_at = ? WHERE id = ?";
        $this->db->execute($query, [$accessToken, $refreshToken, $expiresAt, $socialAccountId]);
    }

    /**
     * Create social account
     */
    private function createSocialAccount($userId, $provider, $userData, $accessToken, $refreshToken)
    {
        $expiresAt = null;
        if ($accessToken && isset($tokenData['expires_in'])) {
            $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        }

        $query = "INSERT INTO social_accounts (user_id, provider, provider_id, provider_email, provider_name, provider_avatar, access_token, refresh_token, expires_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->execute($query, [
            $userId,
            $provider,
            $userData['provider_id'],
            $userData['email'] ?? null,
            $userData['name'] ?? null,
            $userData['avatar'] ?? null,
            $accessToken,
            $refreshToken,
            $expiresAt
        ]);
    }

    /**
     * Get user by ID
     */
    private function getUserById($userId)
    {
        $query = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetch($query, [$userId]);
    }

    /**
     * Get user by email
     */
    private function getUserByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetch($query, [$email]);
    }

    /**
     * Create user from social data
     */
    private function createUserFromSocialData($userData)
    {
        $query = "INSERT INTO users (name, email, role, status, created_at) VALUES (?, ?, 'customer', 'active', NOW())";
        $this->db->execute($query, [
            $userData['name'] ?? 'Social User',
            $userData['email'] ?? null
        ]);

        return $this->db->getLastInsertId();
    }

    /**
     * Normalize user data from different providers
     */
    private function normalizeUserData($provider, $data)
    {
        $normalized = [
            'provider_id' => null,
            'name' => null,
            'email' => null,
            'avatar' => null
        ];

        switch ($provider) {
            case 'google':
                $normalized['provider_id'] = $data['id'];
                $normalized['name'] = $data['name'];
                $normalized['email'] = $data['email'];
                $normalized['avatar'] = $data['picture'];
                break;

            case 'facebook':
                $normalized['provider_id'] = $data['id'];
                $normalized['name'] = $data['name'];
                $normalized['email'] = $data['email'] ?? null;
                $normalized['avatar'] = $data['picture']['data']['url'] ?? null;
                break;

            case 'linkedin':
                $normalized['provider_id'] = $data['id'];
                $normalized['name'] = $data['localizedFirstName'] . ' ' . $data['localizedLastName'];
                // LinkedIn doesn't provide email by default, need additional permissions
                break;
        }

        return $normalized;
    }

    /**
     * Get scope for each provider
     */
    private function getScope($provider)
    {
        $scopes = [
            'google' => 'openid email profile',
            'facebook' => 'email public_profile',
            'linkedin' => 'r_liteprofile r_emailaddress'
        ];

        return $scopes[$provider] ?? '';
    }

    /**
     * Generate random state
     */
    private function generateState()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Make GET request
     */
    private function makeGetRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("HTTP request failed with status: $httpCode");
        }

        return $response;
    }

    /**
     * Make POST request
     */
    private function makePostRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("HTTP request failed with status: $httpCode");
        }

        return $response;
    }

    /**
     * Get all social accounts for a user
     */
    public function getUserSocialAccounts($userId)
    {
        $query = "SELECT * FROM social_accounts WHERE user_id = ?";
        return $this->db->fetchAll($query, [$userId]);
    }

    /**
     * Unlink social account
     */
    public function unlinkSocialAccount($userId, $provider)
    {
        $query = "DELETE FROM social_accounts WHERE user_id = ? AND provider = ?";
        $this->db->execute($query, [$userId, $provider]);
        return true;
    }
}