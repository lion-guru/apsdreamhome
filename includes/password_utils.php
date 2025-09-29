<?php
/**
 * Password Utilities
 * Provides secure password hashing and verification functions
 */

/**
 * Hash a password using bcrypt algorithm
 * @param string $password The plain text password to hash
 * @return string|false The hashed password or false on failure
 */
if (!function_exists('hash_password')) {
    function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

/**
 * Verify a password against a hash
 * @param string $password The plain text password to verify
 * @param string $hash The hash to verify against
 * @return bool True if password matches hash, false otherwise
 */
if (!function_exists('verify_password')) {
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * Check if a password needs rehashing with custom cost (wrapper for built-in function)
 * @param string $hash The hash to check
 * @return bool True if password needs rehashing, false otherwise
 */
if (!function_exists('check_password_rehash_needed')) {
    function check_password_rehash_needed($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

/**
 * Generate a random password
 * @param int $length Length of the password (default: 12)
 * @return string The generated password
 */
if (!function_exists('generate_random_password')) {
    function generate_random_password($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        $charLength = strlen($chars);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charLength - 1)];
        }
        
        return $password;
    }
}

/**
 * Validate password strength
 * @param string $password The password to validate
 * @return array Validation result with 'valid' boolean and 'message' string
 */
if (!function_exists('validate_password_strength')) {
    function validate_password_strength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        if (empty($errors)) {
            return ['valid' => true, 'message' => 'Password is strong'];
        } else {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
    }
}

// Make sure these functions are available
if (!function_exists('password_hash')) {
    throw new Exception('password_hash function is not available. Please use PHP 5.5+');
}
?>