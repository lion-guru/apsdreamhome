<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\AuthenticationService;

/**
 * Custom Authentication Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class AuthenticationController
{
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new \App\Core\View();
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $redirectUrl = $this->authService->getRedirectUrl($this->authService->getUserRole());
            $this->redirect($redirectUrl);
            return;
        }

        $data = [
            'title' => 'Login - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/login', $data);
    }

    /**
     * Process login
     */
    public function login()
    {
        // Get POST data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ['Invalid request'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $result = $this->authService->login($email, $password, $remember);

        if ($result['success']) {
            // Set success message
            $_SESSION['success'] = $result['message'];

            // Redirect to intended URL or default
            $redirectUrl = $_SESSION['intended_url'] ?? $result['redirect'];
            unset($_SESSION['intended_url']);

            $this->redirect($redirectUrl);
        } else {
            // Set error message
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;

            $this->redirect('/login');
        }
    }

    /**
     * Show registration page
     */
    public function showRegister()
    {
        // If already logged in, redirect to dashboard
        if ($this->authService->isAuthenticated()) {
            $redirectUrl = $this->authService->getRedirectUrl($this->authService->getUserRole());
            $this->redirect($redirectUrl);
            return;
        }

        $data = [
            'title' => 'Register - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? [],
            'roles' => [
                'user' => 'User',
                'associate' => 'Associate',
                'manager' => 'Manager'
            ]
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/register', $data);
    }

    /**
     * Process registration
     */
    public function register()
    {
        // Get POST data
        $userData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? '',
            'role' => $_POST['role'] ?? 'user',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ['Invalid request'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/register');
            return;
        }

        // Validate password confirmation
        if ($userData['password'] !== $userData['password_confirmation']) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/register');
            return;
        }

        // Remove password confirmation from user data
        unset($userData['password_confirmation']);

        // Attempt registration
        $result = $this->authService->register($userData);

        if ($result['success']) {
            // Set success message
            $_SESSION['success'] = $result['message'];

            $this->redirect('/login');
        } else {
            // Set error message
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;

            $this->redirect('/register');
        }
    }

    /**
     * Process logout
     */
    public function logout()
    {
        $result = $this->authService->logout();

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/login');
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword()
    {
        $data = [
            'title' => 'Forgot Password - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/forgot-password', $data);
    }

    /**
     * Process forgot password
     */
    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';

        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ['Invalid request'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/forgot-password');
            return;
        }

        $result = $this->authService->resetPassword($email);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;
        }

        $this->redirect('/forgot-password');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ['Invalid reset token'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Reset Password - APS Dream Home',
            'token' => $token,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/reset-password', $data);
    }

    /**
     * Process reset password
     */
    public function resetPassword()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ['Invalid request'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/reset-password?token=' . $token);
            return;
        }

        // Validate password confirmation
        if ($password !== $passwordConfirmation) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/reset-password?token=' . $token);
            return;
        }

        // Validate token and get user
        $user = $this->getUserByResetToken($token);
        if (!$user) {
            $_SESSION['errors'] = ['Invalid or expired reset token'];
            $this->redirect('/login');
            return;
        }

        // Change password
        $result = $this->authService->changePassword($user['id'], '', $password);

        if ($result['success']) {
            // Clear reset token
            $this->clearResetToken($user['id']);

            $_SESSION['success'] = 'Password reset successful. Please login with your new password.';
            $this->redirect('/login');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;
            $this->redirect('/reset-password?token=' . $token);
        }
    }

    /**
     * Show change password page
     */
    public function showChangePassword()
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Change Password - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/change-password', $data);
    }

    /**
     * Process change password
     */
    public function changePassword()
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            return;
        }

        $user = $this->authService->getCurrentUser();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ['Invalid request'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/change-password');
            return;
        }

        // Validate password confirmation
        if ($newPassword !== $passwordConfirmation) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            $_SESSION['old'] = $_POST;
            $this->redirect('/change-password');
            return;
        }

        $result = $this->authService->changePassword($user['id'], $currentPassword, $newPassword);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/dashboard');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;
            $this->redirect('/change-password');
        }
    }

    /**
     * Show profile page
     */
    public function showProfile()
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'My Profile - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'errors' => $_SESSION['errors'] ?? [],
            'success' => $_SESSION['success'] ?? ''
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['success']);

        return $this->viewRenderer->render('auth/profile', $data);
    }

    /**
     * Get authentication status (AJAX endpoint)
     */
    public function getAuthStatus()
    {
        header('Content-Type: application/json');

        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode([
                'authenticated' => false
            ]);
        }
        exit;
    }

    /**
     * Check permission (AJAX endpoint)
     */
    public function checkPermission()
    {
        header('Content-Type: application/json');

        if (!$this->authService->isAuthenticated()) {
            echo json_encode([
                'has_permission' => false,
                'reason' => 'not_authenticated'
            ]);
            exit;
        }

        $permission = $_GET['permission'] ?? '';
        $hasPermission = $this->authService->hasPermission($permission);

        echo json_encode([
            'has_permission' => $hasPermission,
            'permission' => $permission,
            'user_role' => $this->authService->getUserRole()
        ]);
        exit;
    }

    /**
     * Get current user (AJAX endpoint)
     */
    public function getCurrentUser()
    {
        header('Content-Type: application/json');

        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }
        exit;
    }

    // Private helper methods

    private function validateCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    private function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    private function getUserByResetToken($token)
    {
        $database = \App\Core\Database::getInstance();
        return $database->fetchOne(
            "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND deleted_at IS NULL",
            [$token]
        );
    }

    private function clearResetToken($userId)
    {
        $database = \App\Core\Database::getInstance();
        return $database->update(
            'users',
            ['reset_token' => null, 'reset_token_expiry' => null, 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$userId]
        );
    }
}
