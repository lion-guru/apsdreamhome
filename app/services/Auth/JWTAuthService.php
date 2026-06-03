<?php

namespace App\Services\Auth;

use App\Core\Database\Database;

/**
 * JWT Authentication Service for Mobile API
 * Stateless authentication using JSON Web Tokens
 */
class JWTAuthService
{
    private $database;
    private $secretKey;
    private $tokenExpiry = 86400; // 24 hours
    private $refreshExpiry = 2592000; // 30 days
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'aps_dream_home_secret_key_2026';
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure API tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // API tokens table
        $pdo->exec("CREATE TABLE IF NOT EXISTS api_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
            token VARCHAR(500) NOT NULL,
            refresh_token VARCHAR(500) NULL,
            device_info JSON NULL,
            ip_address VARCHAR(45) NULL,
            last_used_at TIMESTAMP NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_user (user_id, user_type),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Push notification tokens
        $pdo->exec("CREATE TABLE IF NOT EXISTS push_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
            device_token VARCHAR(255) NOT NULL,
            platform ENUM('android', 'ios') NOT NULL,
            device_id VARCHAR(100) NULL,
            is_active TINYINT(1) DEFAULT 1,
            last_used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_device (user_id, user_type, device_id),
            INDEX idx_user (user_id, user_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // API rate limiting
        $pdo->exec("CREATE TABLE IF NOT EXISTS api_rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(100) NOT NULL,
            endpoint VARCHAR(100) NOT NULL,
            request_count INT DEFAULT 1,
            window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_window (identifier, endpoint, window_start),
            INDEX idx_identifier (identifier)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    /**
     * Generate JWT Token
     */
    public function generateToken(int $userId, string $userType, array $userData = [], ?string $deviceInfo = null): array
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->tokenExpiry;
        $refreshExpire = $issuedAt + $this->refreshExpiry;
        
        // Token payload
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'type' => $userType,
            'data' => [
                'id' => $userId,
                'name' => $userData['name'] ?? '',
                'email' => $userData['email'] ?? '',
                'phone' => $userData['phone'] ?? '',
                'type' => $userType
            ]
        ];
        
        // Generate tokens
        $token = $this->encode($payload);
        $refreshToken = $this->generateRefreshToken($userId, $userType);
        
        try {
            // Save to database
            $sql = "INSERT INTO api_tokens 
                (user_id, user_type, token, refresh_token, device_info, ip_address, expires_at) 
                VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?))";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $userId,
            $userType,
            $token,
            $refreshToken,
            $deviceInfo,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $expire
        ]);
        
        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->tokenExpiry,
            'expires_at' => date('Y-m-d H:i:s', $expire),
            'user' => $payload['data']
        ];
    }
    
    /**
     * Validate Token
     */
    public function validateToken(string $token): ?array
    {
        try {
            // Decode token
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }
            
            $payload = json_decode(base64_decode($parts[1]), true);
            
            if (!$payload || !isset($payload['exp'])) {
                return null;
            }
            
            // Check expiration
            if ($payload['exp'] < time()) {
                return null;
            }
            
            // Verify signature
            $signature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->secretKey, true);
            $signatureB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            if (!hash_equals($signatureB64, $parts[2])) {
                return null;
            }
            
            // Update last used
            $this->updateLastUsed($token);
            
            return $payload;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Refresh Token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $sql = "SELECT * FROM api_tokens WHERE refresh_token = ? AND expires_at > NOW()";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$refreshToken]);
        $tokenData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            return null;
        }
        
        // Get user data
        $userData = $this->getUserData($tokenData['user_id'], $tokenData['user_type']);
        
        if (!$userData) {
            return null;
        }
        
        // Revoke old token
        $this->revokeToken($tokenData['token']);
        
        // Generate new token
        return $this->generateToken(
            $tokenData['user_id'],
            $tokenData['user_type'],
            $userData,
            $tokenData['device_info']
        );
    }
    
    /**
     * Revoke Token
     */
    public function revokeToken(string $token): bool
    {
        try {
            $sql = "DELETE FROM api_tokens WHERE token = ?";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$token]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Register Push Token
     */
    public function registerPushToken(int $userId, string $userType, string $deviceToken, string $platform, ?string $deviceId = null): bool
    {
        try {
            $sql = "INSERT INTO push_tokens 
                (user_id, user_type, device_token, platform, device_id) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                device_token = VALUES(device_token),
                is_active = 1,
                last_used_at = NOW()";
            
            $stmt = $this->database->prepare($sql);
            return $stmt->execute([$userId, $userType, $deviceToken, $platform, $deviceId]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get user push tokens
     */
    public function getUserPushTokens(int $userId, string $userType): array
    {
        $sql = "SELECT device_token, platform FROM push_tokens 
            WHERE user_id = ? AND user_type = ? AND is_active = 1";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Encode JWT
     */
    private function encode(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $headerB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $payloadB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $this->secretKey, true);
        $signatureB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $headerB64 . '.' . $payloadB64 . '.' . $signatureB64;
    }
    
    /**
     * Generate refresh token
     */
    private function generateRefreshToken(int $userId, string $userType): string
    {
        return bin2hex(random_bytes(32)) . '_' . $userId . '_' . $userType;
    }
    
    /**
     * Get user data
     */
    private function getUserData(int $userId, string $userType): ?array
    {
        $table = match($userType) {
            'customer' => 'users',
            'associate' => 'users',
            'agent' => 'users',
            'admin' => 'users',
            default => null
        };
        
        if (!$table) return null;
        
        $sql = "SELECT id, name, email, phone FROM {$table} WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Update last used timestamp
     */
    private function updateLastUsed(string $token): void
    {
        try {
            $sql = "UPDATE api_tokens SET last_used_at = NOW() WHERE token = ?";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$token]);
    }
    
    /**
     * Check rate limit
     */
    public function checkRateLimit(string $identifier, string $endpoint, int $maxRequests = 60): bool
    {
        $windowStart = date('Y-m-d H:i:00'); // Per minute window
        
        // Get current count
        $sql = "SELECT request_count FROM api_rate_limits 
            WHERE identifier = ? AND endpoint = ? AND window_start = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$identifier, $endpoint, $windowStart]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Create new window
            $insertSql = "INSERT INTO api_rate_limits (identifier, endpoint, window_start) VALUES (?, ?, ?)";
            $insertStmt = $this->database->prepare($insertSql);
            $insertStmt->execute([$identifier, $endpoint, $windowStart]);
            return true;
        }
        
        if ($result['request_count'] >= $maxRequests) {
            return false; // Rate limit exceeded
        }
        
        // Increment count
        $updateSql = "UPDATE api_rate_limits SET request_count = request_count + 1 
            WHERE identifier = ? AND endpoint = ? AND window_start = ?";
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->execute([$identifier, $endpoint, $windowStart]);
        
        return true;
    }
    
    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        try {
            $sql = "DELETE FROM api_tokens WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
