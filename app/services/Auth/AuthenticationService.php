<?php

namespace App\Services\Auth;

use App\Core\Database\Database;
use App\Core\Session\Session;

/**
 * Custom Authentication Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class AuthenticationService
{
    private $db;
    private $session;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = new Session();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return $this->session->get('user_id') !== null;
    }

    /**
     * Get current user
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        return $this->db->fetchOne(
            "SELECT id, name, email, role, created_at FROM users WHERE id = ? AND deleted_at IS NULL",
            [$userId]
        );
    }

    /**
     * Get user role
     */
    public function getUserRole()
    {
        $user = $this->getCurrentUser();
        return $user ? $user['role'] : 'guest';
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        $role = $this->getUserRole();

        $permissions = [
            'admin' => ['admin_access', 'view_logs', 'export_logs', 'clean_logs', 'manage_security_alerts'],
            'manager' => ['view_logs', 'export_logs'],
            'associate' => ['view_dashboard'],
            'user' => ['view_profile']
        ];

        return in_array($permission, $permissions[$role] ?? []);
    }

    /**
     * Attempt user login
     */
    public function login($email, $password, $remember = false)
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
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
                [$email]
            );

            if (!$user) {
                $this->recordLoginAttempt($email, false);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->recordLoginAttempt($email, false);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Successful login
            $this->recordLoginAttempt($email, true);
            $this->createSession($user, $remember);

            return [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $this->getRedirectUrl($user['role'])
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Register new user
     */
    public function register($userData)
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst($field) . ' is required'
                    ];
                }
            }

            // Check if email already exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL",
                [$userData['email']]
            );

            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_ARGON2ID);

            // Insert user
            $userId = $this->db->insert('users', [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'role' => $userData['role'],
                'phone' => $userData['phone'] ?? null,
                'address' => $userData['address'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($userId) {
                return [
                    'success' => true,
                    'message' => 'Registration successful. Please login.',
                    'user_id' => $userId
                ];
            }

            return [
                'success' => false,
                'message' => 'Registration failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Reset password
     */
    public function resetPassword($email)
    {
        try {
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
                [$email]
            );

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found'
                ];
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Update user with reset token
            $updated = $this->db->update('users', [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);

            if ($updated) {
                // TODO: Send email with reset link
                return [
                    'success' => true,
                    'message' => 'Password reset link sent to your email'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to generate reset link'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Password reset failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Get user
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL",
                [$userId]
            );

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Verify current password if provided
            if ($currentPassword && !password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);

            // Update password
            $updated = $this->db->update('users', [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$userId]);

            if ($updated) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to change password'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Password change failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get redirect URL based on user role
     */
    public function getRedirectUrl($role)
    {
        $redirects = [
            'admin' => '/admin/dashboard',
            'manager' => '/dashboard',
            'associate' => '/dashboard',
            'user' => '/dashboard'
        ];

        return $redirects[$role] ?? '/dashboard';
    }

    /**
     * Check rate limiting for login attempts
     */
    private function checkRateLimit($email)
    {
        $recentAttempts = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM login_logs 
             WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0",
            [$email]
        )['count'];

        return $recentAttempts < $this->maxLoginAttempts;
    }

    /**
     * Record login attempt
     */
    private function recordLoginAttempt($email, $success)
    {
        $this->db->insert('login_logs', [
            'email' => $email,
            'success' => $success ? 1 : 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create user session
     */
    private function createSession($user, $remember = false)
    {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('user_name', $user['name']);

        if ($remember) {
            $this->session->set('remember_me', true);
            // Set cookie for 30 days
            setcookie('remember_token', bin2hex(random_bytes(16)), time() + (30 * 24 * 60 * 60), '/');
        }
    }
}
