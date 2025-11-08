<?php
/**
 * Authentication Controller
 * Handles user login, registration, logout and session management
 */

namespace App\Controllers;

class AuthController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display login page
     */
    public function login() {
        // If already logged in, redirect to appropriate dashboard
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] === 'admin') {
                $this->redirect(BASE_URL . 'admin');
            } else {
                $this->redirect(BASE_URL . 'dashboard');
            }
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Login - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Login', 'url' => BASE_URL . 'login']
        ];

        // Check for login error messages
        $this->data['error'] = $_GET['error'] ?? '';
        $this->data['success'] = $_GET['success'] ?? '';

        // Render the login page
        $this->render('auth/login');
    }

    /**
     * Process login form submission
     */
    public function processLogin() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate input
        if (empty($email) || empty($password)) {
            $this->redirect(BASE_URL . 'login?error=' . urlencode('Please fill in all fields'));
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect(BASE_URL . 'login?error=' . urlencode('Please enter a valid email address'));
            return;
        }

        // Attempt to login
        $user = $this->authenticateUser($email, $password);

        if ($user) {
            // Login successful - set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            $_SESSION['user_status'] = $user['status'];
            $_SESSION['login_time'] = time();

            // Update last login
            $this->updateLastLogin($user['id']);

            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                $this->redirect(BASE_URL . 'admin');
            } else {
                $this->redirect(BASE_URL . 'dashboard');
            }
        } else {
            // Login failed
            $this->redirect(BASE_URL . 'login?error=' . urlencode('Invalid email or password'));
        }
    }

    /**
     * Display registration page
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Register - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Register', 'url' => BASE_URL . 'register']
        ];

        // Check for registration messages
        $this->data['error'] = $_GET['error'] ?? '';
        $this->data['success'] = $_GET['success'] ?? '';

        // Render the registration page
        $this->render('auth/register');
    }

    /**
     * Process registration form submission
     */
    public function processRegister() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . 'register');
            return;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'customer';

        // Validate input
        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
            $this->redirect(BASE_URL . 'register?error=' . urlencode('Please fill in all fields'));
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect(BASE_URL . 'register?error=' . urlencode('Please enter a valid email address'));
            return;
        }

        // Validate password strength
        if (strlen($password) < 6) {
            $this->redirect(BASE_URL . 'register?error=' . urlencode('Password must be at least 6 characters long'));
            return;
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $this->redirect(BASE_URL . 'register?error=' . urlencode('Passwords do not match'));
            return;
        }

        // Check if email already exists
        if ($this->emailExists($email)) {
            $this->redirect(BASE_URL . 'register?error=' . urlencode('An account with this email already exists'));
            return;
        }

        // Register the user
        $user_id = $this->registerUser([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => $role
        ]);

        if ($user_id) {
            // Send registration notification email
            $this->sendRegistrationNotifications([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role
            ]);

            // Registration successful
            $this->redirect(BASE_URL . 'login?success=' . urlencode('Registration successful! Please login with your credentials.'));
        } else {
            // Registration failed
            $this->redirect(BASE_URL . 'register?error=' . urlencode('Registration failed. Please try again.'));
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        // Clear session data
        session_unset();
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        // Redirect to home page
        $this->redirect(BASE_URL);
    }

    /**
     * Authenticate user credentials
     */
    private function authenticateUser($email, $password) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare("
                SELECT id, name, email, phone, role, status, password
                FROM users
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $this->verifyPassword($password, $user['password'])) {
                // Remove password from user data before returning
                unset($user['password']);
                return $user;
            }

            return false;

        } catch (Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a new user
     */
    private function registerUser($user_data) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            // Hash the password
            $hashed_password = $this->hashPassword($user_data['password']);

            // Prepare user data
            $user = [
                'name' => $user_data['name'],
                'email' => $user_data['email'],
                'phone' => $user_data['phone'],
                'password' => $hashed_password,
                'role' => $user_data['role'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute(array_values($user))) {
                return $pdo->lastInsertId();
            }

            return false;

        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email already exists
     */
    private function emailExists($email) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0) > 0;

        } catch (Exception $e) {
            error_log('Email check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hash password using Argon2ID (PHP 7.2+)
     */
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password against hash
     */
    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Update user's last login time
     */
    private function updateLastLogin($user_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return;
            }

            $stmt = $pdo->prepare("
                UPDATE users
                SET last_login = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);

        } catch (Exception $e) {
            error_log('Last login update error: ' . $e->getMessage());
        }
    }

    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($user_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 3600); // 30 days

        setcookie('remember_me', $token, $expiry, '/', '', true, true);

        // In production, store this token in database for validation
        // For now, we'll just set the cookie
    }

}

?>
