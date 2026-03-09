<?php

namespace App\Services\Auth;

use App\Core\Database;
use Psr\Log\LoggerInterface;
use App\Models\User;

/**
 * Modern Authentication Service
 * Handles all authentication operations with proper MVC patterns
 */
class AuthService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private int $maxLoginAttempts = 5;
    private int $lockoutDuration = 900; // 15 minutes

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'max_login_attempts' => 5,
            'lockout_duration' => 900,
            'session_timeout' => 3600,
            'password_min_length' => 8
        ], $config);
        
        $this->maxLoginAttempts = $this->config['max_login_attempts'];
        $this->lockoutDuration = $this->config['lockout_duration'];
    }

    /**
     * Authenticate user login
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        try {
            // Check rate limiting
            if (!$this->checkRateLimit($email)) {
                return [
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'locked' => true
                ];
            }

            // Get user by email
            $user = $this->getUserByEmail($email);
            if (!$user) {
                $this->recordLoginAttempt($email, false);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Check if account is locked
            if ($this->isAccountLocked($user['id'])) {
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked. Please contact support.',
                    'locked' => true
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->recordLoginAttempt($email, false);
                $this->incrementFailedAttempts($user['id']);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Check if password needs rehash
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->updatePasswordHash($user['id'], $password);
            }

            // Successful login
            $this->recordLoginAttempt($email, true);
            $this->clearFailedAttempts($user['id']);
            $this->createUserSession($user, $remember);

            // Update last login
            $this->updateLastLogin($user['id']);

            $this->logger->info('User logged in successfully', ['user_id' => $user['id'], 'email' => $email]);

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUserData($user)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Login failed', ['email' => $email, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout(): bool
    {
        try {
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                
                // Clear session
                session_unset();
                session_destroy();
                
                // Clear remember me cookie if exists
                if (isset($_COOKIE['remember_token'])) {
                    $this->clearRememberToken($_COOKIE['remember_token']);
                    setcookie('remember_token', '', time() - 3600, '/');
                }
                
                $this->logger->info('User logged out', ['user_id' => $userId]);
                return true;
            }
            
            return false;

        } catch (\Exception $e) {
            $this->logger->error('Logout failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Register new user
     */
    public function register(array $userData): array
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'phone'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst($field) . ' is required'
                    ];
                }
            }

            // Validate email format
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }

            // Check if email already exists
            if ($this->getUserByEmail($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email already registered'
                ];
            }

            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($userData['password']);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Password does not meet requirements',
                    'errors' => $passwordValidation['errors']
                ];
            }

            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (name, email, password, phone, role, status, created_at) 
                    VALUES (?, ?, ?, ?, 'customer', 'active', NOW())";
            
            $this->db->execute($sql, [
                $userData['name'],
                $userData['email'],
                $hashedPassword,
                $userData['phone']
            ]);

            $userId = $this->db->lastInsertId();

            // Auto-login after registration
            $user = $this->getUserById($userId);
            if ($user) {
                $this->createUserSession($user);
                $this->logger->info('User registered successfully', ['user_id' => $userId, 'email' => $userData['email']]);
            }

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user' => $this->sanitizeUserData($user)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Registration failed', ['email' => $userData['email'] ?? 'unknown', 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current logged in user
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION['user_id'];
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->config['session_timeout']) {
            $this->logout();
            return null;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return $this->getUserById($userId);
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }

    /**
     * Check if current user is agent
     */
    public function isAgent(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'agent';
    }

    /**
     * Check if current user is customer
     */
    public function isCustomer(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'customer';
    }

    /**
     * Require authentication
     */
    public function requireAuth(): ?array
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        return $this->getCurrentUser();
    }

    /**
     * Require admin role
     */
    public function requireAdmin(): ?array
    {
        $user = $this->requireAuth();
        
        if (!$this->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Access denied';
            exit;
        }
        
        return $user;
    }

    /**
     * Change password
     */
    public function changePassword(string $currentPassword, string $newPassword): array
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }

            // Validate new password
            $validation = $this->validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'New password does not meet requirements',
                    'errors' => $validation['errors']
                ];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$hashedPassword, $user['id']]);

            $this->logger->info('Password changed successfully', ['user_id' => $user['id']]);

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Password change failed', ['user_id' => $_SESSION['user_id'] ?? null, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Password change failed. Please try again.'
            ];
        }
    }

    /**
     * Reset password request
     */
    public function requestPasswordReset(string $email): array
    {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found'
                ];
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Save reset token
            $sql = "INSERT INTO password_resets (email, token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()";
            
            $this->db->execute($sql, [$email, $token, $expires, $token, $expires]);

            // TODO: Send email with reset link
            // This would integrate with your email service

            $this->logger->info('Password reset requested', ['email' => $email]);

            return [
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Password reset request failed', ['email' => $email, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Password reset request failed. Please try again.'
            ];
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            // Validate token
            $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
            $reset = $this->db->fetchOne($sql, [$token]);
            
            if (!$reset) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ];
            }

            // Validate new password
            $validation = $this->validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Password does not meet requirements',
                    'errors' => $validation['errors']
                ];
            }

            // Update user password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?";
            $this->db->execute($sql, [$hashedPassword, $reset['email']]);

            // Delete reset token
            $this->db->execute("DELETE FROM password_resets WHERE token = ?", [$token]);

            $this->logger->info('Password reset successful', ['email' => $reset['email']]);

            return [
                'success' => true,
                'message' => 'Password reset successful'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Password reset failed', ['token' => $token, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Password reset failed. Please try again.'
            ];
        }
    }

    /**
     * Get authentication statistics
     */
    public function getAuthStats(): array
    {
        try {
            $stats = [];

            // Total users
            $stats['total_users'] = $this->db->fetchOne("SELECT COUNT(*) FROM users") ?? 0;

            // Users by role
            $roleStats = $this->db->fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            $stats['by_role'] = [];
            foreach ($roleStats as $stat) {
                $stats['by_role'][$stat['role']] = $stat['count'];
            }

            // Active users (logged in last 24 hours)
            $stats['active_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            ) ?? 0;

            // Failed login attempts today
            $stats['failed_logins_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Locked accounts
            $stats['locked_accounts'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM users WHERE status = 'locked'"
            ) ?? 0;

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get auth stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function getUserByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = ? AND status != 'deleted'";
        return $this->db->fetchOne($sql, [$email]);
    }

    private function getUserById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = ? AND status != 'deleted'";
        return $this->db->fetchOne($sql, [$id]);
    }

    private function checkRateLimit(string $email): bool
    {
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE email = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0";
        
        $attempts = $this->db->fetchOne($sql, [$email]) ?? 0;
        return $attempts < $this->maxLoginAttempts;
    }

    private function recordLoginAttempt(string $email, bool $success): void
    {
        $sql = "INSERT INTO login_attempts (email, success, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $email,
            $success ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    private function isAccountLocked(int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM failed_login_attempts 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $count = $this->db->fetchOne($sql, [$userId, $this->lockoutDuration / 60]) ?? 0;
        return $count >= $this->maxLoginAttempts;
    }

    private function incrementFailedAttempts(int $userId): void
    {
        $sql = "INSERT INTO failed_login_attempts (user_id, created_at) VALUES (?, NOW())";
        $this->db->execute($sql, [$userId]);
    }

    private function clearFailedAttempts(int $userId): void
    {
        $sql = "DELETE FROM failed_login_attempts WHERE user_id = ?";
        $this->db->execute($sql, [$userId]);
    }

    private function createUserSession(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days
            
            $sql = "INSERT INTO remember_tokens (user_id, token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()";
            
            $this->db->execute($sql, [$user['id'], $token, $expires, $token, $expires]);
            
            setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/', '', true, true);
        }
    }

    private function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }

    private function updatePasswordHash(int $userId, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$hashedPassword, $userId]);
    }

    private function clearRememberToken(string $token): void
    {
        $sql = "DELETE FROM remember_tokens WHERE token = ?";
        $this->db->execute($sql, [$token]);
    }

    private function sanitizeUserData(array $user): array
    {
        unset($user['password']);
        return $user;
    }

    private function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $valid = true;

        if (strlen($password) < $this->config['password_min_length']) {
            $errors[] = "Password must be at least {$this->config['password_min_length']} characters long";
            $valid = false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
            $valid = false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
            $valid = false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
            $valid = false;
        }

        return ['valid' => $valid, 'errors' => $errors];
    }
}
