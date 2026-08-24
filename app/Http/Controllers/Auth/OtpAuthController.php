<?php
/**
 * OtpAuthController — OTP-based login and smart registration
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;
use App\Services\OTPService;
use App\Services\ProgressiveRegistrationService;
use App\Core\Middleware\TenantContext;

class OtpAuthController extends BaseController
{
    private UserRegistrationService $regService;
    private OTPService $otpService;
    private ProgressiveRegistrationService $progressiveService;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new UserRegistrationService();
        $this->otpService = new OTPService();
        $this->progressiveService = new ProgressiveRegistrationService($this->db ?? \App\Core\Database::getInstance()->getConnection());
    }

    // ============================================================
    // Smart Registration — Role Selection
    // ============================================================

    public function showRoleSelection()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }

        try {
            $db = Database::getInstance();
            $session = $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ? LIMIT 1",
                [$token]
            );
            if (!$session || !$session['user_id']) {
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }

            $csrf_token = $this->getCsrfToken();
            include __DIR__ . '/../../../views/auth/smart_register_role.php';
        } catch (\Exception $e) {
            error_log("Role selection error: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
    }

    public function saveRoleSelection()
    {
        @session_start();
        $token = $_POST['token'] ?? '';
        $role = trim($_POST['role'] ?? 'customer');

        if (!in_array($role, ['customer', 'associate', 'agent'], true)) {
            $role = 'customer';
        }

        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }

        try {
            $db = Database::getInstance();
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $session = $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ?" . $tenantSql . " LIMIT 1",
                array_merge([$token], $tenantParams)
            );
            if (!$session || !$session['user_id']) {
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }

            // Update user role
            $userParams = [$role, $session['user_id']];
            if ($tid > 1) $userParams[] = $tid;
            $db->query(
                "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?" . $tenantSql,
                $userParams
            );

            // If MLM role, create mlm_profiles + network_tree + associates (if not already created)
            if (in_array($role, ['associate', 'agent'], true)) {
                $profileParams = [$session['user_id']];
                if ($tid > 1) $profileParams[] = $tid;
                $existingProfile = $db->fetchOne("SELECT id FROM mlm_profiles WHERE user_id = ?" . $tenantSql . " LIMIT 1", $profileParams);
                if (!$existingProfile) {
                    $userParams = [$session['user_id']];
                    if ($tid > 1) $userParams[] = $tid;
                    $user = $db->fetchOne("SELECT name, email, phone, referral_code, referred_by FROM users WHERE id = ?" . $tenantSql, $userParams);
                    if ($user) {
                        $this->createMlmRecordsForExistingUser($session['user_id'], $user, $role);
                    }
                }
            }

            // Update session
            $db->query(
                "UPDATE smart_registration_sessions SET detected_role = ?, registration_status = CASE WHEN ? IN ('associate','agent') THEN 'role_selected' ELSE 'profile_incomplete' END, updated_at = NOW() WHERE id = ?",
                [$role, $role, $session['id']]
            );

            // Update session role
            $_SESSION['role'] = $role;

            header('Location: ' . BASE_URL . '/register/smart/profile-complete?token=' . urlencode($token) . '&role=' . urlencode($role));
            exit;
        } catch (\Exception $e) {
            error_log("Save role selection error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to save role selection';
            header('Location: ' . BASE_URL . '/register/smart/role?token=' . urlencode($token));
            exit;
        }
    }

    // ============================================================
    // Air Login — OTP-based login without password
    // ============================================================

    public function requestAirLoginOtp()
    {
        @session_start();

        $identity = trim($_POST['identity'] ?? $_POST['email'] ?? $_POST['phone'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? $_SESSION['csrf_token'] ?? '';

        if (empty($identity)) {
            $_SESSION['air_login_error'] = 'Email or phone number is required';
            header('Location: ' . BASE_URL . '/auth/air-login');
            exit;
        }

        try {
            $db = Database::getInstance();
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$identity, $identity];
            if ($tid > 1) $params[] = $tid;

            $user = $db->fetchOne(
                "SELECT id, email, phone, name, role, status, registration_status FROM users WHERE (email = ? OR phone = ?) AND status != 'deleted'" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$user) {
                $_SESSION['air_login_error'] = 'No account found with this email or phone number';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            if (($user['status'] ?? 'active') !== 'active') {
                $_SESSION['air_login_error'] = 'Account is ' . ($user['status'] ?? 'inactive') . '. Please contact support.';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'rejected') {
                $_SESSION['air_login_error'] = 'Registration has been rejected.';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            if (($user['registration_status'] ?? 'approved') === 'pending') {
                $_SESSION['air_login_error'] = 'Account pending approval. You will be notified once approved.';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            // Send OTP
            require_once __DIR__ . '/../../../Services/OTPService.php';
            $otpService = new \App\Services\OTPService();

            $identifier = filter_var($identity, FILTER_VALIDATE_EMAIL) ? $user['email'] : $user['phone'];
            $type = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'sms';

            $result = $otpService->sendOTP($identifier, $type, 'login');

            if (!$result['success']) {
                error_log("Air Login OTP send failed for $identity: " . $result['message']);
                $_SESSION['air_login_error'] = 'Failed to send OTP. Please try again.';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            // Store login context in session for verification step
            $_SESSION['air_login_context'] = [
                'user_id' => (int)$user['id'],
                'identity' => $identifier,
                'type' => $type,
                'role' => $user['role'] ?? 'customer',
                'name' => $user['name'],
            ];

            $_SESSION['air_login_success'] = 'OTP sent! Please check your ' . ($type === 'email' ? 'email' : 'phone') . ' for verification code.';
            header('Location: ' . BASE_URL . '/auth/air-login/verify');
            exit;

        } catch (\Exception $e) {
            error_log("Air Login OTP request error: " . $e->getMessage());
            $_SESSION['air_login_error'] = 'Failed to send OTP. Please try again.';
            header('Location: ' . BASE_URL . '/auth/air-login');
            exit;
        }
    }

    public function showAirLoginVerify()
    {
        @session_start();

        // Must have air_login context in session
        if (!isset($_SESSION['air_login_context'])) {
            header('Location: ' . BASE_URL . '/auth/air-login');
            exit;
        }

        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id']) && !isset($_SESSION['air_login_context'])) {
            $this->redirectToDashboard($_SESSION['role'] ?? 'customer');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['air_login_error'] ?? null;
        $success = $_SESSION['air_login_success'] ?? null;
        $expires_at = $_SESSION['air_login_otp_expires'] ?? null;
        $identifier_type = $_SESSION['air_login_context']['type'] ?? 'email';
        $identifier = $_SESSION['air_login_context']['identifier'] ?? '';

        // Show masked identifier
        if ($identifier_type === 'email') {
            $masked = $this->maskEmail($identifier);
        } else {
            $masked = $this->maskPhone($identifier);
        }

        unset($_SESSION['air_login_error'], $_SESSION['air_login_success'], $_SESSION['air_login_otp_expires']);

        include __DIR__ . '/../../../views/auth/air_login_verify.php';
    }

    public function verifyAirLoginOtp()
    {
        @session_start();

        if (!isset($_SESSION['air_login_context'])) {
            header('Location: ' . BASE_URL . '/auth/air-login');
            exit;
        }

        $otp = trim($_POST['otp'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $_SESSION['air_login_error'] = 'Please enter a valid 6-digit OTP';
            header('Location: ' . BASE_URL . '/auth/air-login/verify');
            exit;
        }

        $context = $_SESSION['air_login_context'];

        try {
            require_once __DIR__ . '/../../../Services/OTPService.php';
            $otpService = new \App\Services\OTPService();

            $result = $otpService->verifyOTP(
                $context['identifier'],
                $otp,
                'login'
            );

            if (!$result['success']) {
                $_SESSION['air_login_error'] = $result['message'] ?? 'Invalid OTP';
                header('Location: ' . BASE_URL . '/auth/air-login/verify');
                exit;
            }

            // OTP verified — set up the session
            $db = Database::getInstance();
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [(int)$context['user_id']];
            if ($tid > 1) $params[] = $tid;

            $user = $db->fetchOne(
                "SELECT * FROM users WHERE id = ?" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$user) {
                unset($_SESSION['air_login_context']);
                $_SESSION['air_login_error'] = 'User not found';
                header('Location: ' . BASE_URL . '/auth/air-login');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();

            $role = $user['role'] ?? 'customer';
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;

            $adminRoles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'sales_director', 'marketing_director', 'construction_director', 'finance_director', 'hr_director', 'operations_director', 'legal_head', 'finance_head', 'hr_head', 'operations_head', 'department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager', 'legal_advisor', 'chartered_accountant', 'senior_developer', 'employee', 'telecaller'];

            if (in_array($role, ['agent', 'associate'], true)) {
                try {
                    $assParams = [$user['id']];
                    if ($tid > 1) $assParams[] = $tid;
                    $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tenantSql . " LIMIT 1", $assParams);
                    if ($ass) {
                        $_SESSION['associate_id'] = (int)$ass['id'];
                        if ($role === 'agent') $_SESSION['agent_id'] = (int)$ass['id'];
                    }
                } catch (\Throwable $e) { error_log("Associate ID error: " . $e->getMessage()); }
            } elseif ($role === 'employee' || $role === 'telecaller') {
                try {
                    $empParams = [$user['id']];
                    if ($tid > 1) $empParams[] = $tid;
                    $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tenantSql . " LIMIT 1", $empParams);
                    if ($emp) $_SESSION['employee_id'] = (int)$emp['id'];
                } catch (\Throwable $e) { error_log("Employee ID error: " . $e->getMessage()); }
            }

            if (in_array($role, $adminRoles, true)) {
                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_user_id'] = (int)$user['id'];
                $_SESSION['admin_email'] = $user['email'] ?? '';
                $_SESSION['admin_role'] = $role;
                $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
                $_SESSION['admin_username'] = $user['name'] ?? 'admin';
            }

            try {
                require_once __DIR__ . '/../../../Services/AuditService.php';
                (new \App\Services\AuditService($db))->log('login', (int)$user['id'], $role, 'user', (int)$user['id'], 'Air Login (OTP)');
            } catch (\Throwable $e) { error_log("Audit log error: " . $e->getMessage()); }

            try {
                require_once __DIR__ . '/../../../Services/Communication/LoginNotificationService.php';
                $loginNotifier = new \App\Services\Communication\LoginNotificationService();
                $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
                $loginNotifier->sendLoginAlerts((int)$user['id'], $role, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', $isMobile, 'otp');
            } catch (\Throwable $e) { error_log("Login notification error: " . $e->getMessage()); }

            unset($_SESSION['air_login_context']);
            $_SESSION['login_success'] = "Welcome back, {$user['name']}!";
            $this->redirectToDashboard($role);
            exit;

        } catch (\Exception $e) {
            $_SESSION['air_login_error'] = 'OTP verification failed. Please try again.';
            header('Location: ' . BASE_URL . '/auth/air-login/verify');
            exit;
        }
    }

    public function showAirLogin()
    {
        @session_start();
        if (isset($_SESSION['user_id']) && !isset($_SESSION['air_login_context'])) {
            $this->redirectToDashboard($_SESSION['role'] ?? 'customer');
            exit;
        }

        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['air_login_error'] ?? null;
        $success = $_SESSION['air_login_success'] ?? null;
        unset($_SESSION['air_login_error'], $_SESSION['air_login_success']);

        include __DIR__ . '/../../../views/auth/air_login.php';
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        $maskedName = strlen($name) > 2
            ? substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1)
            : str_repeat('*', strlen($name));
        return $maskedName . '@' . $domain;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) <= 4) return str_repeat('*', strlen($digits));
        $visibleEnd = 4;
        $maskedPart = str_repeat('*', strlen($digits) - $visibleEnd);
        return $maskedPart . substr($digits, -$visibleEnd);
    }

    private function redirectToDashboard(string $role): void
    {
        $map = [
            'admin' => '/admin/dashboard',
            'super_admin' => '/admin/dashboard',
            'manager' => '/admin/dashboard',
            'employee' => '/employee/dashboard',
            'associate' => '/associate/dashboard',
            'agent' => '/agent/dashboard',
            'telecaller' => '/employee/dashboard',
            'customer' => '/dashboard',
        ];
        $redirect = $map[$role] ?? '/admin/dashboard';
        header('Location: ' . BASE_URL . $redirect);
    }

    private function createMlmRecordsForExistingUser(int $userId, array $user, string $role): void
    {
        $db = Database::getInstance();
        $referralCode = $user['referral_code'] ?? ('REF' . $userId . date('ymd'));
        $sponsorId = !empty($user['referred_by']) ? (int)$user['referred_by'] : null;

        try {
            $db->beginTransaction();

            // mlm_profiles
            $existing = $db->fetchOne("SELECT id FROM mlm_profiles WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$existing) {
                $db->insert('mlm_profiles', [
                    'user_id' => $userId,
                    'referral_code' => $referralCode,
                    'sponsor_user_id' => $sponsorId,
                    'user_type' => $role,
                    'current_level' => 'associate',
                    'total_team_size' => 0,
                    'direct_referrals' => 0,
                    'total_commission' => 0.00,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // associates
            $existing = $db->fetchOne("SELECT id FROM associates WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$existing) {
                $db->insert('associates', [
                    'user_id' => $userId,
                    'name' => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'referral_code' => $referralCode,
                    'sponsor_id' => $sponsorId,
                    'level' => $role === 'agent' ? 'agent' : 'associate',
                    'status' => 'active',
                    'joining_date' => date('Y-m-d'),
                ]);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("createMlmRecordsForExistingUser error: " . $e->getMessage());
        }
    }

    private function getTenantSql(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) return [" AND tenant_id = ?", [$tid]];
        return ["", []];
    }
    
    private function getTenantInsert(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) return ["tenant_id" => $tid];
        return [];
    }

    public function skipCsrfProtection(): bool
    {
        return true;
    }
    
    /**
     * Step 1: Show phone input page
     */
    public function showPhoneInput()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $base = BASE_URL;
        
        // Check for returning user with tracking cookie
        $trackingToken = $_COOKIE['smart_reg_token'] ?? null;
        $session = null;
        
        if ($trackingToken) {
            $session = $this->getSessionByToken($trackingToken);
            if ($session && $session['registration_status'] === 'profile_complete') {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_phone.php';
    }
    
    /**
     * Step 2: Send OTP via selected channel
     */
    public function sendOtp()
    {
        @session_start();
        
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $channel = trim($_POST['channel'] ?? 'whatsapp');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if ($csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
            $_SESSION['error'] = 'Please enter a valid 10-digit phone number.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            
            $existingUser = $db->fetchOne("SELECT id, email, role FROM users WHERE phone = ?" . $tSql . " LIMIT 1", array_merge([$phone], $tParams));
            if ($existingUser) {
                $_SESSION['error'] = 'This phone number is already registered. Please login instead.';
                $_SESSION['show_login_prompt'] = true;
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $sessionToken = bin2hex(random_bytes(32));
            
            $existingSession = $db->fetchOne(
                "SELECT id FROM smart_registration_sessions WHERE phone = ? AND registration_status NOT IN ('profile_complete', 'abandoned')" . $tSql . " ORDER BY id DESC LIMIT 1",
                array_merge([$phone], $tParams)
            );
            
            if ($existingSession) {
                $sessionId = $existingSession['id'];
                $db->query(
                    "UPDATE smart_registration_sessions SET otp_code = ?, otp_channel = ?, otp_sent_at = NOW(), registration_status = 'otp_sent', email = COALESCE(?, email), updated_at = NOW() WHERE id = ?" . $tSql,
                    array_merge([$otp, $channel, !empty($email) ? $email : null, $sessionId], $tParams)
                );
            } else {
                $db->insert('smart_registration_sessions', array_merge([
                    'session_token' => $sessionToken,
                    'phone' => $phone,
                    'email' => !empty($email) ? $email : null,
                    'otp_channel' => $channel,
                    'otp_code' => $otp,
                    'otp_sent_at' => date('Y-m-d H:i:s'),
                    'registration_status' => 'otp_sent',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                    'landing_page' => $_SERVER['REQUEST_URI'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], $this->getTenantInsert()));
                $sessionId = $db->lastInsertId();
            }
            
            $_SESSION['smart_reg_session_id'] = $sessionId;
            $_SESSION['smart_reg_phone'] = $phone;
            $_SESSION['smart_reg_otp'] = $otp;
            $_SESSION['smart_reg_channel'] = $channel;
            
            try {
                if ($channel === 'whatsapp') {
                    $this->sendWhatsAppOtp($phone, $otp);
                } elseif ($channel === 'sms') {
                    $this->sendSmsOtp($phone, $otp);
                } elseif ($channel === 'email' && !empty($email)) {
                    $this->sendEmailOtp($email, $otp);
                }
            } catch (\Exception $e) {
                error_log("OTP send failed: " . $e->getMessage());
            }
            
            setcookie('smart_reg_token', $sessionToken, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($sessionToken));
            exit;
            
        } catch (\Exception $e) {
            error_log("Smart registration error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
    }
    
    /**
     * Step 3: Show OTP verification page
     */
    public function showOtpVerification()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        $base = BASE_URL;
        
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        $session = $this->getSessionByToken($token);
        if (!$session) {
            $_SESSION['error'] = 'Invalid or expired session. Please start again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if ($session['registration_status'] === 'profile_complete') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_otp.php';
    }
    
    /**
     * Step 4: Verify OTP and auto-create account
     */
    public function verifyOtp()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if ($csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if (empty($token) || empty($otp)) {
            $_SESSION['error'] = 'Please enter the OTP.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $tInsert = $this->getTenantInsert();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                $_SESSION['error'] = 'Invalid or expired session.';
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            $attempts = $db->fetchColumn(
                "SELECT COUNT(*) FROM smart_registration_behavior WHERE session_id = ?" . $tSql . " AND event_type = 'otp_verify_attempt'",
                array_merge([$session['id']], $tParams)
            );
            
            if ($attempts >= 5) {
                $_SESSION['error'] = 'Too many failed attempts. Please request a new OTP.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            $db->insert('smart_registration_behavior', array_merge([
                'session_id' => $session['id'],
                'event_type' => 'otp_verify_attempt',
                'event_data' => json_encode(['otp_submitted' => $otp]),
                'created_at' => date('Y-m-d H:i:s')
            ], $tInsert));
            
            if ($session['otp_code'] !== $otp) {
                $_SESSION['error'] = 'Invalid OTP. Please try again.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            $otpSentAt = strtotime($session['otp_sent_at']);
            if (time() - $otpSentAt > 600) {
                $_SESSION['error'] = 'OTP has expired. Please request a new one.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            $phone = $session['phone'];
            $email = $session['email'];
            
            $unique_id = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($phone, -4)) . date('ymd') . rand(100, 999);
            
            $autoPassword = bin2hex(random_bytes(8));
            $hashedPassword = password_hash($autoPassword, PASSWORD_DEFAULT);
            
            $userData = array_merge([
                'customer_id' => $unique_id,
                'name' => 'User ' . substr($phone, -4),
                'email' => $email ?: $phone . '@temp.apsdreamhome.com',
                'phone' => $phone,
                'password' => $hashedPassword,
                'referral_code' => $referral_code,
                'role' => 'customer',
                'status' => 'active',
                'registration_status' => 'approved',
                'registration_method' => 'smart_otp',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert);
            
            $db->insert('users', $userData);
            $newUserId = $db->lastInsertId();
            
            $db->insert('wallet_points', array_merge([
                'user_id' => $newUserId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert));
            
            $db->query(
                "UPDATE smart_registration_sessions SET user_id = ?, user_created = 1, otp_verified = 1, otp_verified_at = NOW(), registration_status = 'account_created', updated_at = NOW() WHERE id = ?" . $tSql,
                array_merge([$newUserId, $session['id']], $tParams)
            );
            
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['customer_id'] = $unique_id;
            $_SESSION['role'] = 'customer';
            $_SESSION['name'] = $userData['name'];
            
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($newUserId);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }
            
            header('Location: ' . BASE_URL . '/auth/smart/role?token=' . urlencode($token));
            exit;
            
        } catch (\Exception $e) {
            error_log("OTP verification error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
    }
    
    /**
     * Step 5: Show progressive profile completion page
     */
    public function showProfileCompletion()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        $base = BASE_URL;
        
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        $session = $this->getSessionByToken($token);
        if (!$session || !$session['user_id']) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        $db = Database::getInstance();
        [$tSql, $tParams] = $this->getTenantSql();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$session['user_id']], $tParams));
        
        if (!$user) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_profile.php';
    }
    
    /**
     * Save profile completion data (AJAX)
     */
    public function saveProfileProgress()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $session = $this->getSessionByToken($token);
            
            if (!$session || !$session['user_id']) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            
            $fields = ['name', 'email', 'city', 'budget_range', 'property_preference', 'occupation'];
            $filled = 0;
            foreach ($fields as $field) {
                if (!empty($data[$field])) $filled++;
            }
            $completionPct = round(($filled / count($fields)) * 100);
            
            $db->query(
                "UPDATE smart_registration_sessions SET profile_data = ?, profile_completion_pct = ?, registration_status = CASE WHEN ? >= 80 THEN 'profile_complete' ELSE 'profile_incomplete' END, updated_at = NOW() WHERE id = ?" . $tSql,
                array_merge([json_encode($data), $completionPct, $completionPct, $session['id']], $tParams)
            );
            
            if ($completionPct >= 80 && !empty($data['name'])) {
                $db->query(
                    "UPDATE users SET name = ?, city = ?, occupation = ?, updated_at = NOW() WHERE id = ?" . $tSql,
                    array_merge([$data['name'] ?? '', $data['city'] ?? '', $data['occupation'] ?? '', $session['user_id']], $tParams)
                );
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'completion_pct' => $completionPct,
                'is_complete' => $completionPct >= 80
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Profile save error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save']);
            exit;
        }
    }
    
    /**
     * Track user behavior (AJAX)
     */
    public function trackBehavior()
    {
        @session_start();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? $_COOKIE['smart_reg_token'] ?? '';
        $eventType = $data['event_type'] ?? 'page_view';
        $eventData = $data['event_data'] ?? null;
        $pageUrl = $data['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '';
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'No token']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                http_response_code(404);
                echo json_encode(['error' => 'Session not found']);
                exit;
            }
            
            $db->insert('smart_registration_behavior', array_merge([
                'session_id' => $session['id'],
                'user_id' => $session['user_id'],
                'event_type' => $eventType,
                'event_data' => is_array($eventData) ? json_encode($eventData) : null,
                'page_url' => $pageUrl,
                'created_at' => date('Y-m-d H:i:s')
            ], $this->getTenantInsert()));
            
            $updates = ['last_activity_at' => date('Y-m-d H:i:s')];
            
            if ($eventType === 'property_view') {
                $updates['properties_viewed'] = ($session['properties_viewed'] ?? 0) + 1;
            } elseif ($eventType === 'page_view') {
                $updates['pages_viewed'] = ($session['pages_viewed'] ?? 0) + 1;
            } elseif ($eventType === 'search') {
                $updates['search_count'] = ($session['search_count'] ?? 0) + 1;
            }
            
            $setClauses = [];
            $values = [];
            foreach ($updates as $key => $value) {
                $setClauses[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $session['id'];
            
            $db->query(
                "UPDATE smart_registration_sessions SET " . implode(', ', $setClauses) . " WHERE id = ?" . $tSql,
                array_merge($values, $tParams)
            );
            
            $this->detectRoleFromBehavior($db, $session);
            
            http_response_code(200);
            echo json_encode(['success' => true]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Behavior tracking error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to track']);
            exit;
        }
    }
    
    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $channel = $_POST['channel'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid session.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        try {
            $db = Database::getInstance();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                $_SESSION['error'] = 'Invalid or expired session.';
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            $db->query(
                "UPDATE smart_registration_sessions SET otp_code = ?, otp_channel = COALESCE(?, otp_channel), otp_sent_at = NOW(), registration_status = 'otp_sent', updated_at = NOW() WHERE id = ?",
                [$otp, !empty($channel) ? $channel : null, $session['id']]
            );
            
            $channel = $channel ?: $session['otp_channel'];
            $phone = $session['phone'];
            $email = $session['email'];
            
            if ($channel === 'whatsapp') {
                $this->sendWhatsAppOtp($phone, $otp);
            } elseif ($channel === 'sms') {
                $this->sendSmsOtp($phone, $otp);
            } elseif ($channel === 'email' && !empty($email)) {
                $this->sendEmailOtp($email, $otp);
            }
            
            $_SESSION['success'] = 'OTP resent successfully via ' . ucfirst($channel) . '.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
            
        } catch (\Exception $e) {
            error_log("Resend OTP error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to resend OTP.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
    }
    
    /**
     * Skip profile completion (for later)
     */
    public function skipProfileCompletion()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        
        if (!empty($token)) {
            try {
                $db = Database::getInstance();
                $session = $this->getSessionByToken($token);
                
                if ($session) {
                    $db->query(
                        "UPDATE smart_registration_sessions SET registration_status = 'profile_incomplete', abandoned_at = NOW(), updated_at = NOW() WHERE id = ?",
                        [$session['id']]
                    );
                }
            } catch (\Exception $e) {
                error_log("Skip profile error: " . $e->getMessage());
            }
        }
        
        $_SESSION['success'] = 'Welcome! You can complete your profile later from your dashboard.';
        header('Location: ' . BASE_URL . '/user/dashboard');
        exit;
    }
    
    // ==================== PRIVATE HELPER METHODS ====================
    
    private function getSessionByToken($token)
    {
        try {
            $db = Database::getInstance();
            return $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ? LIMIT 1",
                [$token]
            );
        } catch (\Exception $e) {
            error_log("Get session error: " . $e->getMessage());
            return null;
        }
    }
    
    private function sendWhatsAppOtp($phone, $otp)
    {
        try {
            $whatsappService = new \App\Services\Communication\WhatsAppService();
            $message = "Your APS Dream Home verification code is: *$otp*\n\nThis code expires in 10 minutes.\n\nDo not share this code with anyone.";
            return $whatsappService->sendTextMessage($phone, $message);
        } catch (\Exception $e) {
            error_log("WhatsApp OTP failed: " . $e->getMessage());
            return $this->sendSmsOtp($phone, $otp);
        }
    }
    
    private function sendSmsOtp($phone, $otp)
    {
        try {
            $smsService = new \App\Services\Communication\SmsService();
            return $smsService->sendOTP($phone, $otp);
        } catch (\Exception $e) {
            error_log("SMS OTP failed: " . $e->getMessage());
            error_log("OTP for $phone: $otp");
            return true;
        }
    }
    
    private function sendEmailOtp($email, $otp)
    {
        try {
            $subject = "APS Dream Home - Verification Code";
            $message = "Your verification code is: $otp\n\nThis code expires in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
            $headers = "From: noreply@apsdreamhome.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            return mail($email, $subject, $message, $headers);
        } catch (\Exception $e) {
            error_log("Email OTP failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function detectRoleFromBehavior($db, $session)
    {
        $stats = $db->fetchOne(
            "SELECT 
                SUM(CASE WHEN event_type = 'property_view' THEN 1 ELSE 0 END) as property_views,
                SUM(CASE WHEN event_type = 'agent_listing_view' THEN 1 ELSE 0 END) as agent_views,
                SUM(CASE WHEN event_type = 'earn_money_click' THEN 1 ELSE 0 END) as earn_clicks,
                SUM(CASE WHEN event_type = 'commission_page_view' THEN 1 ELSE 0 END) as commission_views
            FROM smart_registration_behavior WHERE session_id = ?",
            [$session['id']]
        );
        
        if (!$stats) return;
        
        $role = 'customer';
        $confidence = 0.5;
        
        if ($stats['earn_clicks'] > 0 || $stats['commission_views'] > 2) {
            $role = 'associate';
            $confidence = 0.7 + min($stats['earn_clicks'] * 0.05, 0.2);
        } elseif ($stats['agent_views'] > 2) {
            $role = 'agent';
            $confidence = 0.6 + min($stats['agent_views'] * 0.05, 0.2);
        } elseif ($stats['property_views'] > 5) {
            $role = 'customer';
            $confidence = 0.8;
        }
        
        $db->query(
            "UPDATE smart_registration_sessions SET detected_role = ?, role_confidence = ?, updated_at = NOW() WHERE id = ?",
            [$role, min($confidence, 1.0), $session['id']]
        );
    }

    public function adminDashboard()
    {
        @session_start();
        if (!isset($_SESSION['admin_id']) && empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $db = Database::getInstance();
        $total = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions")->c ?? 0;
        $pendingOtp = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'pending_otp'")->c ?? 0;
        $otpSent = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'otp_sent'")->c ?? 0;
        $abandoned = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'abandoned'")->c ?? 0;
        $completed = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'profile_complete'")->c ?? 0;
        $accountCreated = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE user_created = 1")->c ?? 0;

        $sessions = $db->fetchAll("SELECT * FROM smart_registration_sessions ORDER BY created_at DESC LIMIT 50");
        $roles = $db->fetchAll("SELECT detected_role, COUNT(*) as c FROM smart_registration_sessions WHERE detected_role IS NOT NULL GROUP BY detected_role");
        $channels = $db->fetchAll("SELECT otp_channel, COUNT(*) as c FROM smart_registration_sessions WHERE otp_channel IS NOT NULL GROUP BY otp_channel");

        $conversionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        include __DIR__ . '/../../../views/admin/smart_registration/dashboard.php';
    }

    public function adminSessionDetail()
    {
        @session_start();
        if (!isset($_SESSION['admin_id']) && empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $db = Database::getInstance();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/smart-registration');
            exit;
        }

        $session = $db->fetchOne("SELECT * FROM smart_registration_sessions WHERE id = ?", [$id]);
        if (!$session) {
            $_SESSION['error'] = 'Session not found';
            header('Location: ' . BASE_URL . '/admin/smart-registration');
            exit;
        }

        $behavior = $db->fetchAll("SELECT * FROM smart_registration_behavior WHERE session_id = ? ORDER BY created_at DESC", [$session['id']]);

        include __DIR__ . '/../../../views/admin/smart_registration/detail.php';
    }
}
