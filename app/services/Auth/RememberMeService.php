<?php
namespace App\Services\Auth;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * Remember Me Service
 * Secure persistent login tokens with rotation and invalidation
 */
class RememberMeService
{
    private $db;
    private $tokenLength = 32;
    private $cookieName = 'remember_token';
    private $cookieLifetime = 2592000; // 30 days
    private $maxTokensPerUser = 5;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get current tenant ID
     */
    private function getTenantId(): int
    {
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Create a new remember me token for user
     */
    public function createToken(int $userId): array
    {
        try {
            $tid = $this->getTenantId();

            // Clean up old tokens if user has too many
            $this->cleanupOldTokens($userId);

            // Generate secure token
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes($this->tokenLength));
            $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
            $expiresAt = date('Y-m-d H:i:s', time() + $this->cookieLifetime);

            $tokenData = [
                'user_id' => $userId,
                'selector' => $selector,
                'validator' => $hashedValidator,
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ];
            if ($tid > 1) {
                $tokenData['tenant_id'] = $tid;
            }

            $this->db->insert('remember_tokens', $tokenData);

            // Set cookie
            $cookieValue = $selector . ':' . $validator;
            setcookie($this->cookieName, $cookieValue, [
                'expires' => time() + $this->cookieLifetime,
                'path' => '/',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'samesite' => 'Lax',
            ]);

            return ['success' => true, 'message' => 'Remember me token created'];
        } catch (\Exception $e) {
            error_log("RememberMeService::createToken error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create remember token'];
        }
    }

    /**
     * Validate remember me token and auto-login user
     */
    public function validateToken(): ?int
    {
        if (empty($_COOKIE[$this->cookieName])) {
            return null;
        }

        $cookieValue = $_COOKIE[$this->cookieName];
        $parts = explode(':', $cookieValue, 2);

        if (count($parts) !== 2) {
            $this->clearCookie();
            return null;
        }

        list($selector, $validator) = $parts;

        try {
            $tid = $this->getTenantId();

            $token = $this->db->fetchOne(
                "SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW()" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$selector, $tid] : [$selector]
            );

            if (!$token) {
                $this->clearCookie();
                return null;
            }

            // Verify validator with constant-time comparison
            if (!password_verify($validator, $token['validator'])) {
                // Token theft detected - invalidate all user tokens
                $this->invalidateAllUserTokens($token['user_id']);
                $this->clearCookie();
                return null;
            }

            // Token valid - rotate token (create new, delete old)
            $userId = (int)$token['user_id'];
            $this->db->execute("DELETE FROM remember_tokens WHERE selector = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$selector, $tid] : [$selector]);
            $this->createToken($userId);

            return $userId;
        } catch (\Exception $e) {
            error_log("RememberMeService::validateToken error: " . $e->getMessage());
            $this->clearCookie();
            return null;
        }
    }

    /**
     * Invalidate specific token
     */
    public function invalidateToken(string $selector): bool
    {
        try {
            $tid = $this->getTenantId();
            $deleted = $this->db->execute(
                "DELETE FROM remember_tokens WHERE selector = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$selector, $tid] : [$selector]
            )->rowCount() > 0;

            if ($deleted) {
                $this->clearCookie();
            }
            return $deleted;
        } catch (\Exception $e) {
            error_log("RememberMeService::invalidateToken error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate all tokens for a user
     */
    public function invalidateAllUserTokens(int $userId): bool
    {
        try {
            $tid = $this->getTenantId();
            $deleted = $this->db->execute(
                "DELETE FROM remember_tokens WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$userId, $tid] : [$userId]
            )->rowCount() > 0;
            return $deleted;
        } catch (\Exception $e) {
            error_log("RememberMeService::invalidateAllUserTokens error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up old tokens for user (keep only max tokens)
     */
    private function cleanupOldTokens(int $userId): void
    {
        try {
            $tid = $this->getTenantId();

            // Count current tokens
            $count = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM remember_tokens WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$userId, $tid] : [$userId]
            )['cnt'] ?? 0;

            if ($count >= $this->maxTokensPerUser) {
                // Delete oldest tokens
                $toDelete = $count - $this->maxTokensPerUser + 1;
                $this->db->execute(
                    "DELETE FROM remember_tokens 
                     WHERE id IN (
                         SELECT id FROM remember_tokens 
                         WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                         ORDER BY created_at ASC LIMIT ?
                     )",
                    $tid > 1 ? [$userId, $tid, $toDelete] : [$userId, $toDelete]
                );
            }
        } catch (\Exception $e) {
            error_log("RememberMeService::cleanupOldTokens error: " . $e->getMessage());
        }
    }

    /**
     * Clear remember me cookie
     */
    public function clearCookie(): void
    {
        setcookie($this->cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Get user's active tokens (for session management UI)
     */
    public function getUserTokens(int $userId): array
    {
        try {
            $tid = $this->getTenantId();
            return $this->db->fetchAll(
                "SELECT selector, created_at, expires_at, ip_address, user_agent 
                 FROM remember_tokens 
                 WHERE user_id = ? AND expires_at > NOW()" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                 ORDER BY created_at DESC",
                $tid > 1 ? [$userId, $tid] : [$userId]
            );
        } catch (\Exception $e) {
            error_log("RememberMeService::getUserTokens error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Revoke specific token by selector
     */
    public function revokeToken(int $userId, string $selector): bool
    {
        try {
            $tid = $this->getTenantId();
            $deleted = $this->db->execute(
                "DELETE FROM remember_tokens WHERE user_id = ? AND selector = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$userId, $selector, $tid] : [$userId, $selector]
            )->rowCount() > 0;
            return $deleted;
        } catch (\Exception $e) {
            error_log("RememberMeService::revokeToken error: " . $e->getMessage());
            return false;
        }
    }

}

