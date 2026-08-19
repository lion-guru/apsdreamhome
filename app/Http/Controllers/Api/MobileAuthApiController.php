<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use App\Traits\TenantAwareTrait;

class MobileAuthApiController extends BaseController
{
    use TenantAwareTrait;
    protected $apiAuthService;
    protected $jwtService;

    public function __construct()
    {
        parent::__construct();
        $this->apiAuthService = new \App\Services\Auth\ApiAuthService();
        $this->jwtService = new \App\Services\Auth\JWTAuthService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function login()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password required']);
            return;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid email format']);
            return;
        }

        \App\Middleware\RateLimiter::check('mobile_login', 5, 60);

        $result = $this->apiAuthService->login($email, $password);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if ($result['success']) {
            error_log("MobileAPI Login SUCCESS: email={$email}, ip={$ip}");
            echo json_encode($result);
        } else {
            error_log("MobileAPI Login FAILED: email={$email}, ip={$ip}, reason=" . ($result['error'] ?? 'unknown'));
            http_response_code(401);
            echo json_encode($result);
        }
    }

    public function register()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);

        $name = \App\Core\Security::sanitize(trim($data['name'] ?? ''));
        $email = trim($data['email'] ?? '');
        $phone = preg_replace('/[^0-9+\-\s()]/', '', trim($data['phone'] ?? ''));
        $password = $data['password'] ?? '';
        $role = in_array(($data['role'] ?? ''), ['customer', 'agent', 'employee']) ? $data['role'] : 'customer';

        if ($name === '' || $email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
            return;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }

        if (!$this->tenantEnforce('add_user')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $_SESSION['error'] ?? 'User limit reached for your plan']);
            return;
        }

        try {
            $pdo = $this->db->getConnection();

            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?{$tidSql} LIMIT 1");
            $stmt->execute(array_merge([$email], $tidParams));
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email already registered']);
                return;
            }

            $hash = Security::hashPassword($password);
            $cols = "name, email, phone, password, role, status, created_at, updated_at";
            $vals = "?, ?, ?, ?, ?, 'active', NOW(), NOW()";
            $uParams = [$name, $email, $phone, $hash, $role];
            $insertExtra = $this->tenantInsertData();
            if (!empty($insertExtra)) { $cols .= ", tenant_id"; $vals .= ", ?"; $uParams[] = $insertExtra['tenant_id']; }
            $stmt = $pdo->prepare("INSERT INTO users ($cols) VALUES ($vals)");
            $stmt->execute($uParams);
            $userId = $pdo->lastInsertId();

            $this->tenantTrackUsage('users');

            $result = $this->apiAuthService->login($email, $password);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Registration succeeded but auto-login failed']);
            }
        } catch (\Throwable $e) {
            error_log('MobileAuthApiController::register failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function googleLogin()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);

        $idToken = $data['id_token'] ?? '';
        $email = trim($data['email'] ?? '');
        $name = \App\Core\Security::sanitize(trim($data['name'] ?? ''));
        $photoUrl = filter_var($data['photo_url'] ?? '', FILTER_SANITIZE_URL);

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            return;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }

        $idToken = substr(trim($idToken), 0, 2048);

        try {
            $pdo = $this->db->getConnection();
            $tid = (int)$this->tenantId();

            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?{$tidSql} LIMIT 1");
            $params = [$email];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                $prefix = 'CUS';
                $customerId = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $newReferralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);

                $cols = "customer_id, name, email, password, role, status, provider, provider_id, avatar, referral_code, created_at, updated_at";
                $vals = "?, ?, ?, ?, 'customer', 'active', 'google', ?, ?, ?, NOW(), NOW()";
                $uParams = [$customerId, $name, $email, $password, $idToken, $photoUrl, $newReferralCode];

                $insertExtra = $this->tenantInsertData();
                if (!empty($insertExtra)) {
                    $cols .= ", tenant_id";
                    $vals .= ", ?";
                    $uParams[] = $insertExtra['tenant_id'];
                }

                $stmt = $pdo->prepare("INSERT INTO users ($cols) VALUES ($vals)");
                $stmt->execute($uParams);
                $userId = $pdo->lastInsertId();

                $tidSql2 = $tid > 1 ? " AND tenant_id = ?" : "";
                $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?{$tidSql2} LIMIT 1");
                $fetchParams = [$userId];
                if ($tid > 1) $fetchParams[] = $tid;
                $stmt2->execute($fetchParams);
                $user = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->tenantTrackUsage('users');
            }

            $token = \App\Core\Security::generateRandomString(64);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $tokenCols = "user_id, token, expires_at, created_at";
            $tokenVals = "?, ?, ?, NOW()";
            $tokenParams = [$user['id'], $token, $expiresAt];
            if ($tid > 1) {
                $tokenCols .= ", tenant_id";
                $tokenVals .= ", ?";
                $tokenParams[] = $tid;
            }
            $stmt = $pdo->prepare("INSERT INTO api_tokens ($tokenCols) VALUES ($tokenVals)");
            $stmt->execute($tokenParams);

            try {
                $profileSql = "SELECT u.id as userId, u.name, u.email, u.phone, u.role,
                        COALESCE(u.created_at, NOW()) as createdAt,
                        COALESCE(u.updated_at, NOW()) as updatedAt,
                        COALESCE(mp.current_level, 'Customer') as rank
                 FROM users u
                 LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                 WHERE u.id = ?" . ($tid > 1 ? " AND u.tenant_id = ?" : "");
                $stmt = $pdo->prepare($profileSql);
                $profileParams = [$user['id']];
                if ($tid > 1) $profileParams[] = $tid;
                $stmt->execute($profileParams);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                error_log('Google login profile fetch: ' . $e->getMessage());
                $userData = [
                    'userId' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'role' => $user['role'],
                    'rank' => 'Customer',
                ];
            }

            $userData['avatar'] = $user['avatar'] ?? null;
            $userData['target'] = 0.0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                ],
                'expires_at' => $expiresAt,
            ]);

        } catch (\Exception $e) {
            error_log('MobileAuthApiController::googleLogin failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Google login failed: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        $this->setCorsHeaders();
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $header);

        if ($token) {
            $this->apiAuthService->logout($token);
        }

        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function forgotPassword()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid email is required']);
                return;
            }

            $resetToken = $this->apiAuthService->sendPasswordReset($email);
            echo json_encode([
                'success' => true,
                'message' => 'Password reset instructions sent to your email',
                'reset_token' => $resetToken
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Forgot Password API error');
        }
    }

    public function verifyOtp()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $otp = preg_replace('/[^0-9]/', '', $input['otp'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($otp)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid email and OTP are required']);
                return;
            }

            if ($this->apiAuthService->verifyOtp($email, $otp)) {
                echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid OTP']);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Verify OTP API error');
        }
    }

    public function resendOtp()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid email is required']);
                return;
            }

            $this->apiAuthService->sendOtp($email);
            echo json_encode(['success' => true, 'message' => 'OTP resent successfully']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Resend OTP API error');
        }
    }

    public function resetPassword()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $otp = preg_replace('/[^0-9]/', '', $input['otp'] ?? '');
            $password = $input['password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? $password;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($otp)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid email and OTP are required']);
                return;
            }

            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
                return;
            }

            if ($password !== $confirmPassword) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
                return;
            }

            if ($this->apiAuthService->resetPassword($email, $otp, $password)) {
                echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid OTP or email']);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Reset Password API error');
        }
    }

    public function changePassword()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $oldPassword = $input['old_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
                return;
            }

            if ($this->apiAuthService->changePassword($userId, $oldPassword, $newPassword)) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Change Password API error');
        }
    }

    public function refresh()
    {
        $this->setCorsHeaders();
        $refreshToken = $_SERVER['HTTP_REFRESH_TOKEN'] ?? '';
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Token is required']);
            return;
        }

        $result = $this->apiAuthService->refreshToken($token, $refreshToken);
        echo json_encode($result);
    }

    public function refreshV2()
    {
        $this->refresh();
    }

    public function checkUser()
    {
        $this->setCorsHeaders();
        $mobile = preg_replace('/[^0-9+]/', '', $_GET['mobile'] ?? $_GET['phone'] ?? '');

        if (empty($mobile)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Mobile number is required']);
            return;
        }

        $user = $this->apiAuthService->findByMobile($mobile);
        if ($user) {
            echo json_encode([
                'success' => true,
                'exists' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode(['success' => true, 'exists' => false]);
        }
    }

    public function getReferrer()
    {
        $this->setCorsHeaders();
        $mobile = preg_replace('/[^0-9+]/', '', $_GET['mobile'] ?? '');

        if (empty($mobile)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Mobile number is required']);
            return;
        }

        $referrer = $this->apiAuthService->findReferrer($mobile);
        echo json_encode($referrer);
    }

    public function firebaseLogin()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $idToken = $input['firebase_id_token'] ?? $input['id_token'] ?? '';

            if (empty($idToken)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Firebase ID token is required']);
                return;
            }

            $result = $this->apiAuthService->firebaseLogin($idToken);
            echo json_encode($result);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Firebase Login API error');
        }
    }

    protected function handleApiError(\Throwable $exception, string $context = 'API Error'): void
    {
        error_log($context . ': ' . $exception->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'message' => 'Internal server error',
            'context' => $context
        ]);
    }

    public function loginV2()
    {
        $this->setCorsHeaders();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'email and password are required', 'code' => 400]);
            return;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid email format', 'code' => 400]);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $pdo->prepare('SELECT id, name, email, role, status, password FROM users WHERE email = ?'.$tidSql.' LIMIT 1');
            $stmt->execute(array_merge([$email], $tidParams));
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials', 'code' => 401]);
                return;
            }

            $hash = $user['password'] ?? '';
            $valid = $hash !== '' && (
                password_verify($password, $hash)
                || (\App\Core\Security::verifyPassword($password, $hash) ?? false)
                || hash_equals($hash, $password)
            );

            if (!$valid) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials', 'code' => 401]);
                return;
            }

            if (isset($user['status']) && $user['status'] === 'suspended') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Account suspended', 'code' => 403]);
                return;
            }

            $role = $user['role'] ?? 'customer';
            $tokens = $this->jwtService->generateToken((int) $user['id'], $role);

            echo json_encode([
                'success' => true,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'user_id' => (int) $user['id'],
                'role' => $role,
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? $email,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::loginV2 failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Login failed: ' . $e->getMessage(), 'code' => 500]);
        }
    }

    public function registerFcmToken()
    {
        $this->setCorsHeaders();
        // Auth already handled by ApiAuthMiddleware on this route
        // $GLOBALS['api_user_id'] is set by middleware

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        // Flutter sends "token", backend expects "device_token" — handle both
        $deviceToken = trim((string) ($data['token'] ?? $data['device_token'] ?? ''));
        $platform = trim((string) ($data['platform'] ?? 'android'));
        $appVersion = trim((string) ($data['app_version'] ?? ''));

        if ($deviceToken === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'token required', 'code' => 400]);
            return;
        }

        $userId = (int) $GLOBALS['api_user_id'];
        $userRole = (string) $GLOBALS['api_user_role'];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // 1. Write to push_tokens (used by JWTAuthService path)
            $tid = (int)$this->tenantId();
            $stmt = $pdo->prepare("
                INSERT INTO push_tokens (user_id, user_type, device_token, platform, is_active, tenant_id, last_used_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, 1, ?, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    is_active = 1,
                    platform = VALUES(platform),
                    last_used_at = NOW(),
                    updated_at = NOW()
            ");
            $stmt->execute([$userId, $userRole, $deviceToken, $platform, $tid]);

            // 2. Also write to mobile_devices (used by PushNotificationService::sendToUser)
            try {
                $existing = $pdo->prepare("SELECT id FROM mobile_devices WHERE device_token = ? LIMIT 1");
                $existing->execute([$deviceToken]);
                $existingDevice = $existing->fetchColumn();

                if ($existingDevice) {
                    $upd = $pdo->prepare("UPDATE mobile_devices SET user_id = ?, platform = ?, last_used_at = NOW(), is_active = 1 WHERE device_token = ? AND tenant_id = ?");
                    $upd->execute([$userId, $platform, $deviceToken, $tid]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO mobile_devices (user_id, device_token, platform, last_used_at, is_active, tenant_id, created_at) VALUES (?, ?, ?, NOW(), 1, ?, NOW())");
                    $ins->execute([$userId, $deviceToken, $platform, $tid]);
                }
            } catch (\Throwable $e) {
                // mobile_devices table might not exist — push_tokens is sufficient
                error_log('FCM register: mobile_devices write failed: ' . $e->getMessage());
            }

            // 3. Subscribe to role-based topic for broadcast notifications
            try {
                $fcmProjectId = $_ENV['FCM_PROJECT_ID'] ?? '';
                if (!empty($fcmProjectId)) {
                    // Topic subscription handled by PushNotificationService
                    $pushSvc = new \App\Services\Communication\PushNotificationService();
                    $pushSvc->subscribeToTopic($deviceToken, 'role_' . $userRole);
                    $pushSvc->subscribeToTopic($deviceToken, 'all');
                }
            } catch (\Throwable $e) {
            // Non-critical — token is already saved
            error_log($e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'FCM token registered',
            ]);

        } catch (\Throwable $e) {
            error_log('registerFcmToken error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error', 'code' => 500]);
        }
    }
}
