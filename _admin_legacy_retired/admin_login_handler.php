<?php
/**
 * Enhanced Security Admin Login Handler
 * Provides secure authentication with comprehensive security measures
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/admin_login_security.log');
error_reporting(E_ALL);

// Include main site config for database connection
require_once __DIR__ . '/../includes/config/config.php';

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminLoginHandler {
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public static function login($username, $password) {
        error_log("Login attempt for username: " . $username);
        try {
            // Validate login attempt
            $validation = self::validateLoginAttempt($username, $password);
            if ($validation !== true) {
                return [
                    'status' => 'error',
                    'message' => is_string($validation) ? $validation : 'Invalid login attempt'
                ];
            }

            // Fetch user from database
            $user = self::getUserByUsername($username);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid username or password'
                ];
            }

            // Verify password
            if (!self::verifyPassword($password, $user['apass'])) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid username or password'
                ];
            }

            // Successful login
            return self::createSession($user);

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }

    private static function validateLoginAttempt($username, $password) {
        if (empty($username) || empty($password)) {
            return 'Username and password are required';
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            return 'Username must be between 3 and 50 characters';
        }

        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long';
        }

        return true;
    }

    private static function getUserByUsername($username) {
        global $con;

        try {
            if (!$con) {
                return null;
            }

            $query = "SELECT auser, apass, role, status, email FROM admin WHERE auser = ? AND status = 'active' LIMIT 1";
            $stmt = $con->prepare($query);
            if (!$stmt) {
                return null;
            }

            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                return null;
            }

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            return $user ?: null;

        } catch (Exception $e) {
            error_log('Database error in getUserByUsername: ' . $e->getMessage());
            return null;
        }
    }

    private static function verifyPassword($password, $hash) {
        if (empty($password) || empty($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    private static function createSession($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['auser'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_last_activity'] = time();

        session_regenerate_id(true);

        return [
            'status' => 'success',
            'message' => 'Logged in successfully',
            'redirect' => self::getDashboardForRole($user['role'])
        ];
    }

    private static function getDashboardForRole($role) {
        $role_dashboard_map = [
            'superadmin' => 'superadmin_dashboard.php',
            'admin' => 'admin_dashboard.php',
            'manager' => 'manager_dashboard.php',
            'director' => 'director_dashboard.php',
            'office_admin' => 'office_admin_dashboard.php',
            'ceo' => 'ceo_dashboard.php',
            'cfo' => 'cfo_dashboard.php',
            'coo' => 'coo_dashboard.php',
            'cto' => 'cto_dashboard.php',
            'cm' => 'cm_dashboard.php',
            'sales' => 'sales_dashboard.php',
            'employee' => 'employee_dashboard.php',
            'legal' => 'legal_dashboard.php',
            'marketing' => 'marketing_dashboard.php',
            'finance' => 'finance_dashboard.php',
            'hr' => 'hr_dashboard.php',
            'it' => 'it_dashboard.php',
            'operations' => 'operations_dashboard.php',
            'support' => 'support_dashboard.php',
            'builder' => 'builder_management_dashboard.php',
            'agent' => 'agent_dashboard.php',
            'associate' => 'associate_dashboard.php'
        ];

        return $role_dashboard_map[$role] ?? 'enhanced_dashboard.php';
    }

    public static function checkSession() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return [
                'status' => 'error',
                'message' => 'Not authenticated',
                'redirect' => 'index.php'
            ];
        }

        if (time() - $_SESSION['admin_last_activity'] > self::SESSION_TIMEOUT) {
            self::terminateSession();
            return [
                'status' => 'error',
                'message' => 'Session expired',
                'redirect' => 'index.php'
            ];
        }

        $_SESSION['admin_last_activity'] = time();

        return [
            'status' => 'success',
            'message' => 'Session valid'
        ];
    }

    public static function terminateSession() {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/admin');
        }
        session_destroy();
    }
}
?>
