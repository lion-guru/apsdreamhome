<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use App\Http\Controllers\Controller;

/**
 * Authentication Controller
 * Handles all authentication operations
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
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
        
        return view('auth.login');
    }

    /**
     * Show register page
     */
    public function showRegister()
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword()
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }
        
        return view('auth.forgot-password');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword($token)
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }
        
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Process login
     */
    public function login()
    {
        $email = request('email');
        $password = request('password');
        $remember = request('remember', false);

        $result = $this->authService->login($email, $password, $remember);

        if ($result['success']) {
            if ($result['locked']) {
                return response()->json([
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

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $redirectUrl
            ]);
        }

        return response()->json([
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
        $userData = request()->only([
            'name', 'email', 'password', 'phone', 'confirm_password'
        ]);

        // Validate password confirmation
        if ($userData['password'] !== $userData['confirm_password']) {
            return response()->json([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
        }

        unset($userData['confirm_password']);

        $result = $this->authService->register($userData);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json([
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
        
        return response()->json([
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
        $email = request('email');

        $result = $this->authService->requestPasswordReset($email);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        $token = request('token');
        $newPassword = request('password');
        $confirmPassword = request('confirm_password');

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
        }

        $result = $this->authService->resetPassword($token, $newPassword);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $currentPassword = request('current_password');
        $newPassword = request('new_password');
        $confirmPassword = request('confirm_password');

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            return response()->json([
                'success' => false,
                'message' => 'New passwords do not match'
            ]);
        }

        $result = $this->authService->changePassword($currentPassword, $newPassword);

        return response()->json([
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
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        }

        return response()->json([
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

        return response()->json([
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
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $stats = $this->authService->getAuthStats();

        return response()->json([
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
        
        return view('auth.profile', ['user' => $user]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        $user = $this->authService->requireAuth();

        $data = request()->only(['name', 'phone']);

        // Validate required fields
        if (empty($data['name']) || empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Name and phone are required'
            ]);
        }

        // Update user in database
        try {
            $sql = "UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$data['name'], $data['phone'], $user['id']]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
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
        
        return view('auth.change-password');
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        // TODO: Implement email verification logic
        return view('auth.email-verified', ['success' => true]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        $user = $this->authService->requireAuth();

        // TODO: Implement email resend logic
        return response()->json([
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
        return response()->json([
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
        return response()->json([
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

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
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

            return response()->json([
                'success' => true,
                'message' => 'Login history cleared'
            ]);

        } catch (\Exception $e) {
            return response()->json([
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
        return response()->json([
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
        return response()->json([
            'success' => false,
            'message' => 'Session management not yet implemented'
        ]);
    }
}
