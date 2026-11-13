<?php

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use App\Core\Security;
use PDO;
use Exception;

class ApiAuthService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Refresh an API token.
     *
     * MobileAuthApiController::refresh() / ::refreshV2() call this, but the
     * method was never implemented -> both endpoints 500'd with
     * "Call to undefined method App\Services\Auth\ApiAuthService::refreshToken()".
     *
     * Rules:
     *  - the access token must still be valid
     *  - the refresh token must match the stored one
     *  - the old token row is revoked and a new one issued
     */
    public function refreshToken(string $accessToken, string $refreshToken = ''): array
    {
        try {
            if ($accessToken === '') {
                return ['success' => false, 'message' => 'Token is required'];
            }

            $tid = $this->getTenantId();

            $stmt = $this->db->prepare(
                "SELECT id, user_id, token, expires_at FROM api_tokens
                 WHERE token = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                 LIMIT 1"
            );
            $params = [$accessToken];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Invalid token'];
            }

            if (!empty($row['expires_at']) && strtotime((string)$row['expires_at']) <= time()) {
                return ['success' => false, 'message' => 'Token expired'];
            }

            // A refresh token must be supplied and must match.
            if ($refreshToken === '' || !hash_equals((string)$row['token'], $refreshToken)) {
                return ['success' => false, 'message' => 'Invalid refresh token'];
            }

            // Revoke the old token, issue a new one.
            $newToken = Security::generateRandomString(64);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $this->db->prepare("DELETE FROM api_tokens WHERE id = ?")->execute([(int)$row['id']]);

            $ins = $this->db->prepare(
                "INSERT INTO api_tokens (user_id, token, expires_at, created_at" . ($tid > 1 ? ", tenant_id" : "") . ")
                 VALUES (?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")"
            );
            $insParams = [(int)$row['user_id'], $newToken, $expiresAt];
            if ($tid > 1) $insParams[] = $tid;
            $ins->execute($insParams);

            $userStmt = $this->db->prepare(
                "SELECT id, name, email, phone, role FROM users WHERE id = ? LIMIT 1"
            );
            $userStmt->execute([(int)$row['user_id']]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'success'     => true,
                'message'     => 'Token refreshed',
                'token'       => $newToken,
                'refresh_token' => $newToken,
                'expires_at'  => $expiresAt,
                'user'        => $user,
            ];
        } catch (\Throwable $e) {
            error_log('[ApiAuthService::refreshToken] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not refresh the token'];
        }
    }

    /**
     * Authenticate a user and generate an API token
     */
    public function login($email, $password)
    {
        try {
            $tid = $this->getTenantId();
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $params = [$email];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Generate token
            $token = Security::generateRandomString(64);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt = $this->db->prepare(
                "INSERT INTO api_tokens (user_id, token, expires_at, created_at" . ($tid > 1 ? ", tenant_id" : "") . ")
                 VALUES (?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")"
            );
            $apiParams = [$user['id'], $token, $expiresAt];
            if ($tid > 1) $apiParams[] = $tid;
            $stmt->execute($apiParams);

            try {
                // Fetch full profile info for initial app state
                $stmt = $this->db->prepare(
                    "SELECT u.id as userId, u.name, u.email, u.phone, u.role,
                            COALESCE(u.created_at, NOW()) as createdAt, 
                            COALESCE(u.updated_at, NOW()) as updatedAt,
                            COALESCE(mp.current_level, 'Customer') as rank
                     FROM users u
                     LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                     WHERE u.id = ?" . ($tid > 1 ? " AND u.tenant_id = ?" : "")
                );
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $profileParams = [$user['id']];
            if ($tid > 1) $profileParams[] = $tid;
            $stmt->execute($profileParams);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            $userData['avatar'] = null;
            $userData['target'] = 0.0;

            return [
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                ],
                'expires_at' => $expiresAt
            ];

        } catch (\Throwable $e) {
            error_log("API Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error'];
        }
    }

    /**
     * Logout and revoke API token
     */
    public function logout($token)
    {
        try {
            $tid = $this->getTenantId();
            try {
                $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE token = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $params = [$token];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
