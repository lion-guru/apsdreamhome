<?php
// Advanced Authentication and Authorization Management

class AuthManager {
    // User Roles
    const ROLE_GUEST    = 0;
    const ROLE_CUSTOMER = 1;
    const ROLE_AGENT    = 2;
    const ROLE_ADMIN    = 3;

    // Session Configuration
    private $session_config = [
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    // Encryption and Security
    private $encryption_key;
    private $hash_algo = PASSWORD_ARGON2ID;
    private $hash_options = [
        'memory_cost' => 1024 * 64,
        'time_cost' => 4,
        'threads' => 3
    ];

    // Dependencies
    private $db;
    private $logger;

    public function __construct($db, $logger) {
        $this->db = $db;
        $this->logger = $logger;

        // Initialize session
        $this->initializeSession();

        // Set encryption key (should be stored securely, e.g., in environment variables)
        $this->encryption_key = hash('sha256', getenv('APP_SECRET_KEY') ?: 'fallback_secret_key');
    }

    /**
     * Initialize secure session
     */
    private function initializeSession() {
        // Secure session configuration
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');

        // Start session with secure settings
        session_start([
            'cookie_lifetime' => $this->session_config['lifetime'],
            'cookie_path' => $this->session_config['path'],
            'cookie_domain' => $this->session_config['domain'],
            'cookie_secure' => $this->session_config['secure'],
            'cookie_httponly' => $this->session_config['httponly'],
        ]);

        // Regenerate session ID periodically to prevent session fixation
        $this->regenerateSessionId();
    }

    /**
     * Regenerate session ID
     */
    private function regenerateSessionId() {
        // Regenerate session ID every 30 minutes
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * User registration
     * @param array $user_data User registration details
     * @return bool|int User ID or false
     */
    public function register($user_data) {
        try {
            // Validate input
            $this->validateRegistrationData($user_data);

            // Hash password
            $hashed_password = $this->hashPassword($user_data['password']);

            // Prepare SQL statement
            $stmt = $this->db->prepare("
                INSERT INTO users 
                (first_name, last_name, email, password, phone, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            // Default role and status
            $role = $user_data['role'] ?? self::ROLE_CUSTOMER;
            $status = 'active';

            $stmt->bind_param(
                'sssssss', 
                $user_data['first_name'], 
                $user_data['last_name'], 
                $user_data['email'], 
                $hashed_password, 
                $user_data['phone'], 
                $role, 
                $status
            );

            // Execute and get user ID
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Log registration
                $this->logger->log(
                    "User registered: {$user_data['email']} (ID: {$user_id})", 
                    'info', 
                    'security'
                );

                return $user_id;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->log(
                "Registration error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return false;
        }
    }

    /**
     * Validate registration data
     * @param array $user_data User registration details
     * @throws Exception If validation fails
     */
    private function validateRegistrationData($user_data) {
        // Email validation
        if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Check email uniqueness
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $user_data['email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Password strength validation
        if (strlen($user_data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        // Additional validations can be added here
    }

    /**
     * User login
     * @param string $email User email
     * @param string $password User password
     * @return bool|array User data or false
     */
    public function login($email, $password) {
        try {
            // Prepare SQL statement
            $stmt = $this->db->prepare("
                SELECT id, email, password, role, status 
                FROM users 
                WHERE email = ?
            ");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user exists
            if ($result->num_rows === 0) {
                throw new Exception("User not found");
            }

            $user = $result->fetch_assoc();

            // Verify password
            if (!$this->verifyPassword($password, $user['password'])) {
                throw new Exception("Invalid credentials");
            }

            // Check user status
            if ($user['status'] !== 'active') {
                throw new Exception("Account is not active");
            }

            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Log successful login
            $this->logger->log(
                "User logged in: {$email} (ID: {$user['id']})", 
                'info', 
                'security'
            );

            return $user;
        } catch (Exception $e) {
            // Log login failure
            $this->logger->log(
                "Login attempt failed: {$email} - " . $e->getMessage(), 
                'warning', 
                'security'
            );
            return false;
        }
    }

    /**
     * Hash password
     * @param string $password Plain text password
     * @return string Hashed password
     */
    private function hashPassword($password) {
        return password_hash($password, $this->hash_algo, $this->hash_options);
    }

    /**
     * Verify password
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool
     */
    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Logout user
     */
    public function logout() {
        // Log logout
        $this->logger->log(
            "User logged out: " . ($_SESSION['email'] ?? 'Unknown'), 
            'info', 
            'security'
        );

        // Destroy session
        session_unset();
        session_destroy();

        // Regenerate session ID
        session_regenerate_id(true);
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     * @return int
     */
    public function getCurrentUserRole() {
        return $_SESSION['role'] ?? self::ROLE_GUEST;
    }

    /**
     * Check user authorization
     * @param int $required_role Minimum role required
     * @return bool
     */
    public function checkAuthorization($required_role) {
        $current_role = $this->getCurrentUserRole();
        return $current_role >= $required_role;
    }

    /**
     * Generate password reset token
     * @param string $email User email
     * @return string|bool Reset token or false
     */
    public function generatePasswordResetToken($email) {
        try {
            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expiry = time() + 3600; // 1 hour expiry

            // Store token in database
            $stmt = $this->db->prepare("
                INSERT INTO password_reset_tokens 
                (email, token, expiry) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE token = ?, expiry = ?
            ");
            $stmt->bind_param('ssiss', $email, $token, $expiry, $token, $expiry);
            
            if ($stmt->execute()) {
                return $token;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->log(
                "Password reset token generation failed: {$email}", 
                'error', 
                'security'
            );
            return false;
        }
    }

    /**
     * Validate password reset token
     * @param string $email User email
     * @param string $token Reset token
     * @return bool
     */
    public function validatePasswordResetToken($email, $token) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM password_reset_tokens 
                WHERE email = ? AND token = ? AND expiry > ?
            ");
            $current_time = time();
            $stmt->bind_param('ssi', $email, $token, $current_time);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->num_rows > 0;
        } catch (Exception $e) {
            $this->logger->log(
                "Password reset token validation failed", 
                'error', 
                'security'
            );
            return false;
        }
    }
}

// Helper function for dependency injection
function getAuthManager() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $db = $container->resolve('db_connection');
    $logger = $container->resolve('logger');
    
    return new AuthManager($db, $logger);
}

return getAuthManager();
