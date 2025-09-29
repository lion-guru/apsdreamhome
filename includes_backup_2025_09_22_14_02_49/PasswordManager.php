<?php
/**
 * Advanced Password Management Utility
 * Provides secure password handling, hashing, and validation
 */
class PasswordManager {
    // Password complexity requirements
    private const MIN_LENGTH = 12;
    private const MAX_LENGTH = 128;
    private const COMPLEXITY_RULES = [
        'uppercase' => '/[A-Z]/',
        'lowercase' => '/[a-z]/',
        'number' => '/[0-9]/',
        'special_char' => '/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/'
    ];

    /**
     * Generate a cryptographically secure salt
     * @return string
     */
    public static function generateSalt() {
        return bin2hex(random_bytes(16)); // 32 character salt
    }

    /**
     * Hash password with advanced security
     * @param string $password
     * @param string|null $salt
     * @return array
     */
    public static function hashPassword($password, $salt = null) {
        // Validate password complexity
        if (!self::validatePasswordComplexity($password)) {
            throw new InvalidArgumentException('Password does not meet complexity requirements');
        }

        // Generate salt if not provided
        $salt = $salt ?? self::generateSalt();

        // Multi-stage hashing for enhanced security
        $stages = [
            hash('sha256', $password . $salt),
            hash('sha512', $password . $salt),
            hash('whirlpool', $password . $salt)
        ];

        // Final hash combines multiple stages
        $finalHash = hash('sha3-512', implode('', $stages));

        return [
            'hash' => $finalHash,
            'salt' => $salt
        ];
    }

    /**
     * Verify password against stored hash
     * @param string $inputPassword
     * @param string $storedHash
     * @param string $storedSalt
     * @return bool
     */
    public static function verifyPassword($inputPassword, $storedHash, $storedSalt) {
        $hashedAttempt = self::hashPassword($inputPassword, $storedSalt);
        return hash_equals($storedHash, $hashedAttempt['hash']);
    }

    /**
     * Validate password complexity
     * @param string $password
     * @return bool
     */
    public static function validatePasswordComplexity($password) {
        // Check length
        if (strlen($password) < self::MIN_LENGTH || strlen($password) > self::MAX_LENGTH) {
            return false;
        }

        // Check complexity requirements
        $complexityChecks = array_map(function($rule, $pattern) use ($password) {
            return preg_match($pattern, $password);
        }, array_keys(self::COMPLEXITY_RULES), self::COMPLEXITY_RULES);

        // Require at least 3 out of 4 complexity checks
        return count(array_filter($complexityChecks)) >= 3;
    }

    /**
     * Generate a secure random password
     * @param int $length
     * @return string
     */
    public static function generateSecurePassword($length = 16) {
        $charsets = [
            'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
            'numbers' => '0123456789',
            'special' => '!@#$%^&*()_+-=[]{}|;:,.<>?'
        ];

        $password = '';
        
        // Ensure at least one character from each set
        foreach ($charsets as $charset) {
            $password .= $charset[random_int(0, strlen($charset) - 1)];
        }

        // Fill remaining length
        $remainingLength = $length - count($charsets);
        $allChars = implode('', $charsets);
        
        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        $passwordArray = str_split($password);
        shuffle($passwordArray);
        return implode('', $passwordArray);
    }

    /**
     * Check if password has been previously used
     * @param string $newPassword
     * @param array $previousPasswords
     * @return bool
     */
    public static function isPasswordReused($newPassword, $previousPasswords) {
        foreach ($previousPasswords as $oldPassword) {
            if (self::verifyPassword($newPassword, $oldPassword['hash'], $oldPassword['salt'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Perform password reset with additional security
     * @param int $userId
     * @param string $newPassword
     * @return array
     */
    public static function resetPassword($userId, $newPassword) {
        // Validate new password
        if (!self::validatePasswordComplexity($newPassword)) {
            throw new InvalidArgumentException('New password does not meet complexity requirements');
        }

        // Hash new password
        $passwordData = self::hashPassword($newPassword);

        // Log password reset event
        AdminLogger::log('PASSWORD_RESET', [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);

        return $passwordData;
    }
}

// Helper function for global use
function generate_secure_password($length = 16) {
    return PasswordManager::generateSecurePassword($length);
}
