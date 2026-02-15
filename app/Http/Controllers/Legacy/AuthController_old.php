<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use App\Services\EmailService;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Show login form
    public function login() {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $data = [
            'title' => 'Login',
            'email' => '',
            'password' => '',
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? ''
        ];
        
        // Clear flash messages
        unset($_SESSION['error'], $_SESSION['success']);
        
        $this->view('auth/login', $data);
    }

    // Process login
    public function authenticate() {
        $this->validateCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize_input(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Please enter both email and password');
                header('Location: /login');
                exit;
            }

            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if ($user && $user->verifyPassword($password)) {
                if ($user->isActive()) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['user_role'] = $user->role;
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

                    // Redirect based on user role
                    if ($user->isAdmin()) {
                        header('Location: /admin/dashboard');
                    } else {
                        header('Location: /dashboard');
                    }
                    exit;
                } else {
                    $this->setFlash('error', 'Your account is not active. Please contact support.');
                }
            } else {
                $this->setFlash('error', 'Invalid email or password');
            }

            header('Location: /login');
            exit;
        }

        // If not a POST request, redirect to login
        header('Location: /login');
        exit;
    }
    
    // Show registration form
    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $data = [
            'title' => 'Register',
            'username' => '',
            'email' => '',
            'mobile' => '',
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? ''
        ];
        
        // Clear flash messages
        unset($_SESSION['error'], $_SESSION['success']);
        
        $this->view('auth/register', $data);
    }
    
    // Process registration
    public function store() {
        $this->validateCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize_input(trim($_POST['username'] ?? ''));
            $email = sanitize_input(trim($_POST['email'] ?? ''));
            $mobile = sanitize_input(trim($_POST['mobile'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validate input
            $errors = [];

            if (empty($username)) {
                $errors[] = 'Username is required';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors[] = 'Email already registered';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match';
            }

            if (empty($errors)) {
                // Create new user
                $user = new User();
                $user->username = $username;
                $user->email = $email;
                $user->mobile = $mobile;
                $user->setPassword($password);
                $user->role = 'user'; // Default role
                $user->status = 'pending'; // Set to pending until email is verified
                $user->email_verified_at = null;

                if ($user->save()) {
                    // Create and send verification email
                    $token = EmailVerification::createToken($user->id);

                    if ($token) {
                        $emailService = new EmailService();
                        $emailSent = $emailService->sendVerificationEmail(
                            $user->email,
                            $user->username,
                            $token
                        );

                        if ($emailSent) {
                            $this->setFlash('success', 'Registration successful! Please check your email to verify your account.');
                            header('Location: /login');
                            exit;
                        } else {
                            $errors[] = 'Account created but failed to send verification email. Please contact support.';
                        }
                    } else {
                        $errors[] = 'Failed to create verification token. Please try again.';
                    }
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }
            }

            // If there are errors, store them and redirect back
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
                $_SESSION['form_data'] = [
                    'username' => $username,
                    'email' => $email,
                    'mobile' => $mobile
                ];
                header('Location: /register');
                exit;
            }
        }

        // If not a POST request, redirect to register
        header('Location: /register');
        exit;
    }
    
    // Logout
    public function logout() {
        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        // Redirect to login page
        header('Location: /login');
        exit;
    }
    
    /**
     * Verify user's email
     */
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlash('error', 'Invalid verification link');
            header('Location: /login');
            exit;
        }
        
        // Find the verification token
        $verification = EmailVerification::isValidToken($token);
        
        if ($verification) {
            // Get the user
            $user = $this->userModel->find($verification->user_id);
            
            if ($user) {
                // Update user's status and verification timestamp
                $user->status = 'active';
                $user->email_verified_at = date('Y-m-d H:i:s');
                
                if ($user->save()) {
                    // Delete the verification token
                    $verification->delete();
                    
                    $this->setFlash('success', 'Email verified successfully! You can now login.');
                    header('Location: /login');
                    exit;
                }
            }
        }
        
        $this->setFlash('error', 'Invalid or expired verification link');
        header('Location: /login');
        exit;
    }
    
    /**
     * Resend verification email
     */
    public function resendVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please provide a valid email address');
            header('Location: /login');
            exit;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            if ($user->status === 'active' && $user->email_verified_at) {
                $this->setFlash('error', 'Your email is already verified. Please login.');
            } else {
                // Create and send new verification email
                $token = EmailVerification::createToken($user->id);
                
                if ($token) {
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendVerificationEmail(
                        $user->email, 
                        $user->username, 
                        $token
                    );
                    
                    if ($emailSent) {
                        $this->setFlash('success', 'Verification email sent! Please check your inbox.');
                    } else {
                        $this->setFlash('error', 'Failed to send verification email. Please try again later.');
                    }
                } else {
                    $this->setFlash('error', 'Failed to create verification token. Please try again.');
                }
            }
        } else {
            // For security, don't reveal if the email exists or not
            $this->setFlash('success', 'If an account exists with this email, a verification link has been sent.');
        }
        
        header('Location: /login');
        exit;
    }
}
