<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\Auth\ApiAuthService;
use App\Services\Auth\JWTAuthService;
use \App\Traits\TenantAwareTrait;

class AuthController extends BaseController
{
    use TenantAwareTrait;
    protected $apiAuthService;
    protected $jwtService;

    public function __construct()
    {
        parent::__construct();
        $this->apiAuthService = new ApiAuthService();
        $this->jwtService = new JWTAuthService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function login()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        // Rate limit login attempts
        \App\Middleware\RateLimiter::check('auth_login', 5, 60);

        $result = $this->apiAuthService->login($email, $password);

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($result['success']) {
            error_log("AuthController Login SUCCESS: email={$email}, ip={$ip}");
        } else {
            error_log("AuthController Login FAILED: email={$email}, ip={$ip}, reason=" . ($result['message'] ?? 'unknown'));
            http_response_code(401);
        }
        echo json_encode($result);
    }

    public function me()
    {
        header('Content-Type: application/json');
        $user = $this->getAuthUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthenticated']);
            return;
        }
        echo json_encode(['success' => true, 'data' => $user]);
    }

    public function refresh()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $token);
        $result = $this->jwtService->refreshToken($token);

        if ($result === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Token refresh failed']);
            return;
        }
        echo json_encode(['success' => true, 'data' => $result]);
    }

    public function logout()
    {
        header('Content-Type: application/json');
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $header);
        if ($token) {
            $this->apiAuthService->logout($token);
        }
        echo json_encode(['success' => true, 'message' => 'Logged out']);
    }

    private function getAuthUser()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $header);
        if (!$token) return null;
        $payload = $this->jwtService->verifyToken($token);
        if (!$payload || !isset($payload['sub'])) return null;
        try {
            $pdo = $this->db->getConnection();
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $pdo->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ?{$tidSql} LIMIT 1");
            $stmt->execute(array_merge([$payload['sub']], $tidParams));
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
