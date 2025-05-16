<?php
/**
 * Password Utility Functions
 * Provides secure password hashing and verification
 */

class PasswordUtils {
    // Password hashing configuration
    private const HASH_ALGO = PASSWORD_ARGON2ID;
    private const HASH_OPTIONS = [
        'memory_cost' => 1024 * 16,  // 16 MB
        'time_cost' => 4,
        'threads' => 3
    ];

    /**
     * Hash a password securely
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string {
        // Validate password strength
        if (strlen($password) < 12) {
            throw new InvalidArgumentException('Password must be at least 12 characters long');
        }

        return password_hash($password, self::HASH_ALGO, self::HASH_OPTIONS);
    }

    /**
     * Verify a password against its hash
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password is correct, false otherwise
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     * @param string $hash Current password hash
     * @return bool True if rehash is needed
     */
    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, self::HASH_ALGO, self::HASH_OPTIONS);
    }

    /**
     * Generate a secure random password
     * @param int $length Password length
     * @return string Generated password
     */
    public static function generateRandomPassword(int $length = 16): string {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }
        
        return $password;
    }
}

// Optional: Add a function to reset admin password
function resetAdminPassword(string $username, string $newPassword) {
    require_once __DIR__ . '/db_config.php';
    
    $con = getDbConnection();
    if (!$con) {
        throw new Exception('Database connection failed');
    }

    $hashedPassword = PasswordUtils::hashPassword($newPassword);
    
    $stmt = $con->prepare("UPDATE admin SET apass = ? WHERE auser = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    
    $result = $stmt->execute();
    
    $stmt->close();
    $con->close();
    
    return $result;
}
