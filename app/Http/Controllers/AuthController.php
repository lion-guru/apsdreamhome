<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use App\Http\Controllers\BaseController;

/**
 * Authentication Controller
 * Handles all authentication operations
 */
class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.login');
    }

    /**
     * Show register page
     */
    public function showRegister()
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.register');
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword()
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.forgot-password');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword($token)
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Process login
     */
    public function login()
    {
        $email = $this->request('email');
        $password = $this->request('password');
        $remember = $this->request('remember', false);

        $result = $this->authService->login($email, $password, $remember);

        if ($result['success']) {
            if ($result['locked']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                    'locked' => true
                ]);
            }

            // Redirect based on user role
            $user = $result['user'];
            $redirectUrl = '/dashboard';

            if ($user['role'] === 'admin') {
                $redirectUrl = '/admin/dashboard';
            } elseif ($user['role'] === 'agent') {
                $redirectUrl = '/agent/dashboard';
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $redirectUrl
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => $result['message'],
            'locked' => $result['locked'] ?? false
        ]);
    }

    /**
     * Process registration
     */
    public function register()
    {
        $userData = $this->request()->only([
            'name',
            'email',
            'password',
            'phone',
            'confirm_password'
        ]);

        // Validate password confirmation
        if ($userData['password'] !== $userData['confirm_password']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
        }

        unset($userData['confirm_password']);

        $result = $this->authService->register($userData);

        if ($result['success']) {
            return $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'redirect' => '/dashboard'
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => $result['message'],
            'errors' => $result['errors'] ?? []
        ]);
    }

    /**
     * Process logout
     */
    public function logout()
    {
        $this->authService->logout();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '/login'
        ]);
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset()
    {
        $email = $this->request('email');

        $result = $this->authService->requestPasswordReset($email);

        return $this->jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        $token = $this->request('token');
        $newPassword = $this->request('password');
        $confirmPassword = $this->request('confirm_password');

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
        }

        $result = $this->authService->resetPassword($token, $newPassword);

        return $this->jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $currentPassword = $this->request('current_password');
        $newPassword = $this->request('new_password');
        $confirmPassword = $this->request('confirm_password');

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'New passwords do not match'
            ]);
        }

        $result = $this->authService->changePassword($currentPassword, $newPassword);

        return $this->jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    /**
     * Get current user info
     */
    public function getCurrentUser()
    {
        $user = $this->authService->getCurrentUser();

        if ($user) {
            return $this->jsonResponse([
                'success' => true,
                'user' => $user
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'User not logged in'
        ]);
    }

    /**
     * Check authentication status
     */
    public function checkAuth()
    {
        $isLoggedIn = $this->authService->isLoggedIn();
        $user = $isLoggedIn ? $this->authService->getCurrentUser() : null;

        return $this->jsonResponse([
            'success' => true,
            'authenticated' => $isLoggedIn,
            'user' => $user,
            'is_admin' => $this->authService->isAdmin(),
            'is_agent' => $this->authService->isAgent(),
            'is_customer' => $this->authService->isCustomer()
        ]);
    }

    /**
     * Get authentication statistics (admin only)
     */
    public function getAuthStats()
    {
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $stats = $this->authService->getAuthStats();

        return $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Show profile page
     */
    public function showProfile()
    {
        $user = $this->authService->requireAuth();

        return $this->view('auth.profile', ['user' => $user]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        $user = $this->authService->requireAuth();

        $data = $this->request()->only(['name', 'phone']);

        // Validate required fields
        if (empty($data['name']) || empty($data['phone'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Name and phone are required'
            ]);
        }

        // Update user in database
        try {
            $sql = "UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$data['name'], $data['phone'], $user['id']]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update profile'
            ]);
        }
    }

    /**
     * Show change password page
     */
    public function showChangePassword()
    {
        $this->authService->requireAuth();

        return $this->view('auth.change-password');
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        // TODO: Implement email verification logic
        return $this->view('auth.email-verified', ['success' => true]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement email resend logic
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Verification email sent'
        ]);
    }

    /**
     * Enable two-factor authentication
     */
    public function enable2FA()
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement 2FA setup
        return $this->jsonResponse([
            'success' => false,
            'message' => '2FA not yet implemented'
        ]);
    }

    /**
     * Disable two-factor authentication
     */
    public function disable2FA()
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement 2FA disable
        return $this->jsonResponse([
            'success' => false,
            'message' => '2FA not yet implemented'
        ]);
    }

    /**
     * Get login history
     */
    public function getLoginHistory()
    {
        $user = $this->authService->requireAuth();

        try {
            $sql = "SELECT * FROM login_attempts 
                    WHERE email = ? 
                    ORDER BY created_at DESC 
                    LIMIT 20";

            $history = $this->db->fetchAll($sql, [$user['email']]);

            return $this->jsonResponse([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get login history'
            ]);
        }
    }

    /**
     * Clear login history
     */
    public function clearLoginHistory()
    {
        $user = $this->authService->requireAuth();

        try {
            $sql = "DELETE FROM login_attempts WHERE email = ?";
            $this->db->execute($sql, [$user['email']]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Login history cleared'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear login history'
            ]);
        }
    }

    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement session management
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                [
                    'id' => 'current',
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'last_activity' => date('Y-m-d H:i:s'),
                    'is_current' => true
                ]
            ]
        ]);
    }

    /**
     * Revoke session
     */
    public function revokeSession($sessionId)
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement session revocation
        return $this->jsonResponse([
            'success' => false,
            'message' => 'Session management not yet implemented'
        ]);
    }

    /**
     * Get request input
     */
    private function request($key = null)
    {
        if ($key === null) {
            return $_REQUEST;
        }
        return $_REQUEST[$key] ?? null;
    }
}
