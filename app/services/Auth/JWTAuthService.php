<?php

namespace App\Services\Auth;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;



/**
 * JWT Authentication Service for Mobile API V2
 * Stateless authentication using JSON Web Tokens (HS256)
 */
class JWTAuthService
{
    private $secret;
    private $database;
    private $inMemory = [];

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function __construct()
    {
        $this->database = Database::getInstance();

        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');
        if (!$secret) {
            $secret = $this->generateSecureSecret();
        }
        $this->secret = $secret;
    }

    /**
     * Generate a 64-char base64-url random secret
     */
    public function generateSecureSecret(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
    }

    /**
     * Get the active secret (useful for diagnostics)
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Generate a JWT access token (HS256) and persist a refresh token row.
     */
    public function generateToken($userId, $role, $expiresIn = 86400): array
    {
        $issuedAt = time();
        $expire = $issuedAt + $expiresIn;
        $refreshExpire = $issuedAt + (30 * 86400);

        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->base64UrlEncode(json_encode([
            'sub'  => (int) $userId,
            'role' => $role,
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'jti'  => bin2hex(random_bytes(8)),
        ]));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        $accessToken = "$header.$payload.$signature";

        $refreshToken = bin2hex(random_bytes(32));

        try {
            $pdo = $this->database->getConnection();
            $tid = $this->getTenantId();
            $stmt = $pdo->prepare("
                INSERT INTO api_tokens
                  (user_id, user_type, token, refresh_token, device_info, ip_address, expires_at, last_used_at, created_at, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?), NOW(), NOW(), ?)
            ");
            $stmt->execute([
                (int) $userId,
                $role,
                $accessToken,
                $refreshToken,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $refreshExpire,
                $tid > 1 ? $tid : null,
            ]);
        } catch (\Throwable $e) {
            error_log('JWTAuthService::generateToken persist failed: ' . $e->getMessage());
        }

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $expiresIn,
            'token_type'    => 'Bearer',
        ];
    }

    /**
     * Verify a JWT and return the decoded payload, or null on any failure.
     */
    public function verifyToken($token): ?array
    {
        if (!is_string($token) || $token === '') {
            return null;
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$h, $p, $s] = $parts;

        $expected = $this->base64UrlEncode(hash_hmac('sha256', "$h.$p", $this->secret, true));
        if (!hash_equals($expected, $s)) {
            return null;
        }

        $padded = strtr($p, '-_', '+/');
        $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
        $payload = json_decode(base64_decode($padded), true);
        if (!is_array($payload) || !isset($payload['exp'], $payload['sub'], $payload['role'])) {
            return null;
        }
        if ((int) $payload['exp'] < time()) {
            return null;
        }
        return $payload;
    }

    /**
     * Issue a new access+refresh pair if the old token is still valid.
     */
    public function refreshToken($oldToken): ?array
    {
        $oldToken = (string) $oldToken;
        if ($oldToken === '') {
            return null;
        }

        try {
            $pdo = $this->database->getConnection();
        } catch (\Throwable $e) {
            return null;
        }

        try {
            $isJwt = substr_count($oldToken, '.') === 2;
            if ($isJwt) {
                $payload = $this->verifyToken($oldToken);
                if (!$payload) {
                    return null;
                }
                $userId = (int) ($payload['sub'] ?? 0);
                $role   = (string) ($payload['role'] ?? 'customer');
            } else {
                $stmt = $pdo->prepare("
                    SELECT user_id, user_type, expires_at, last_used_at
                    FROM api_tokens
                    WHERE refresh_token = ?
                    LIMIT 1
                ");
                $stmt->execute([$oldToken]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$row) {
                    return null;
                }
                if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
                    return null;
                }
                $userId = (int) $row['user_id'];
                $role   = (string) ($row['user_type'] ?? 'customer');
            }

            if ($userId <= 0) {
                return null;
            }

            return $this->generateToken($userId, $role);
        } catch (\Throwable $e) {
            error_log('JWTAuthService::refreshToken failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rate limit check using `rate_limits` table.
     * Returns true when the request is allowed, false when blocked.
     */
    public function rateLimit($key, $maxRequests = 60, $windowSec = 60): bool
    {
        if (!$key) {
            return true;
        }

        try {
            $pdo = $this->database->getConnection();
        } catch (\Throwable $e) {
            return true;
        }

        $now = time();
        $windowStart = date('Y-m-d H:i:s', $now - ($now % $windowSec));

        try {
            $stmt = $pdo->prepare("SELECT request_count FROM rate_limits WHERE rate_key = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                $ins = $pdo->prepare("INSERT INTO rate_limits (rate_key, request_count, window_start) VALUES (?, 1, ?)
                    ON DUPLICATE KEY UPDATE request_count = request_count + 1, window_start = VALUES(window_start)");
                $ins->execute([$key, $windowStart]);
                return true;
            }

            $count = (int) $row['request_count'];
            if ($count >= $maxRequests) {
                return false;
            }

            $upd = $pdo->prepare("UPDATE rate_limits SET request_count = request_count + 1, window_start = ? WHERE rate_key = ?");
            $upd->execute([$windowStart, $key]);
            return true;
        } catch (\Throwable $e) {
            error_log('JWTAuthService::rateLimit failed: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Get user record by id + role. Returns null when not found.
     */
    public function getUser($userId, $role = null)
    {
        try {
            $pdo = $this->database->getConnection();
            $tid = $this->getTenantId();
            $sql = "SELECT id, name, email, phone, role, status FROM users WHERE id = ?";
            $params = [(int) $userId];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Persist a push notification token for a user.
     */
    public function registerPushToken($userId, $role, $deviceToken, $platform = 'android', $deviceId = null): bool
    {
        if (!$userId || !$deviceToken) {
            return false;
        }
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO push_tokens
                  (user_id, user_type, device_token, platform, device_id, is_active, last_used_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                  is_active = 1,
                  platform = VALUES(platform),
                  device_id = VALUES(device_id),
                  last_used_at = NOW(),
                  updated_at = NOW()
            ");
            $stmt->execute([(int) $userId, $role, $deviceToken, $platform, $deviceId]);
            return true;
        } catch (\Throwable $e) {
            error_log('JWTAuthService::registerPushToken failed: ' . $e->getMessage());
            return false;
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
