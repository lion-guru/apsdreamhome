<?php
/**
 * Enhanced Admin Login Handler
 * Provides secure authentication with session management
 */

require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/includes/csrf_protection.php';

// Initialize session with proper security settings
initAdminSession();

class AdminLoginHandler {
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    
    public static function initSession() {
        if (!isset($_SESSION['admin_last_activity'])) {
            $_SESSION['admin_last_activity'] = time();
        }

        // Check session timeout
        if (time() - $_SESSION['admin_last_activity'] > self::SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }

        // Update last activity
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    public static function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: login.php');
            exit();
        }
        
        // Verify CSRF token
        if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Invalid security token. Please refresh and try again.';
            header('Location: login.php');
            exit();
        }
        // Rate limiting: track failed attempts in session
        if (!isset($_SESSION['admin_login_attempts'])) {
            $_SESSION['admin_login_attempts'] = 0;
            $_SESSION['admin_login_blocked_until'] = 0;
        }
        if (time() < ($_SESSION['admin_login_blocked_until'] ?? 0)) {
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']) . '.';
            header('Location: login.php');
            exit();
        }
        // Verify CAPTCHA
        if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_admin'] + $_SESSION['captcha_num2_admin'])) {
            $_SESSION['login_error'] = 'Security error: Invalid CAPTCHA answer.';
            // Reset CAPTCHA for next attempt
            $_SESSION['captcha_num1_admin'] = rand(1, 10);
            $_SESSION['captcha_num2_admin'] = rand(1, 10);
            $_SESSION['admin_login_attempts'] += 1;
            // Lockout after 5 failed attempts for 10 minutes
            if ($_SESSION['admin_login_attempts'] >= 5) {
                $_SESSION['admin_login_blocked_until'] = time() + 600;
                $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']) . '.';
            }
            header('Location: login.php');
            exit();
        }
        // Reset CAPTCHA for next login
        $_SESSION['captcha_num1_admin'] = rand(1, 10);
        $_SESSION['captcha_num2_admin'] = rand(1, 10);
        
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Please fill in all fields';
            header('Location: login.php');
            exit();
        }

        // Use getDbConnection() to connect to DB
        $con = getDbConnection();
        if (!$con) {
            $_SESSION['login_error'] = 'Database connection error';
            header('Location: login.php');
            exit();
        }

        try {
            // Use the unified 'admin' table for both admin and super_admin
            $stmt = $con->prepare("SELECT aid, auser, apass, role, status FROM admin WHERE auser = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            require_once __DIR__ . '/../includes/log_admin_action_db.php';
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if ($user['status'] !== 'active') {
                    $_SESSION['login_error'] = 'Account is not active.';
                    log_admin_action_db('admin_login_failed', 'Inactive account: ' . $username);
                    $con->close();
                    header('Location: login.php');
                    exit();
                }
                if (password_verify($password, $user['apass'])) {
                    session_regenerate_id(true); // Regenerate session ID to prevent fixation
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['aid'];
                    $_SESSION['admin_name'] = $user['auser'];
                    $_SESSION['admin_role'] = $user['role']; // 'admin' or 'super_admin'
                    $_SESSION['admin_last_activity'] = time();
                    $_SESSION['CREATED'] = time();
                    // Set remember me cookie if requested
                    if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                        setcookie('username', $username, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    }
                    // Reset failed attempts on successful login
                    $_SESSION['admin_login_attempts'] = 0;
                    $_SESSION['admin_login_blocked_until'] = 0;
                    log_admin_action_db(($user['role']==='super_admin'?'superadmin_login':'admin_login'), 'Login successful: ' . $username);
                    $con->close();
                    header('Location: index.php');
                    exit();
                }
            }
            $_SESSION['login_error'] = 'Invalid username or password';
            $_SESSION['admin_login_attempts'] += 1;
            // Lockout after 5 failed attempts for 10 minutes
            if ($_SESSION['admin_login_attempts'] >= 5) {
                $_SESSION['admin_login_blocked_until'] = time() + 600;
                $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']) . '.';
            }
            log_admin_action_db('admin_login_failed', 'Failed login for username: ' . $username);
            $con->close();
            header('Location: login.php');
            exit();
            
        } catch (Exception $e) {
            $_SESSION['login_error'] = 'An error occurred. Please try again later.';
            if ($con) $con->close();
            header('Location: login.php');
            exit();
        } finally {
            $stmt->close();
        }
    }
    
    public function checkSession() {
        try {
            // Initialize and validate session
            if (!self::initSession()) {
                return array(
                    'status' => 'error',
                    'message' => 'Session has expired. Please log in again.'
                );
            }

            // Check if user is authenticated
            if (!isset($_SESSION['admin_session']['is_authenticated']) || 
                !$_SESSION['admin_session']['is_authenticated']) {
                return array(
                    'status' => 'error',
                    'message' => 'Unauthorized access. Please log in.'
                );
            }

            // Check session timeout
            if (time() - $_SESSION['admin_session']['last_activity'] > self::SESSION_TIMEOUT) {
                self::terminateSession();
                return array(
                    'status' => 'error',
                    'message' => 'Session has timed out. Please log in again.'
                );
            }

            // Update last activity time
            $_SESSION['admin_session']['last_activity'] = time();

            return array(
                'status' => 'success',
                'message' => 'Session is active.'
            );
        } catch (Exception $e) {
            error_log('Session check error: ' . $e->getMessage());
            return array(
                'status' => 'error',
                'message' => 'Session check failed. Please try again.'
            );
        }
    }
    
    public function createSession($admin_id, $username) {
        // Ensure session is properly configured
        configureSession();
        
        if (!isset($_SESSION['admin_session'])) {
            self::initSession();
        }
        
        $_SESSION['aid'] = $admin_id;
        $_SESSION['auser'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['admin_session']['last_activity'] = time();
        $_SESSION['admin_session']['is_authenticated'] = true;
        
        // Regenerate session ID after successful login
        session_regenerate_id(true);
        
        return array(
            'status' => 'success',
            'message' => 'Logged in successfully.'
        );
    }
    
    public static function terminateSession() {
        // Clear all session data
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        return true;
    }
}

// No auto handler or extra output at the end of file!