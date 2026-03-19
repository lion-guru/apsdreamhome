<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom Authentication Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class AuthenticationService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * User login
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        try {
            // Rate limiting check
            $cacheKey = "login_attempts_" . md5($email);
            if (!CoreFunctionsServiceCustom::checkRateLimit($cacheKey, 5, 300)) {
                return [
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'error_code' => 'RATE_LIMITED'
                ];
            }
            
            // Find user
            $sql = "SELECT id, name, email, password, role, status, created_at FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password',
                    'error_code' => 'INVALID_CREDENTIALS'
                ];
            }
            
            // Check account status
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Account is not active',
                    'error_code' => 'ACCOUNT_INACTIVE'
                ];
            }
            
            // Verify password
            if (!CoreFunctionsServiceCustom::verifyPasswordHash($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password',
                    'error_code' => 'INVALID_CREDENTIALS'
                ];
            }
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = CoreFunctionsServiceCustom::generateRandomString(64);
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store remember token
                $sql = "UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $user['id']]);
                
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            // Log admin action
            if ($user['role'] === 'admin') {
                CoreFunctionsServiceCustom::logAdminAction([
                    'action' => 'login',
                    'email' => $email,
                    'ip' => CoreFunctionsServiceCustom::getClientIp()
                ]);
            }
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * User registration
     */
    public function register(array $userData): array
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst($field) . ' is required',
                        'error_code' => 'MISSING_FIELD'
                    ];
                }
            }
            
            // Validate email
            $email = CoreFunctionsServiceCustom::validateInput($userData['email'], 'email');
            if (!$email) {
                return [
                    'success' => false,
                    'message' => 'Invalid email address',
                    'error_code' => 'INVALID_EMAIL'
                ];
            }
            
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email already registered',
                    'error_code' => 'EMAIL_EXISTS'
                ];
            }
            
            // Validate password
            $password = $userData['password'];
            if (strlen($password) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 8 characters',
                    'error_code' => 'WEAK_PASSWORD'
                ];
            }
            
            // Hash password
            $hashedPassword = CoreFunctionsServiceCustom::hashPassword($password);
            
            // Create user
            $sql = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($userData['name'], 'string'),
                $email,
                $hashedPassword,
                $userData['role']
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create user');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Log admin action
            CoreFunctionsServiceCustom::logAdminAction([
                'action' => 'user_registered',
                'user_id' => $userId,
                'email' => $email,
                'role' => $userData['role']
            ]);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * User logout
     */
    public function logout(): bool
    {
        try {
            // Log admin action
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                CoreFunctionsServiceCustom::logAdminAction([
                    'action' => 'logout',
                    'user_id' => $_SESSION['user_id'] ?? 0
                ]);
            }
            
            // Clear session
            session_unset();
            session_destroy();
            
            // Clear remember me cookie
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
                unset($_COOKIE['remember_token']);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return CoreFunctionsServiceCustom::isAuthenticated();
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $sql = "SELECT id, name, email, role, status, created_at FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch() ?: null;
            
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            // Get current user
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }
            
            // Verify current password
            if (!CoreFunctionsServiceCustom::verifyPasswordHash($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'error_code' => 'INVALID_CURRENT_PASSWORD'
                ];
            }
            
            // Validate new password
            if (strlen($newPassword) < 8) {
                return [
                    'success' => false,
                    'message' => 'New password must be at least 8 characters',
                    'error_code' => 'WEAK_PASSWORD'
                ];
            }
            
            // Update password
            $hashedPassword = CoreFunctionsServiceCustom::hashPassword($newPassword);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if (!$result) {
                throw new Exception('Failed to update password');
            }
            
            // Log admin action
            CoreFunctionsServiceCustom::logAdminAction([
                'action' => 'password_updated',
                'user_id' => $userId
            ]);
            
            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update password',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword(string $email): array
    {
        try {
            // Find user
            $sql = "SELECT id, name FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found',
                    'error_code' => 'EMAIL_NOT_FOUND'
                ];
            }
            
            // Generate reset token
            $token = CoreFunctionsServiceCustom::generateRandomString(64);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Store reset token
            $sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$token, $expires, $user['id']]);
            
            if (!$result) {
                throw new Exception('Failed to store reset token');
            }
            
            // TODO: Send reset email (implement email service)
            
            // Log admin action
            CoreFunctionsServiceCustom::logAdminAction([
                'action' => 'password_reset_requested',
                'user_id' => $user['id'],
                'email' => $email
            ]);
            
            return [
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ];
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process password reset',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin(int $userId): void
    {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Failed to update last login: " . $e->getMessage());
        }
    }
    
    /**
     * Check user permissions
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'] ?? '';
        
        // Simple role-based permissions
        $permissions = [
            'admin' => ['*'], // All permissions
            'manager' => ['view', 'create', 'edit'],
            'associate' => ['view', 'create'],
            'customer' => ['view']
        ];
        
        $userPermissions = $permissions[$userRole] ?? [];
        
        return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
    }
}