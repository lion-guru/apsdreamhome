<?php

namespace App\Services\Legacy;

/**
 * Unified Authentication and Authorization System
 *
 * Handles both session-based and JWT-based authentication.
 * Consolidates functionality from legacy Auth and ApiAuth classes.
 */

class Auth
{
    private static $instance = null;
    private $config;
    private $secretKey;
    private $algorithm = 'HS256';
    private $tokenExpiry = 86400; // 24 hours

    private function __construct()
    {
        // Load configuration
        if (class_exists('AppConfig')) {
            $this->config = AppConfig::getInstance();
            $this->secretKey = $this->config->get('auth.jwt_secret', 'default_jwt_secret');
            $this->tokenExpiry = (int)$this->config->get('auth.jwt_ttl', 86400);
        } else {
            // Fallback for standalone usage
            $this->secretKey = getenv('JWT_SECRET') ?: 'default_jwt_secret';
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Authenticate user with email and password
     *
     * @param string $email
     * @param string $password
     * @return string|false JWT token on success, false on failure
     */
    public function login($email, $password)
    {
        $user = $this->getUserByEmail($email);

        if ($user && $user['status'] === 'active' && password_verify($password, $user['upass'])) {
            return $this->generateToken($user);
        }

        return false;
    }

    /**
     * Validate a JWT token
     *
     * @param string $token
     * @return array|false Decoded payload on success, false on failure
     */
    public function validateToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }

            $header = json_decode($this->base64UrlDecode($parts[0]), true);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            $signature = $parts[2];

            if (!$header || !$payload || $header['alg'] !== $this->algorithm) {
                return false;
            }

            $signatureCheck = hash_hmac('sha256', "$parts[0].$parts[1]", $this->secretKey, true);
            $base64UrlSignature = $this->base64UrlEncode($signatureCheck);

            if (!hash_equals($base64UrlSignature, $signature)) {
                return false;
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            return $payload;
        } catch (Exception $e) {
            error_log('Token validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a JWT token for a user
     *
     * @param array $user
     * @return string JWT token
     */
    public function generateToken($user)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'sub' => $user['uid'] ?? $user['id'],
            'email' => $user['uemail'] ?? $user['email'],
            'role' => $user['utype'] ?? $user['role'] ?? 'user',
            'iat' => time(),
            'exp' => time() + $this->tokenExpiry
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Alias for generateToken (ApiAuth compatibility)
     */
    public function generateJWT($user)
    {
        return $this->generateToken($user);
    }

    /**
     * Check if a user is authenticated (via session or token)
     */
    public function check()
    {
        return $this->getCurrentUser() !== false;
    }

    /**
     * Get the currently authenticated user (from session, Bearer token, or API Key)
     *
     * @return array|false User payload or false
     */
    public function getCurrentUser()
    {
        // 1. Check Bearer token (JWT)
        $headers = $this->getAuthorizationHeader();
        if ($headers && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
            $payload = $this->validateToken($token);
            if ($payload) {
                return $payload;
            }
        }

        // 2. Check API Key header
        $apiKey = $this->getApiKeyFromRequest();
        if ($apiKey) {
            $user = $this->validateApiKey($apiKey);
            if ($user) {
                return [
                    'sub' => $user['uid'],
                    'id' => $user['uid'],
                    'email' => $user['uemail'],
                    'role' => $user['utype'],
                    'type' => 'api_key'
                ];
            }
        }

        // 3. Check Session (Web)
        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/session_helpers.php';
            ensureSessionStarted();
        }

        // Check for unified session first
        if (isset($_SESSION['auth']) && isset($_SESSION['auth']['authenticated']) && $_SESSION['auth']['authenticated'] === true) {
            return [
                'sub' => $_SESSION['auth']['user_id'],
                'id' => $_SESSION['auth']['user_id'],
                'role' => $_SESSION['auth']['role'] ?? 'user',
                'email' => $_SESSION['auth']['email'] ?? null,
                'type' => 'session'
            ];
        }

        // Fallback to legacy session keys
        if (isset($_SESSION['user_id'])) {
            return [
                'sub' => $_SESSION['user_id'],
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['user_role'] ?? 'user',
                'email' => $_SESSION['user_email'] ?? null,
                'type' => 'session'
            ];
        }

        // Check for admin session
        if (isset($_SESSION['admin_session']) && isset($_SESSION['admin_session']['is_authenticated']) && $_SESSION['admin_session']['is_authenticated'] === true) {
            return [
                'sub' => $_SESSION['admin_session']['user_id'],
                'id' => $_SESSION['admin_session']['user_id'],
                'role' => 'admin',
                'email' => $_SESSION['admin_email'] ?? null,
                'type' => 'session'
            ];
        }

        // Check for employee session
        if (isset($_SESSION['employee_id'])) {
            return [
                'sub' => $_SESSION['employee_id'],
                'id' => $_SESSION['employee_id'],
                'role' => 'employee',
                'email' => $_SESSION['employee_email'] ?? null,
                'type' => 'session'
            ];
        }

        // Check for associate session
        if (isset($_SESSION['associate_id'])) {
            return [
                'sub' => $_SESSION['associate_id'],
                'id' => $_SESSION['associate_id'],
                'role' => 'associate',
                'email' => $_SESSION['associate_email'] ?? null,
                'type' => 'session'
            ];
        }

        return false;
    }

    /**
     * Get API Key from request headers
     */
    private function getApiKeyFromRequest()
    {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        if (isset($headers['x-api-key'])) {
            return $headers['x-api-key'];
        }

        if (isset($headers['apikey'])) {
            return $headers['apikey'];
        }

        return $_GET['api_key'] ?? null;
    }

    /**
     * Validate an API key
     */
    public function validateApiKey($apiKey)
    {
        $db = $this->getDb();
        if (!$db) return false;

        $hashedApiKey = hash('sha256', $apiKey);

        try {
            $user = $db->query("
                SELECT u.*
                FROM api_keys ak
                JOIN user u ON ak.user_id = u.uid
                WHERE ak.api_key = :api_key
                AND ak.is_active = 1
                AND (ak.expires_at IS NULL OR ak.expires_at > NOW())
                AND u.status = 'active'
            ", ['api_key' => $hashedApiKey])->fetch();

            if ($user) {
                // Update last used
                $db->execute("UPDATE api_keys SET last_used_at = NOW(), usage_count = usage_count + 1 WHERE api_key = :api_key", ['api_key' => $hashedApiKey]);
                return $user;
            }
        } catch (\Exception $e) {
            error_log('API Key validation error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Update API key usage stats
     */
    private function updateApiKeyUsage($hashedApiKey)
    {
        $db = $this->getDb();
        if (!$db) return;

        try {
            $db->execute("UPDATE api_keys SET last_used_at = NOW(), usage_count = usage_count + 1 WHERE api_key = :api_key", ['api_key' => $hashedApiKey]);
        } catch (\Exception $e) {
            error_log('API Key usage update error: ' . $e->getMessage());
        }
    }

    /**
     * Get database connection
     */
    private function getDb()
    {
        return \App\Core\App::database();
    }

    /**
     * Log a user in (sets session)
     */
    public function loginWithSession($user)
    {
        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/session_helpers.php';
            ensureSessionStarted();
        }

        $userId = $user['uid'] ?? $user['id'];
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $user['utype'] ?? $user['role'] ?? 'user';
        $_SESSION['user_email'] = $user['uemail'] ?? $user['email'] ?? null;

        return true;
    }

    /**
     * Log a user out (clears session)
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            require_once __DIR__ . '/session_helpers.php';
            ensureSessionStarted();
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        return true;
    }

    /**
     * Alias for getCurrentUser (ApiAuth compatibility)
     */
    public function authenticate()
    {
        return $this->getCurrentUser();
    }

    /**
     * Enforce authentication and optionally a specific role
     *
     * @param string|null $requiredRole
     * @return array User payload
     */
    public function requireAuth($requiredRole = null)
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->sendErrorResponse('Unauthorized', 401);
        }

        if ($requiredRole) {
            $userRole = $user['role'] ?? $user['utype'] ?? '';
            if ($userRole !== $requiredRole) {
                $this->sendErrorResponse('Forbidden', 403);
            }
        }

        return $user;
    }

    /**
     * Helper to get user by email
     */
    public function getUserByEmail($email)
    {
        $db = $this->getDb();

        if (!$db) {
            error_log('Auth error: No database connection found');
            return false;
        }

        try {
            return $db->query("SELECT * FROM user WHERE uemail = ?", [$email])->fetch();
        } catch (\Exception $e) {
            error_log('Database error in Auth: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Base64Url Encoding
     */
    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64Url Decoding
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Get Authorization header
     */
    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('getallheaders')) {
            $requestHeaders = getallheaders();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Send error response
     */
    private function sendErrorResponse($message, $code)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}
