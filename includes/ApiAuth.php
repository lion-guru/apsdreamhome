<?php

/**
 * API Authentication Handler
 * 
 * Handles JWT-based authentication for API endpoints
 */
class ApiAuth
{
    private static $instance = null;
    private $secretKey;
    private $algorithm = 'HS256';
    private $tokenExpiry = 86400; // 24 hours in seconds

    private function __construct()
    {
        // Get secret key from environment or configuration
        $this->secretKey = defined('JWT_SECRET') ? JWT_SECRET : 'your-secret-key-change-in-production';
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Authenticate the current request
     * 
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate()
    {
        $headers = $this->getAuthorizationHeader();

        if (empty($headers)) {
            return false;
        }

        // Extract the token from the header
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
            return $this->validateToken($token);
        }

        return false;
    }

    /**
     * Generate a JWT token for a user
     * 
     * @param array $user User data to include in the token
     * @return string JWT token
     */
    public function generateToken($user)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'user',
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
     * Validate a JWT token
     * 
     * @param string $token JWT token to validate
     * @return array|false Decoded token payload if valid, false otherwise
     */
    public function validateToken($token)
    {
        try {
            $tokenParts = explode('.', $token);

            if (count($tokenParts) !== 3) {
                return false;
            }

            $header = json_decode($this->base64UrlDecode($tokenParts[0]), true);
            $payload = json_decode($this->base64UrlDecode($tokenParts[1]), true);
            $signature = $tokenParts[2];

            // Check if token is expired
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            // Verify signature
            $signatureCheck = hash_hmac('sha256', "$tokenParts[0].$tokenParts[1]", $this->secretKey, true);
            $base64UrlSignature = $this->base64UrlEncode($signatureCheck);

            if (!hash_equals($base64UrlSignature, $signature)) {
                return false;
            }

            return $payload;
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the authorization header
     * 
     * @return string|null Authorization header if found, null otherwise
     */
    private function getAuthorizationHeader()
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    /**
     * Encode a string to URL-safe base64
     * 
     * @param string $data Data to encode
     * @return string URL-safe base64 encoded string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode a URL-safe base64 string
     * 
     * @param string $data URL-safe base64 encoded string
     * @return string Decoded string
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Authenticate a user with email and password
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticateUser($email, $password)
    {
        $db = \App\Core\App::database();

        try {
            $user = $db->fetch("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1", ['email' => $email]);

            if ($user && password_verify($password, $user['password'])) {
                // Remove sensitive data before returning
                unset($user['password']);
                unset($user['reset_token']);
                unset($user['reset_expires']);

                return $user;
            }

            return false;
        } catch (Exception $e) {
            error_log("Database error in authenticateUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Require authentication for the current request
     * 
     * @param string|array $roles Required role(s) for the endpoint
     * @return array Authenticated user data
     * @throws Exception If authentication fails
     */
    public function requireAuth($roles = [])
    {
        $user = $this->authenticate();

        if (!$user) {
            throw new Exception('Authentication required', 401);
        }

        // Check if user has required role
        if (!empty($roles)) {
            $roles = is_array($roles) ? $roles : [$roles];

            if (!in_array($user['role'], $roles)) {
                throw new Exception('Insufficient permissions', 403);
            }
        }

        return $user;
    }
}

// Helper function to get the authenticated user
function getAuthenticatedUser()
{
    return ApiAuth::getInstance()->authenticate();
}

// Helper function to require authentication
function requireAuth($roles = [])
{
    return ApiAuth::getInstance()->requireAuth($roles);
}
