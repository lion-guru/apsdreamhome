<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Enhanced Admin Login Handler
 * Provides secure authentication with session management
 */

require_once __DIR__ . '/../includes/db_connection.php';
echo 'DEBUG: db_connection.php included<br>';
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/../includes/password_utils.php';

// Define ValidationException class if not already defined
class ValidationException extends Exception {
    // Custom exception for validation errors
    public function __construct($message, $code = 0, ?Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

// Define logAdminAction function if not already defined elsewhere
if (!function_exists('logAdminAction')) {
    function logAdminAction($data) {
        // Log to error log
        error_log("[Admin Action] " . json_encode($data));
        
        // If you have a database logging function, you can call it here
        if (function_exists('log_admin_action_db')) {
            log_admin_action_db('admin_action', json_encode($data));
        }
    }
}

// Initialize session with proper security settings
initAdminSession();

class AdminLoginHandler {
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    
    public static function login($username, $password) {
        try {
            // Validate login attempt
            if (!self::validateLoginAttempt($username, $password)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid login attempt'
                ];
            }

            // Fetch user from database (replace with actual database logic)
            echo 'DEBUG: In login(), before getUserByUsername<br>';
            $user = self::getUserByUsername($username);
            echo 'DEBUG: In login(), after getUserByUsername<br>';

            // DEBUG: Log username and DB user row
            error_log('[DEBUG] Username received: ' . $username);
            error_log('[DEBUG] DB user row: ' . json_encode($user));
            echo 'DEBUG: Username received: ' . htmlspecialchars($username) . '<br>';
            echo 'DEBUG: DB user row: ' . htmlspecialchars(json_encode($user)) . '<br>';

            // Backward compatible password check
if (!$user) {
    // User not found
    logAdminAction([
        'action' => 'login_failed',
        'username' => $username,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);
    $_SESSION['login_error'] = 'User not found for username: ' . htmlspecialchars($username);
    return [
        'status' => 'error',
        'message' => 'User not found for username: ' . htmlspecialchars($username)
    ];
}

$db_hash = $user['apass'];
$valid = false;
$needs_rehash = false;
echo 'DEBUG: In login(), before password check<br>';

if (strpos($db_hash, '$argon2id$') === 0 || strpos($db_hash, '$2y$') === 0) {
    // Argon2 or bcrypt
    $valid = password_verify($password, $db_hash);
    $needs_rehash = password_needs_rehash($db_hash, PASSWORD_ARGON2ID);
} elseif (preg_match('/^[a-f0-9]{40}$/i', $db_hash)) {
    // SHA1 fallback
    $valid = (sha1($password) === $db_hash);
    $needs_rehash = $valid; // Always rehash if SHA1 matches
}

if (!$valid) {
    // DEBUG: Log password hash being checked
    error_log('[DEBUG] Password hash in DB: ' . $db_hash);
    error_log('[DEBUG] Password entered: ' . $password);
    echo 'DEBUG: In login(), password check failed<br>';
    logAdminAction([
        'action' => 'login_failed',
        'username' => $username,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);
    $_SESSION['login_error'] = 'Password mismatch. Entered: ' . htmlspecialchars($password) . ' | Hash in DB: ' . htmlspecialchars($db_hash);
    return [
        'status' => 'error',
        'message' => 'Password mismatch. Entered: ' . htmlspecialchars($password) . ' | Hash in DB: ' . htmlspecialchars($db_hash)
    ];
}

// If password needs rehash (e.g., was SHA1 or old bcrypt), upgrade to Argon2
if ($needs_rehash) {
    $new_hash = password_hash($password, PASSWORD_ARGON2ID);
    // Update password in DB
    $conn = getDbConnection();
    $stmt = $conn->prepare('UPDATE admin SET apass = ? WHERE id = ?');
    $stmt->bind_param('si', $new_hash, $user['id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    $user['apass'] = $new_hash;
}

            echo 'DEBUG: In login(), password check success<br>';
            // Successful login
            return self::createSession($user);

        } catch (Exception $e) {
            // Log unexpected errors
            self::logError('login_error', $e->getMessage());
            $_SESSION['login_error'] = 'Exception: ' . htmlspecialchars($e->getMessage()) . ' | File: ' . htmlspecialchars($e->getFile()) . ' | Line: ' . htmlspecialchars($e->getLine());
            return [
                'status' => 'error',
                'message' => 'Exception: ' . htmlspecialchars($e->getMessage()) . ' | File: ' . htmlspecialchars($e->getFile()) . ' | Line: ' . htmlspecialchars($e->getLine())
            ];
        }
    }

    private static function validateLoginAttempt($username, $password) {
        // Basic validation
        return !empty($username) && !empty($password);
    }

    private static function getUserByUsername($username) {
        echo 'DEBUG: getUserByUsername() called<br>';
        $conn = getDbConnection();
        if (!$conn) {
            echo 'DEBUG: DB connection failed<br>';
            error_log('DEBUG: DB connection failed in getUserByUsername');
            return null;
        }
        echo 'DEBUG: DB connection successful<br>';
        $stmt = $conn->prepare("SELECT * FROM admin WHERE auser = ? LIMIT 1");
        if (!$stmt) {
            echo 'DEBUG: Prepare failed: ' . $conn->error . '<br>';
            error_log('DEBUG: Prepare failed in getUserByUsername: ' . $conn->error);
            return null;
        }
        echo 'DEBUG: Statement prepared<br>';
        $stmt->bind_param("s", $username);
        $execResult = $stmt->execute();
        if (!$execResult) {
            echo 'DEBUG: Execute failed: ' . $stmt->error . '<br>';
            error_log('DEBUG: Execute failed in getUserByUsername: ' . $stmt->error);
            return null;
        }
        echo 'DEBUG: Statement executed<br>';
        $result = $stmt->get_result();
        if (!$result) {
            echo 'DEBUG: get_result() failed: ' . $stmt->error . '<br>';
            error_log('DEBUG: get_result() failed in getUserByUsername: ' . $stmt->error);
            return null;
        }
        echo 'DEBUG: Result fetched<br>';
        $user = $result->fetch_assoc();
        if ($user) {
            echo 'DEBUG: User found<br>';
        } else {
            echo 'DEBUG: User not found<br>';
        }
        $stmt->close();
        $conn->close();
        return $user;
    }

    private static function createSession($user) {
        // Set up session data
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['auser'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_last_activity'] = time();
        // Set for index.php compatibility
        $_SESSION['admin_session']['is_authenticated'] = true;
        $_SESSION['admin_session']['username'] = $user['auser'];

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Log successful login
        logAdminAction([
            'action' => 'login_success',
            'username' => $user['auser'],
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        return [
            'status' => 'success',
            'message' => 'Logged in successfully',
            'redirect' => self::getDashboardForRole($user['role'])
        ];
    }

    private static function getDashboardForRole($role) {
        // Return appropriate dashboard URL based on role
        switch ($role) {
            case 'superadmin': return 'superadmin_dashboard.php';
            case 'manager': return 'manager_dashboard.php';
            default: return 'dashboard.php';
        }
    }

    public static function checkSession() {
        // Check if user is logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return [
                'status' => 'error',
                'message' => 'Not authenticated',
                'redirect' => 'login.php'
            ];
        }

        // Check session timeout
        if (time() - $_SESSION['admin_last_activity'] > self::SESSION_TIMEOUT) {
            self::terminateSession();
            return [
                'status' => 'error',
                'message' => 'Session expired',
                'redirect' => 'login.php'
            ];
        }

        // Update last activity time
        $_SESSION['admin_last_activity'] = time();

        return [
            'status' => 'success',
            'message' => 'Session valid'
        ];
    }

    public static function terminateSession() {
        // Clear all session data
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();
    }

    private static function logError($type, $message) {
        error_log("Admin Login Error [$type]: $message");
    }

    public static function initSession() {
        if (!isset($_SESSION['admin_last_activity'])) {
            $_SESSION['admin_last_activity'] = time();
        }
        if (time() - $_SESSION['admin_last_activity'] > self::SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['admin_last_activity'] = time();
        return true;
    }

    public static function handleLogin() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php');
                exit();
            }
            
            // CAPTCHA validation
            $captcha_answer = filter_input(INPUT_POST, 'captcha_answer', FILTER_VALIDATE_INT);
            if (!isset($_SESSION['captcha_num1_admin']) || !isset($_SESSION['captcha_num2_admin']) || 
                $captcha_answer !== ($_SESSION['captcha_num1_admin'] + $_SESSION['captcha_num2_admin'])) {
                $_SESSION['login_error'] = 'Invalid security answer';
                header('Location: index.php');
                exit();
            }
            
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '';
            
            if (empty($username) || empty($password)) {
                $_SESSION['login_error'] = 'Please fill in all fields';
                header('Location: index.php');
                exit();
            }
            
            // Check if account is locked
            if (isset($_SESSION['admin_login_blocked_until']) && $_SESSION['admin_login_blocked_until'] > time()) {
                $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']);
                header('Location: index.php');
                exit();
            }
            
            // Get database connection
            require_once __DIR__ . '/../includes/db_connection.php';
            $conn = getDbConnection();
            if (!$conn) {
                $_SESSION['login_error'] = 'Database connection error';
                header('Location: index.php');
                exit();
            }

            $user = null;
            // Remove the inner try-catch, and handle errors in the outer catch
            // Use if/else and error handling logic instead
            $stmt = $conn->prepare("SELECT id, auser, apass, role, status FROM admin WHERE auser = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                self::handleUserNotFound($username);
            }
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // Check user status
            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = 'Account is not active';
                header('Location: index.php');
                exit();
            }
            
            // Successful login - set session
            $_SESSION['admin_session'] = [
                'is_authenticated' => true,
                'user_id' => $user['id'],
                'username' => $user['auser'],
                'role' => $user['role'],
                'last_activity' => time(),
                'created' => time()
            ];
            
            // Verify password
            if (password_verify($password, $user['apass'])) {
                // Check if password needs rehashing
                if (password_needs_rehash($user['apass'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update the password in the database
                    $update_stmt = $conn->prepare("UPDATE admin SET apass = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_hash, $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Successful login
                session_regenerate_id(true); // Prevent session fixation

                // Set comprehensive session data
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['auser'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                $_SESSION['created'] = time();

                // Log successful login
                logAdminAction([
                    'action' => 'login_success',
                    'username' => $user['auser'],
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);

                // Redirect based on user role
                // Automatically redirect to the correct dashboard if a matching *_dashboard.php exists
                $role_dashboard_map = [
                    'superadmin' => 'superadmin_dashboard.php',
                    'admin' => 'dashboard.php',
                    'manager' => 'manager_dashboard.php',
                    'director' => 'director_dashboard.php',
                    'office_admin' => 'office_admin_dashboard.php',
                    'sales' => 'sales_dashboard.php',
                    'employee' => 'employee_dashboard.php',
                    'legal' => 'legal_dashboard.php',
                    'marketing' => 'marketing_dashboard.php',
                    'finance' => 'finance_dashboard.php',
                    'hr' => 'hr_dashboard.php',
                    'it' => 'it_dashboard.php',
                    'operations' => 'operations_dashboard.php',
                    'support' => 'support_dashboard.php',
                    // Add more roles and dashboards as needed
                ];
                $role = $user['role'];
                if (isset($role_dashboard_map[$role]) && file_exists(__DIR__ . '/' . $role_dashboard_map[$role])) {
                    header('Location: ' . $role_dashboard_map[$role]);
                } else if (file_exists(__DIR__ . '/' . $role . '_dashboard.php')) {
                    header('Location: ' . $role . '_dashboard.php');
                } else {
                    header('Location: login.php?error=unauthorized');
                    $_SESSION['login_error'] = 'Unauthorized access';
                }
                exit();
                // This logic ensures that any official user with a matching dashboard can log in. Add new roles in the map above as needed.
            } else {
                // Failed password verification
                $_SESSION['login_error'] = 'Invalid username or password';
                $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;
                            
                // Implement progressive lockout
                if ($_SESSION['admin_login_attempts'] >= 5) {
                    $_SESSION['admin_login_blocked_until'] = time() + 600; // 10 minutes
                }

                // Log failed login attempt
                logAdminAction([
                    'action' => 'login_failed',
                    'username' => $username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);

                header('Location: login.php');
                exit();
            }
        } catch (Exception $e) {
            // Log the error
            error_log('Admin login handler error: ' . $e->getMessage());
            $_SESSION['login_error'] = 'An error occurred. Please try again.';
            header('Location: index.php');
            exit();
        }
    }
    
    // Handle user not found case
    private static function handleUserNotFound($username) {
        $_SESSION['login_error'] = 'Invalid username or password';
        $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;
        
        if ($_SESSION['admin_login_attempts'] >= 5) {
            $_SESSION['admin_login_blocked_until'] = time() + 600;
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['admin_login_blocked_until']);
        }
        
        if (!isset($_SESSION['admin_session'])) {
            self::initSession();
        }
        
        // Log failed login attempt
        logAdminAction([
            'action' => 'login_failed',
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        
        header('Location: login.php');
        exit();
    }
    
    private static function updateSessionActivity() {
        $_SESSION['admin_session']['last_activity'] = time();
        $_SESSION['admin_session']['is_authenticated'] = true;
        
        // Regenerate session ID after successful login
        session_regenerate_id(true);
        
        return array(
            'status' => 'success',
            'message' => 'Logged in successfully.'
        );
    }
}
