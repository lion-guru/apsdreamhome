<?php

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
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

    /**
     * Get current tenant ID for multi-tenant scoping.
     */
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'max_activity_logs_unified' => 5,
            'lockout_duration' => 900,
            'session_timeout' => 3600,
            'password_min_length' => 8
        ], $config);
        
        $this->maxLoginAttempts = $this->config['max_activity_logs_unified'];
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
                    setcookie('remember_token', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
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
            $tid = TenantContext::getId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $sql = "INSERT INTO users (name, email, password, phone, role, status, created_at{$tenantCol}) 
                    VALUES (?, ?, ?, ?, 'customer', 'active', NOW(){$tenantVal})";
            $params = [
                $userData['name'],
                $userData['email'],
                $hashedPassword,
                $userData['phone']
            ];
            if ($tid > 1) $params[] = $tid;
            
            $this->db->execute($sql, $params);

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
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $this->db->execute($sql, $tid > 1 ? [$hashedPassword, $user['id'], $tid] : [$hashedPassword, $user['id']]);

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

            $tid = $this->getTenantId();
            $user = $this->db->fetchOne("SELECT id FROM users WHERE email = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$email, $tid] : [$email]);
            if (!$user) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at" . ($tid > 1 ? ", tenant_id" : "") . ") 
                     VALUES (?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")
                     ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()" . ($tid > 1 ? ", tenant_id = ?" : "");
            
            $params = [$user['id'], $token, $expires, $token, $expires];
            if ($tid > 1) {
                $params[] = $tid;
                $params[] = $tid;
            }
            $this->db->execute($sql, $params);

            // Log reset link (email sending placeholder - integrate with EmailService when SMTP configured)
            $resetLink = (rtrim(BASE_URL, '/')) . '/reset-password?token=' . $token;
            error_log("PASSWORD RESET LINK for {$email}: {$resetLink}");

            try {
                $emailService = new \App\Services\Communication\EmailService();
                $emailService->sendPasswordResetEmail($email, $token);
            } catch (\Exception $e) {
                $this->logger->error('Failed to send password reset email', ['email' => $email, 'error' => $e->getMessage()]);
            }

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
            $tid = $this->getTenantId();
            $sql = "SELECT prt.*, u.email FROM password_reset_tokens prt
                    JOIN users u ON prt.user_id = u.id
                    WHERE prt.token = ? AND prt.expires_at > NOW()" . ($tid > 1 ? " AND prt.tenant_id = ? AND u.tenant_id = ?" : "");
            $params = [$token];
            if ($tid > 1) {
                $params[] = $tid;
                $params[] = $tid;
            }
            $reset = $this->db->fetchOne($sql, $params);
            
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $this->db->execute($sql, $tid > 1 ? [$hashedPassword, $reset['email'], $tid] : [$hashedPassword, $reset['email']]);

            $tid = $this->getTenantId();
            $this->db->execute("DELETE FROM password_reset_tokens WHERE token = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$token, $tid] : [$token]);

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
            $tid = $this->getTenantId();
            $stats['total_users'] = $this->db->fetchOne("SELECT COUNT(*) FROM users" . ($tid > 1 ? " WHERE tenant_id = ?" : ""), $tid > 1 ? [$tid] : []) ?? 0;

            // Users by role
            $roleStats = $this->db->fetchAll("SELECT role, COUNT(*) as count FROM users" . ($tid > 1 ? " WHERE tenant_id = ?" : "") . " GROUP BY role", $tid > 1 ? [$tid] : []);
            $stats['by_role'] = [];
            foreach ($roleStats as $stat) {
                $stats['by_role'][$stat['role']] = $stat['count'];
            }

            // Active users (logged in last 24 hours)
            $tid = $this->getTenantId();
            $stats['active_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$tid] : []
            ) ?? 0;

            // Failed login attempts today
            $stats['failed_logins_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM activity_logs_unified WHERE success = 0 AND DATE(created_at) = CURDATE()" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$tid] : []
            ) ?? 0;

            // Locked accounts
            $stats['locked_accounts'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM users WHERE status = 'locked'" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$tid] : []
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
        $tid = $this->getTenantId();
        $sql = "SELECT * FROM users WHERE email = ? AND status != 'deleted'" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$email, $tid] : [$email]);
    }

    private function getUserById(int $id): ?array
    {
        $tid = $this->getTenantId();
        $sql = "SELECT * FROM users WHERE id = ? AND status != 'deleted'" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]);
    }

    private function checkRateLimit(string $email): bool
    {
        $tid = $this->getTenantId();
        $sql = "SELECT COUNT(*) as attempts FROM activity_logs_unified 
                WHERE email = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0" . ($tid > 1 ? " AND tenant_id = ?" : "");
        
        $attempts = $this->db->fetchOne($sql, $tid > 1 ? [$email, $tid] : [$email]) ?? 0;
        return $attempts < $this->maxLoginAttempts;
    }

    private function recordLoginAttempt(string $email, bool $success): void
    {
        $tid = $this->getTenantId();
        $tenantCol = $tid > 1 ? ", tenant_id" : "";
        $tenantVal = $tid > 1 ? ", ?" : "";
        $sql = "INSERT INTO activity_logs_unified (email, success, ip_address, user_agent, created_at{$tenantCol}) 
                VALUES (?, ?, ?, ?, NOW(){$tenantVal})";
        
        $params = [
            $email,
            $success ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        if ($tid > 1) $params[] = $tid;
        $this->db->execute($sql, $params);
    }

    private function isAccountLocked(int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM failed_activity_logs_unified 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $count = $this->db->fetchOne($sql, [$userId, $this->lockoutDuration / 60]) ?? 0;
        return $count >= $this->maxLoginAttempts;
    }

    private function incrementFailedAttempts(int $userId): void
    {
        $sql = "INSERT INTO failed_activity_logs_unified (user_id, created_at) VALUES (?, NOW())";
        $this->db->execute($sql, [$userId]);
    }

    private function clearFailedAttempts(int $userId): void
    {
        $sql = "DELETE FROM failed_activity_logs_unified WHERE user_id = ?";
        $this->db->execute($sql, [$userId]);
    }

    private function createUserSession(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days
            
            try {
                $sql = "INSERT INTO remember_tokens (user_id, token, expires_at, created_at) 
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            
            $this->db->execute($sql, [$user['id'], $token, $expires, $token, $expires]);
            
            setcookie('remember_token', $token, [
                'expires' => time() + (30 * 24 * 3600),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    private function updateLastLogin(int $userId): void
    {
        $tid = $this->getTenantId();
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $this->db->execute($sql, $tid > 1 ? [$userId, $tid] : [$userId]);
    }

    private function updatePasswordHash(int $userId, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $tid = $this->getTenantId();
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $this->db->execute($sql, $tid > 1 ? [$hashedPassword, $userId, $tid] : [$hashedPassword, $userId]);
    }

    private function clearRememberToken(string $token): void
    {
        try {
            $sql = "DELETE FROM remember_tokens WHERE token = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
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
