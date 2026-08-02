<?php

namespace App\Services\Communication;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class DigiLockerService
{
    use ServiceTenantTrait;
    /** @var PDO */
    protected $db;

    /** @var array */
    protected $config = [];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
        $this->loadConfig();
    }

    protected function loadConfig(): void
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->query("SELECT * FROM digilocker_config WHERE is_active = 1 LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($config) {
                $this->config = $config;
            }
        } catch (Exception $e) {
        // Use defaults
        error_log($e->getMessage());
        }
    }

    /**
     * Get authorization URL for DigiLocker
     */
    public function getAuthUrl(string $state = ''): array
    {
        if (empty($this->config)) {
            return ['success' => false, 'error' => 'DigiLocker not configured'];
        }

        $state = $state ?: bin2hex(random_bytes(16));
        $rawScopes = $this->config['scopes'] ?? '["aadhaar","pan"]';
        $scopes = is_string($rawScopes)
            ? (json_decode($rawScopes, true) ?: ['aadhaar', 'pan'])
            : $rawScopes;

        // Save session
        $this->saveSession($state, $scopes);

        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
        ];

        $authUrl = rtrim($this->config['auth_url'], '/') . '?' . http_build_query($params);

        return [
            'success' => true,
            'auth_url' => $authUrl,
            'state' => $state,
            'scopes' => $scopes,
        ];
    }

    /**
     * Handle callback from DigiLocker
     */
    public function handleCallback(string $code, string $state): array
    {
        if (empty($this->config)) {
            return ['success' => false, 'error' => 'DigiLocker not configured'];
        }

        // Verify state
        $session = $this->getSession($state);
        if (!$session) {
            return ['success' => false, 'error' => 'Invalid or expired state'];
        }

        try {
            // Exchange code for access token
            $tokenResponse = $this->exchangeCodeForToken($code);
            if (!isset($tokenResponse['access_token'])) {
                return ['success' => false, 'error' => 'Failed to get access token: ' . ($tokenResponse['error_description'] ?? 'Unknown error')];
            }

            // Fetch user data
            $userData = $this->fetchUserData($tokenResponse['access_token']);

            // Update session
            $this->updateSession($state, $tokenResponse, $userData);

            return [
                'success' => true,
                'user_data' => $userData,
                'access_token' => $tokenResponse['access_token'],
                'refresh_token' => $tokenResponse['refresh_token'] ?? null,
                'expires_in' => $tokenResponse['expires_in'] ?? 3600,
            ];
        } catch (Exception $e) {
            error_log('[DigiLockerService::handleCallback] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Callback handling failed'];
        }
    }

    /**
     * Exchange authorization code for access token
     */
    protected function exchangeCodeForToken(string $code): array
    {
        $data = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
        ];

        $ch = curl_init($this->config['token_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'Token exchange failed: HTTP ' . $httpCode, 'response' => $response];
        }

        return json_decode($response, true) ?: [];
    }

    /**
     * Fetch user data from DigiLocker
     */
    protected function fetchUserData(string $accessToken): array
    {
        $apiUrl = rtrim($this->config['api_base_url'], '/') . '/user/profile';

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'Failed to fetch user data: HTTP ' . $httpCode];
        }

        return json_decode($response, true) ?: [];
    }

    /**
     * Save session
     */
    protected function saveSession(string $state, array $scopes): void
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO digilocker_sessions (session_id, state, scopes, status, tenant_id)
                VALUES (?, ?, ?, 'pending', ?)
                ON DUPLICATE KEY UPDATE scopes = VALUES(scopes), status = 'pending'
            ");
            $stmt->execute([$state, $state, json_encode($scopes), $this->tenantId()]);
        } catch (Exception $e) {
            error_log('[DigiLockerService::saveSession] ' . $e->getMessage());
        }
    }

    /**
     * Get session
     */
    protected function getSession(string $state): ?array
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("SELECT * FROM digilocker_sessions WHERE session_id = ? AND status = 'pending'" . $this->tenantSql());
            $stmt->execute([$state]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update session with token and user data
     */
    protected function updateSession(string $state, array $tokenData, array $userData): void
    {
        if (!$this->db) return;

        try {
            $expiresAt = date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600));
            
            $stmt = $this->db->prepare("
                UPDATE digilocker_sessions 
                SET access_token = ?, refresh_token = ?, expires_at = ?, 
                    user_data = ?, status = 'authorized', updated_at = CURRENT_TIMESTAMP
                WHERE session_id = ?" . $this->tenantSql() . "
            ");
            $stmt->execute([
                $tokenData['access_token'] ?? '',
                $tokenData['refresh_token'] ?? null,
                $expiresAt,
                json_encode($userData),
                $state,
            ]);
        } catch (Exception $e) {
            error_log('[DigiLockerService::updateSession] ' . $e->getMessage());
        }
    }

    /**
     * Get user's DigiLocker data
     */
    public function getUserData(int $userId): ?array
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM digilocker_sessions 
                WHERE user_id = ? AND status = 'authorized' " . $this->tenantSql() . "
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array
    {
        if (empty($this->config)) {
            return ['success' => false, 'error' => 'DigiLocker not configured'];
        }

        $data = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $ch = curl_init($this->config['token_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Token refresh failed'];
        }

        return json_decode($response, true) ?: ['success' => false, 'error' => 'Invalid response'];
    }
}