<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\PasswordOtpService;
use App\Core\Middleware\TenantContext;

/**
 * Custom Authentication Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class AuthenticationController extends BaseController
{
    private $authService;
    private $otpService;
    private $viewRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthenticationService();
        $this->otpService = new PasswordOtpService();
        $this->viewRenderer = new \App\Core\View();
    }

    /**
     * This controller validates CSRF manually inside each method,
     * so skip the parent's automatic CSRF check on POST.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
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
            'page_title' => 'Forgot Password - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/forgot_password', $data);
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
            'page_title' => 'Reset Password - APS Dream Home',
            'token' => $token,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        // Clear session messages
        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/reset_password', $data);
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

        // Use OTP service which handles token-based reset
        $result = $this->otpService->resetPasswordWithToken($token, $password);

        if ($result['success']) {
            $_SESSION['success'] = 'Password reset successful. Please login with your new password.';
            $this->redirect('/login');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $_POST;
            $this->redirect('/reset-password?token=' . $token);
        }
    }

    /**
     * Send OTP for forgot password (AJAX)
     */
    public function sendForgotOtp()
    {
        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            exit;
        }

        $result = $this->otpService->sendOtp($email, 'password_reset');
        // Don't reveal if email exists - return generic success
        if (isset($result['silent']) && $result['silent']) {
            echo json_encode(['success' => true, 'message' => 'If an account exists with this email, an OTP has been sent.']);
            exit;
        }
        echo json_encode($result);
        exit;
    }

    /**
     * Verify OTP and issue reset token (AJAX)
     */
    public function verifyForgotOtp()
    {
        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        $otp = $_POST['otp'] ?? '';

        $result = $this->otpService->verifyOtp($email, $otp, 'password_reset');

        if ($result['success']) {
            // Stash reset token in session so reset-password page can use it
            $_SESSION['reset_token'] = $result['reset_token'];
            $_SESSION['reset_email'] = $email;
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Send OTP for change password (logged-in user, AJAX)
     */
    public function sendChangePasswordOtp()
    {
        header('Content-Type: application/json');

        if (!$this->authService->isAuthenticated()) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }

        $user = $this->authService->getCurrentUser();
        $result = $this->otpService->sendOtp($user['email'], 'change_password');

        echo json_encode($result);
        exit;
    }

    /**
     * Verify change-password OTP and change password (AJAX)
     */
    public function verifyChangePasswordOtp()
    {
        header('Content-Type: application/json');

        if (!$this->authService->isAuthenticated()) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }

        $user = $this->authService->getCurrentUser();
        $otp = $_POST['otp'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit;
        }

        $verify = $this->otpService->verifyOtp($user['email'], $otp, 'change_password');
        if (!$verify['success']) {
            echo json_encode($verify);
            exit;
        }

        // OTP verified - now change password (skip current password check since OTP = identity proof)
        $result = $this->authService->changePassword($user['id'], $currentPassword, $newPassword);
        echo json_encode($result);
        exit;
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

    private function getUserByResetToken($token)
    {
        $database = \App\Core\Database::getInstance();
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log($e->getMessage()); }
        return $database->fetchOne(
            "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            ($tid > 1 ? [$token, $tid] : [$token])
        );
    }

    private function clearResetToken($userId)
    {
        $database = \App\Core\Database::getInstance();
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log($e->getMessage()); }
        $whereClause = 'id = ?';
        $whereParams = [$userId];
        if ($tid > 1) {
            $whereClause .= ' AND tenant_id = ?';
            $whereParams[] = $tid;
        }
        return $database->update(
            'users',
            ['reset_token' => null, 'reset_token_expiry' => null, 'updated_at' => date('Y-m-d H:i:s')],
            $whereClause,
            $whereParams
        );
    }
}
