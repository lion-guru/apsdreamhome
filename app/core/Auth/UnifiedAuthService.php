<?php
/**
 * Unified Authentication Service
 * Consolidates session management for both MVC and Legacy systems
 */

namespace App\Core\Auth;

use App\Core\Session\SessionManager;

class UnifiedAuthService {
    private static $instance = null;
    private $session;
    private $config;
    
    // Session keys for different user types
    const ADMIN_SESSION_KEY = 'admin_logged_in';
    const USER_SESSION_KEY = 'user_logged_in';
    const USER_ID_KEY = 'user_id';
    const ADMIN_ID_KEY = 'admin_id';
    const USER_ROLE_KEY = 'user_role';
    const ADMIN_ROLE_KEY = 'admin_role';
    
    private function __construct() {
        $this->session = new SessionManager();
        $this->config = $this->loadConfig();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load authentication configuration
     */
    private function loadConfig() {
        return [
            'session_timeout' => $_ENV['SESSION_TIMEOUT'] ?? 1800, // 30 minutes
            'max_login_attempts' => $_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5,
            'lockout_duration' => $_ENV['LOCKOUT_DURATION'] ?? 900, // 15 minutes
            'password_min_length' => $_ENV['PASSWORD_MIN_LENGTH'] ?? 8,
            'require_strong_password' => filter_var($_ENV['REQUIRE_STRONG_PASSWORD'] ?? true, FILTER_VALIDATE_BOOLEAN)
        ];
    }
    
    /**
     * Initialize secure session
     */
    public function initializeSession() {
        if ($this->session->has('initialized')) {
            return;
        }
        
        // Set secure session parameters
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        session_set_cookie_params([
            'lifetime' => $this->config['session_timeout'],
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Start session
        $this->session->start();
        
        // Regenerate ID for security
        $this->session->regenerate(true);
        
        // Mark as initialized
        $this->session->set('initialized', true);
        $this->session->set('last_activity', time());
        
        // Check for session timeout
        $this->checkSessionTimeout();
    }
    
    /**
     * Check if admin is logged in
     */
    public function isAdminLoggedIn() {
        $this->initializeSession();
        return $this->session->has(self::ADMIN_SESSION_KEY) && 
               $this->session->get(self::ADMIN_SESSION_KEY) === true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isUserLoggedIn() {
        $this->initializeSession();
        return $this->session->has(self::USER_SESSION_KEY) && 
               $this->session->get(self::USER_SESSION_KEY) === true;
    }
    
    /**
     * Login admin user
     */
    public function loginAdmin($adminId, $role, $username) {
        $this->initializeSession();
        
        $this->session->set(self::ADMIN_SESSION_KEY, true);
        $this->session->set(self::ADMIN_ID_KEY, $adminId);
        $this->session->set(self::ADMIN_ROLE_KEY, $role);
        $this->session->set('admin_username', $username);
        $this->session->set('login_time', time());
        $this->session->set('last_activity', time());
        
        // Regenerate session ID after login
        $this->session->regenerate(true);
        
        return true;
    }
    
    /**
     * Login regular user
     */
    public function loginUser($userId, $role, $username) {
        $this->initializeSession();
        
        $this->session->set(self::USER_SESSION_KEY, true);
        $this->session->set(self::USER_ID_KEY, $userId);
        $this->session->set(self::USER_ROLE_KEY, $role);
        $this->session->set('username', $username);
        $this->session->set('login_time', time());
        $this->session->set('last_activity', time());
        
        // Regenerate session ID after login
        $this->session->regenerate(true);
        
        return true;
    }
    
    /**
     * Logout current user (admin or regular)
     */
    public function logout() {
        if ($this->session->has('initialized')) {
            // Clear all auth-related session data
            $keys = [
                self::ADMIN_SESSION_KEY,
                self::USER_SESSION_KEY,
                self::ADMIN_ID_KEY,
                self::USER_ID_KEY,
                self::ADMIN_ROLE_KEY,
                self::USER_ROLE_KEY,
                'admin_username',
                'username',
                'login_time',
                'last_activity',
                'initialized'
            ];
            
            foreach ($keys as $key) {
                $this->session->remove($key);
            }
            
            // Destroy session
            $this->session->destroy();
        }
    }
    
    /**
     * Get current admin data
     */
    public function getCurrentAdmin() {
        if (!$this->isAdminLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->session->get(self::ADMIN_ID_KEY),
            'role' => $this->session->get(self::ADMIN_ROLE_KEY),
            'username' => $this->session->get('admin_username'),
            'login_time' => $this->session->get('login_time'),
            'last_activity' => $this->session->get('last_activity')
        ];
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isUserLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->session->get(self::USER_ID_KEY),
            'role' => $this->session->get(self::USER_ROLE_KEY),
            'username' => $this->session->get('username'),
            'login_time' => $this->session->get('login_time'),
            'last_activity' => $this->session->get('last_activity')
        ];
    }
    
    /**
     * Check session timeout
     */
    public function checkSessionTimeout() {
        if (!$this->session->has('last_activity')) {
            return false;
        }
        
        $lastActivity = $this->session->get('last_activity');
        $timeout = $this->config['session_timeout'];
        
        if (time() - $lastActivity > $timeout) {
            $this->logout();
            return false;
        }
        
        // Update last activity
        $this->session->set('last_activity', time());
        return true;
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password) {
        if (!$this->config['require_strong_password']) {
            return strlen($password) >= $this->config['password_min_length'];
        }
        
        // Strong password requirements
        $length = strlen($password) >= $this->config['password_min_length'];
        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasLower = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
        
        return $length && $hasUpper && $hasLower && $hasNumber && $hasSpecial;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        $this->initializeSession();
        
        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32)));
        }
        
        return $this->session->get('csrf_token');
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token) {
        $this->initializeSession();
        
        if (!$this->session->has('csrf_token')) {
            return false;
        }
        
        return hash_equals($this->session->get('csrf_token'), $token);
    }
    
    /**
     * Check login attempts (for rate limiting)
     */
    public function checkLoginAttempts($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        
        if (!$this->session->has($key)) {
            return true;
        }
        
        $attempts = $this->session->get($key);
        
        if ($attempts['count'] >= $this->config['max_login_attempts']) {
            $lockoutTime = $attempts['last_attempt'] + $this->config['lockout_duration'];
            
            if (time() < $lockoutTime) {
                return false; // Still locked out
            }
            
            // Lockout expired, reset attempts
            $this->session->remove($key);
            return true;
        }
        
        return true;
    }
    
    /**
     * Record failed login attempt
     */
    public function recordFailedAttempt($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        
        if (!$this->session->has($key)) {
            $this->session->set($key, [
                'count' => 1,
                'last_attempt' => time()
            ]);
        } else {
            $attempts = $this->session->get($key);
            $this->session->set($key, [
                'count' => $attempts['count'] + 1,
                'last_attempt' => time()
            ]);
        }
    }
    
    /**
     * Clear failed login attempts
     */
    public function clearFailedAttempts($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        $this->session->remove($key);
    }
    
    /**
     * Get session timeout remaining time
     */
    public function getSessionTimeoutRemaining() {
        if (!$this->session->has('last_activity')) {
            return 0;
        }
        
        $lastActivity = $this->session->get('last_activity');
        $timeout = $this->config['session_timeout'];
        $elapsed = time() - $lastActivity;
        
        return max(0, $timeout - $elapsed);
    }
}