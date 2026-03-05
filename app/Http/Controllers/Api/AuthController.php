<?php

namespace App\Http\Controllers\Api;

use \Exception;

class AuthController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['login']]);
    }

    /**
     * Authenticate user and generate JWT token
     */
    public function login()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed. Use POST.', 405);
        }

        try {
            $email = $this->request()->input('email');
            $password = $this->request()->input('password');

            if (empty($email) || empty($password)) {
                return $this->jsonError('Email and password are required', 400);
            }

            $token = $this->auth->login($email, $password);

            if (!$token) {
                return $this->jsonError('Invalid email or password', 401);
            }

            $userPayload = $this->auth->validateToken($token);
            $user = $this->model('User')->find($userPayload['sub']);

            if (!$user) {
                return $this->jsonError('User not found after authentication', 404);
            }

            return $this->jsonSuccess([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'token' => $token,
                'expires_in' => 86400,
                'token_type' => 'Bearer'
            ], 'Login successful');

        } catch (Exception $e) {
            return $this->jsonError('Authentication failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current user info
     */
    public function me()
    {
        try {
            $user = $this->auth->user();

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            $userModel = $this->model('User');

            // Get roles and permissions
            $roles = $userModel->getRoles($user->id);
            $permissions = $userModel->getPermissions($user->id);

            return $this->jsonSuccess([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'roles' => $roles,
                'permissions' => $permissions
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Refresh authentication token
     */
    public function refresh()
    {
        try {
            $newToken = $this->auth->refreshToken();

            if (!$newToken) {
                return $this->jsonError('Token refresh failed', 401);
            }

            return $this->jsonSuccess([
                'token' => $newToken,
                'expires_in' => 86400,
                'token_type' => 'Bearer'
            ], 'Token refreshed');

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Log out current user
     */
    public function logout()
    {
        try {
            $this->auth->logout();
            return $this->jsonSuccess(null, 'Logged out successfully');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function showLogin()
    {
        if (AuthHelper::isLoggedIn('admin')) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->data['page_title'] = 'Admin Login - ' . APP_NAME;
        $this->data['error'] = $this->getFlash('error') ?? ($_GET['error'] ?? '');
        $this->data['success'] = $this->getFlash('success') ?? ($_GET['success'] ?? '');

        // Generate simple CAPTCHA
        $num1 = SecurityHelper::secureRandomInt(1, 10);
        $num2 = SecurityHelper::secureRandomInt(1, 10);
        $_SESSION['captcha_num1_admin'] = $num1;
        $_SESSION['captcha_num2_admin'] = $num2;
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $this->data['captcha_question'] = "$num1 + $num2 = ?";

        // Generate CSRF token if not already in view
        if (!isset($this->data['csrf_token'])) {
            $this->data['csrf_token'] = SecurityHelper::generateCsrfToken();
        }

        return $this->render('admin/login');
    }

    public function processLogin()
    {
        $username = trim(Security::sanitize($_POST['username']) ?? '');
        $password = Security::sanitize($_POST['password']) ?? '';
        $captcha = Security::sanitize($_POST['captcha_answer']) ?? '';

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Please fill in all fields');
            $this->redirect('/admin/login');
        }

        // Verify CAPTCHA
        if (!isset($_SESSION['captcha_answer']) || (int)$captcha !== (int)$_SESSION['captcha_answer']) {
            $this->setFlash('error', 'Incorrect security answer');
            $this->redirect('/admin/login');
        }

        // Check if account is locked
        if (isset($_SESSION['admin_login_blocked_until']) && $_SESSION['admin_login_blocked_until'] > time()) {
            $this->setFlash('error', 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']));
            $this->redirect('/admin/login');
        }

        try {
            $adminModel = new Admin();
            // Fetch admin from database using email or username
            $admin = $adminModel->findByUsernameOrEmail($username);

            if (!$admin) {
                return $this->handleFailedLogin($username);
            }

            // Verify password with support for multiple hash columns and legacy SHA1
            $hash = $admin->apass ?? $admin->password;
            $verified = false;

            if ($hash && password_verify($password, $hash)) {
                $verified = true;
            } elseif ($hash && preg_match('/^[a-f0-9]{40}$/i', $hash) && sha1($password) === $hash) {
                // Legacy SHA1 support
                $verified = true;

                // Rehash password to bcrypt and update database
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $admin->password = $newHash;

                // If using apass column for legacy compatibility, update it too if it exists in fillable
                if ($admin->apass) {
                    $admin->apass = $newHash;
                }

                $admin->save();
            }

            if (!$verified) {
                return $this->handleFailedLogin($username);
            }

            // Check admin status
            if (isset($admin->status) && $admin->status !== 'active') {
                $this->setFlash('error', 'Account is not active');
                $this->redirect('/admin/login');
            }

            // Successful login
            // Check if password needs rehash
            if (password_needs_rehash($admin->apass, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $adminModel->update($admin->aid, ['apass' => $new_hash]);
            }

            // Set session using unified helper
            // Note: the helper uses an array, so we convert the model object to array if needed
            $userData = $admin->toArray();
            // Use static method for unified session handling
            SessionHelpers::setAuthSession($userData, 'admin', $admin->role ?? 'admin');

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Log success
            error_log("[Admin Login Success] User: " . $admin->auser . " IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            $this->setFlash('success', 'Logged in successfully');
            $this->redirect('/admin/dashboard');
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            $this->setFlash('error', 'An unexpected error occurred');
            $this->redirect('/admin/login');
        }
    }

    public function authenticate()
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new Exception('Security validation failed');
            }

            // Get form data
            $email = trim(Security::sanitize($_POST['email']) ?? '');
            $password = Security::sanitize($_POST['password']) ?? '';
            $remember = isset(Security::sanitize($_POST['remember']));

            // Validate input
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            // Check user credentials
            $user = $this->db->table('users')
                ->where('email', $email)
                ->where('status', 'active')
                ->first();

            if (!$user) {
                throw new Exception('Invalid email or password');
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                throw new Exception('Invalid email or password');
            }

            // Update last login
            $this->db->table('users')
                ->where('id', $user['id'])
                ->update([
                    'last_login' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days

                // Store token in database
                $this->db->table('user_sessions')->insert([
                    'user_id' => $user['id'],
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Log login activity
            $this->logUserActivity($user['id'], 'login', 'User logged in successfully');

            // Redirect to intended page or dashboard
            $redirectUrl = $_SESSION['redirect_url'] ?? '/dashboard';
            unset($_SESSION['redirect_url']);

            $this->setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
            $this->redirect($redirectUrl);

        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function register()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->render('auth/register', [
            'page_title' => 'Register - APS Dream Home',
            'page_description' => 'Create your APS Dream Home account'
        ], 'layouts/base');
    }

    public function store()
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new Exception('Security validation failed');
            }

            // Get form data
            $firstName = trim(Security::sanitize($_POST['first_name']) ?? '');
            $lastName = trim(Security::sanitize($_POST['last_name']) ?? '');
            $email = trim(Security::sanitize($_POST['email']) ?? '');
            $phone = trim(Security::sanitize($_POST['phone']) ?? '');
            $password = Security::sanitize($_POST['password']) ?? '';
            $confirmPassword = Security::sanitize($_POST['confirm_password']) ?? '';
            $userType = Security::sanitize($_POST['user_type']) ?? '';
            $agreeTerms = isset(Security::sanitize($_POST['agree_terms']));
            $subscribeNewsletter = isset(Security::sanitize($_POST['subscribe_newsletter']));

            // Validate required fields
            if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password) || empty($userType)) {
                throw new Exception('All required fields must be filled');
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            // Validate phone number
            $phoneDigits = preg_replace('/\D/', '', $phone);
            if (strlen($phoneDigits) !== 10) {
                throw new Exception('Please enter a valid 10-digit phone number');
            }

            // Validate password
            if (strlen($password) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }

            // Validate password confirmation
            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }

            // Validate user type
            $validUserTypes = ['buyer', 'seller', 'agent', 'investor'];
            if (!in_array($userType, $validUserTypes)) {
                throw new Exception('Please select a valid user type');
            }

            // Validate terms agreement
            if (!$agreeTerms) {
                throw new Exception('You must agree to the terms and conditions');
            }

            // Check if email already exists
            $existingUser = $this->db->table('users')->where('email', $email)->first();
            if ($existingUser) {
                throw new Exception('An account with this email already exists');
            }

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));

            // Create user
            $userId = $this->db->table('users')->insert([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'user_type' => $userType,
                'email_verified' => 0,
                'email_verification_token' => $verificationToken,
                'status' => 'active',
                'subscribe_newsletter' => $subscribeNewsletter ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$userId) {
                throw new Exception('Failed to create account. Please try again.');
            }

            // Send welcome email
            try {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Services/EmailService.php';
                $emailService = new \App\Services\EmailService();
                $emailResult = $emailService->sendWelcomeEmail($email, $firstName . ' ' . $lastName);

                if (!$emailResult['success']) {
                    // Log email failure but don't fail registration
                    error_log('Welcome email failed to send: ' . $emailResult['message']);
                }
            } catch (Exception $e) {
                // Log email error but don't fail registration
                error_log('Email service error: ' . $e->getMessage());
            }

            // Send newsletter confirmation if subscribed
            if ($subscribeNewsletter) {
                try {
                    $newsletterResult = $emailService->sendNewsletterConfirmation($email, $firstName . ' ' . $lastName);
                    if (!$newsletterResult['success']) {
                        error_log('Newsletter confirmation failed: ' . $newsletterResult['message']);
                    }
                } catch (Exception $e) {
                    error_log('Newsletter email error: ' . $e->getMessage());
                }
            }

            // Log registration activity
            $this->logUserActivity($userId, 'registration', 'User account created successfully');

            // Send verification email (you can implement this later)
            // $this->sendVerificationEmail($email, $verificationToken);

            // Auto-login the user
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $userType;

            $this->setFlash('success', 'Account created successfully! Welcome to APS Dream Home.');
            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function doLogin()
    {
        $this->processLogin();
    }

    public function doRegister()
    {
        $this->processRegister();
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\Public\AuthController.php

function processRegister()
    {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . 'register');
            return;
        }
function authenticateUser($email, $password)
    {
        try {
            if (!$this->db) {
                return false;
            }
function registerUser($user_data)
    {
        try {
            if (!$this->db) {
                return false;
            }
function emailExists($email)
    {
        try {
            if (!$this->db) {
                return false;
            }
function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
function updateLastLogin($user_id)
    {
        try {
            if (!$this->db) {
                return;
            }
function setRememberMeCookie($user_id)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 3600); // 30 days

        setcookie('remember_me', $token, $expiry, '/', '', true, true);

        // In production, store this token in database for validation
        // For now, we'll just set the cookie
    }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\AuthController.php

function requireGuest()
    {
        // This method is called in constructor, but we handle logged-in users in individual methods
    }
function logUserActivity($userId, $action, $description)
    {
        try {
            $this->db->table('user_activity_log')->insert([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
function sendVerificationEmail($email, $token)
    {
        // TODO: Implement email verification
        // You can integrate with services like SendGrid, Mailgun, etc.
        error_log("Verification email would be sent to $email with token $token");
    }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 570 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//