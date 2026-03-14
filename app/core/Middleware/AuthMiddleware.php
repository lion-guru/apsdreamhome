<?php

namespace App\Core\Middleware;

/**
 * Advanced Authentication Middleware
 * Provides robust authentication and authorization mechanisms
 */
class AuthMiddleware {
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes
    private const SESSION_TIMEOUT = 1800; // 30 minutes
    private $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'redirect' => '/login',
            'except' => [],
            'only' => [],
            'ajax_only' => false,
            'remember_me' => true,
        ], $options);
    }

    /**
     * Validate admin login credentials
     */
    public static function validateAdminCredentials($username, $password) {
        $username = self::sanitize($username);
        $password = self::sanitize($password);

        if (!$username || !$password) {
            return false;
        }

        if (!self::checkLoginAttempts($username)) {
            return false;
        }

        try {
            $db = \App\Core\App::database();
            $sql = "SELECT * FROM users WHERE name = :username OR email = :email";
            $user = $db->fetch($sql, ['username' => $username, 'email' => $username]);

            if (!$user) {
                $sqlAdmin = "SELECT * FROM admin WHERE auser = :username OR email = :email";
                $adminUser = $db->fetch($sqlAdmin, ['username' => $username, 'email' => $username]);
                if ($adminUser) {
                    $user = $adminUser;
                    $user['password'] = $adminUser['apass'] ?? $adminUser['password'];
                    $user['username'] = $adminUser['auser'] ?? $adminUser['username'];
                    $user['role'] = $adminUser['role'] ?? 'admin';
                }
            }

            if (!$user) return false;

            $password_verified = false;
            if (isset($user['password']) && password_verify($password, $user['password'])) {
                $password_verified = true;
            } else if (isset($user['apass']) && sha1($password) === $user['apass']) {
                $password_verified = true;
            }

            if (!$password_verified) return false;
            
            self::createSecureSession($user['username'] ?? $username);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    private static function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private static function checkLoginAttempts($username) {
        if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
        $currentTime = time();
        foreach ($_SESSION['login_attempts'] as $user => $timestamp) {
            if ($currentTime - $timestamp > self::LOCKOUT_DURATION) unset($_SESSION['login_attempts'][$user]);
        }
        $_SESSION['login_attempts'][$username] = $currentTime;
        return count($_SESSION['login_attempts']) <= self::MAX_LOGIN_ATTEMPTS;
    }

    private static function createSecureSession($username) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    public static function isAdminSessionValid() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) return false;
        $currentTime = time();
        if (($currentTime - $_SESSION['last_activity']) > self::SESSION_TIMEOUT) {
            self::destroySession();
            return false;
        }
        $_SESSION['last_activity'] = $currentTime;
        return true;
    }

    public static function destroySession() {
        $_SESSION = [];
        if (session_id()) session_destroy();
    }

    public static function requireAdminAuth() {
        if (!self::isAdminSessionValid()) {
            header('Location: /admin/login.php');
            exit();
        }
    }

    public function handle(array $request, callable $next) {
        if ($this->shouldBypass($request)) return $next($request);
        if (!isset($_SESSION['user_id'])) return $this->handleUnauthenticated($request);
        return $next($request);
    }

    private function shouldBypass($request) {
        $path = $request['path'] ?? '';
        foreach ($this->options['except'] as $except) {
            if (strpos($path, $except) !== false) return true;
        }
        return false;
    }

    private function handleUnauthenticated($request) {
        if ($this->isAjaxRequest()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
        header('Location: ' . $this->options['redirect']);
        exit;
    }

    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // Static helper methods for different roles
    public static function adminAuth() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            self::redirectOrJson('Admin authentication required');
        }
    }

    public static function employeeAuth() {
        if (!isset($_SESSION['employee_id'])) {
            self::redirectOrJson('Employee authentication required');
        }
    }

    public static function customerAuth() {
        if (!isset($_SESSION['customer_id'])) {
            self::redirectOrJson('Customer authentication required');
        }
    }

    public static function associateAuth() {
        if (!isset($_SESSION['associate_id'])) {
            self::redirectOrJson('Associate authentication required');
        }
    }

    public static function auth() {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in']) &&
            !isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id']) &&
            !isset($_SESSION['associate_id'])) {
            self::redirectOrJson('Authentication required');
        }
    }

    private static function redirectOrJson($message) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode(['error' => $message]);
            exit;
        }
        header('Location: /login');
        exit;
    }
}