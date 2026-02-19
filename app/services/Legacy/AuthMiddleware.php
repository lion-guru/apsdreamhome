<?php

namespace App\Services\Legacy;
/**
 * Advanced Authentication Middleware
 * Provides robust authentication and authorization mechanisms
 */
class AuthMiddleware {
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes
    private const SESSION_TIMEOUT = 1800; // 30 minutes

    /**
     * Validate admin login credentials
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function validateAdminCredentials($username, $password) {
        // Sanitize inputs
        $username = SecurityUtility::sanitizeInput($username, 'username');
        $password = SecurityUtility::sanitizeInput($password, 'password');

        if (!$username || !$password) {
            AdminLogger::log('INVALID_LOGIN_ATTEMPT', [
                'reason' => 'Invalid input format',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }

        // Check brute force attempts
        if (!self::checkLoginAttempts($username)) {
            return false;
        }

        try {
            // Database credential verification
            $db = \App\Core\App::database();
            
            // Query users table (modern) instead of legacy user table
            // Check both username and email for flexibility
            $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
            $user = $db->fetch($sql, ['username' => $username, 'email' => $username]);

            if (!$user) {
                // Fallback check in admin table if not found in users
                $sqlAdmin = "SELECT * FROM admin WHERE auser = :username OR email = :email";
                $adminUser = $db->fetch($sqlAdmin, ['username' => $username, 'email' => $username]);
                
                if ($adminUser) {
                    $user = $adminUser;
                    // Map admin columns to user columns for consistency
                    $user['password'] = $adminUser['apass'] ?? $adminUser['password'];
                    $user['username'] = $adminUser['auser'] ?? $adminUser['username'];
                    $user['role'] = $adminUser['role'] ?? 'admin';
                }
            }

            if (!$user) {
                AdminLogger::log('LOGIN_ATTEMPT_NONEXISTENT_USER', [
                    'username' => $username,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }

            // Verify password - support both modern and legacy hashing
            $password_verified = false;
            
            // Modern bcrypt check
            if (isset($user['password']) && password_verify($password, $user['password'])) {
                $password_verified = true;
            } 
            // Legacy SHA1/MD5 check (fallback)
            else if (isset($user['salt']) && isset($user['password_hash'])) {
                // Legacy user table format
                $hashedPassword = hash('sha256', $password . $user['salt']);
                if (hash_equals($user['password_hash'], $hashedPassword)) {
                    $password_verified = true;
                }
            }
            // Simple SHA1 check (legacy admin)
            else if (isset($user['apass']) && sha1($password) === $user['apass']) {
                $password_verified = true;
            }

            if (!$password_verified) {
                AdminLogger::log('INVALID_PASSWORD_ATTEMPT', [
                    'username' => $username,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }
            
            // Check if user has admin privileges
            $role = $user['role'] ?? 'user';
            if (!in_array($role, ['admin', 'super_admin', 'manager', 'employee'])) {
                 AdminLogger::log('UNAUTHORIZED_ROLE_LOGIN', [
                    'username' => $username,
                    'role' => $role,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }

            // Successful login
            self::createSecureSession($user['username'] ?? $username);
            return true;

        } catch (Exception $e) {
            AdminLogger::logError('DATABASE_AUTH_ERROR', [
                'message' => $e->getMessage(),
                'username' => $username
            ]);
            return false;
        }
    }

    /**
     * Check and manage login attempts
     * @param string $username
     * @return bool
     */
    private static function checkLoginAttempts($username) {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        $currentTime = time();
        
        // Clean up old attempts
        foreach ($_SESSION['login_attempts'] as $attempt => $timestamp) {
            if ($currentTime - $timestamp > self::LOCKOUT_DURATION) {
                unset($_SESSION['login_attempts'][$attempt]);
            }
        }

        // Record this attempt
        $_SESSION['login_attempts'][$username] = $currentTime;

        // Check if attempts exceed limit
        if (count($_SESSION['login_attempts']) > self::MAX_LOGIN_ATTEMPTS) {
            AdminLogger::securityAlert('BRUTE_FORCE_ATTEMPT', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }

        return true;
    }

    /**
     * Create secure session after successful login
     * @param string $username
     */
    private static function createSecureSession($username) {
        // Regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Log successful login
        AdminLogger::log('ADMIN_LOGIN_SUCCESS', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    }

    /**
     * Check if admin session is valid
     * @return bool
     */
    public static function isAdminSessionValid() {
        // Check if admin is logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }

        // Check session timeout
        $currentTime = time();
        if (($currentTime - $_SESSION['last_activity']) > self::SESSION_TIMEOUT) {
            // Session expired
            self::destroySession();
            return false;
        }

        // Update last activity
        $_SESSION['last_activity'] = $currentTime;
        return true;
    }

    /**
     * Destroy admin session
     */
    public static function destroySession() {
        // Log session destruction
        AdminLogger::log('ADMIN_SESSION_DESTROYED', [
            'username' => $_SESSION['admin_username'] ?? 'Unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Redirect to login
        header('Location: /admin/login.php');
        exit();
    }

    /**
     * Require admin authentication for a page
     */
    public static function requireAdminAuth() {
        if (!self::isAdminSessionValid()) {
            AdminLogger::log('UNAUTHORIZED_ACCESS_ATTEMPT', [
                'url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            
            header('Location: /admin/login.php');
            exit();
        }
    }
}

// Helper function for quick access
function admin_auth_required() {
    AuthMiddleware::requireAdminAuth();
}

