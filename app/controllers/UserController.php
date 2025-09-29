<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\GoogleAuthService;

class UserController extends Controller {
    private $userService;
    private $googleAuthService;

    public function __construct() {
        parent::__construct();
        $this->userService = new UserService();
        $this->googleAuthService = new GoogleAuthService();
    }

    /**
     * Display user profile
     */
    public function profile() {
        $this->requireLogin();

        $user = $this->auth->user();

        $this->view('users/profile', [
            'title' => 'My Profile',
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile() {
        $this->requireLogin();
        
        try {
            $data = [
                'username' => $_POST['username'] ?? '',
                'mobile' => $_POST['mobile'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'country' => $_POST['country'] ?? '',
                'pincode' => $_POST['pincode'] ?? ''
            ];
            
            $result = $this->userService->updateProfile($_SESSION['user_id'], $data);
            
            if ($result) {
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $_POST;
        }
        
        $this->redirect('/profile');
    }

    /**
     * Change password
     */
    public function changePassword() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    throw new \Exception('All fields are required');
                }
                
                if ($newPassword !== $confirmPassword) {
                    throw new \Exception('New password and confirm password do not match');
                }
                
                $result = $this->userService->changePassword(
                    $_SESSION['user_id'],
                    $currentPassword,
                    $newPassword
                );
                
                if ($result) {
                    $_SESSION['success'] = 'Password changed successfully!';
                    $this->redirect('/profile');
                    return;
                }
                
                throw new \Exception('Failed to change password');
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        $this->view('users/change_password', [
            'title' => 'Change Password'
        ]);
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
            return;
        }

        $this->view('auth/forgot_password', [
            'title' => 'Forgot Password'
        ]);
    }

    /**
     * Handle forgot password request
     */
    public function sendPasswordReset() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Please provide a valid email address';
                $this->redirect('/forgot-password');
                return;
            }
            
            try {
                $result = $this->userService->requestPasswordReset($email);
                
                if ($result) {
                    $_SESSION['success'] = 'If an account exists with this email, a password reset link has been sent.';
                    $this->redirect('/login');
                    return;
                }
                
                throw new \Exception('Failed to send password reset email');
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $this->redirect('/forgot-password');
            }
        }
        
        $this->redirect('/forgot-password');
    }

    /**
     * Show reset password form
     */
    public function resetPasswordForm($token) {
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid reset token';
            $this->redirect('/login');
            return;
        }
        
        $this->view('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All fields are required';
            $this->redirect("/reset-password/$token");
            return;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            $this->redirect("/reset-password/$token");
            return;
        }
        
        try {
            $result = $this->userService->resetPassword($token, $password);
            
            if ($result) {
                $_SESSION['success'] = 'Your password has been reset successfully. Please login with your new password.';
                $this->redirect('/login');
                return;
            }
            
            throw new \Exception('Failed to reset password');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect("/reset-password/$token");
        }
    }

    /**
     * Google OAuth login
     */
    public function googleLogin() {
        $authUrl = $this->googleAuthService->getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Google OAuth callback
     */
    public function googleCallback() {
        if (isset($_GET['code'])) {
            try {
                $user = $this->googleAuthService->handleCallback($_GET['code']);
                
                if ($user) {
                    // Log the user in
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_email'] = $user->email;
                    $_SESSION['user_role'] = $user->role;
                    
                    $_SESSION['success'] = 'Logged in successfully with Google!';
                    $this->redirect('/dashboard');
                    return;
                }
                
                throw new \Exception('Failed to authenticate with Google');
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Invalid request';
        }
        
        $this->redirect('/login');
    }
}
