<?php
class Auth {
    private static $instance = null;
    private $config;
    private $secretKey;
    private $algorithm = 'HS256';

    private function __construct() {
        $this->config = AppConfig::getInstance();
        $this->secretKey = $this->config->get('auth.jwt_secret');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function login($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $this->generateJWT($user);
        }
        
        return false;
    }

    public function validateToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }

            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);
            $signature = $parts[2];

            if ($header['alg'] !== $this->algorithm) {
                return false;
            }

            $signatureCheck = hash_hmac('sha256', "$parts[0].$parts[1]", $this->secretKey, true);
            $signatureCheckBase64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureCheck));

            if (!hash_equals($signatureCheckBase64, $signature)) {
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

    private function generateJWT($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // Token expires in 24 hours
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return "$base64Header.$base64Payload.$base64Signature";
    }

    private function getUserByEmail($email) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public function getCurrentUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            return $this->validateToken($token);
        }
        
        return false;
    }

    public function requireAuth($requiredRole = null) {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        if ($requiredRole && $user['role'] !== $requiredRole) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        
        return $user;
    }
}
