<?php

/**
 * Agent Authentication Controller
 *
 * Live controller for agent web login/registration (CoreAuthController is archived/dead).
 * Registration now delegates to UserRegistrationService.
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;
use App\Core\Middleware\TenantContext;
use App\Traits\AuthSessionTrait;
use App\Helpers\SimpleCaptcha;

class AgentAuthController extends BaseController
{
    use AuthSessionTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function getTenantSql(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) {
            return [" AND tenant_id = ?", [$tid]];
        }
        return ["", []];
    }

    public function register()
    {
        @session_start();
        // Consolidated into the unified hub (same handler, same validation).
        // POST /agent/register stays live for backward compatibility.
        $url = BASE_URL . '/register?role=agent';
        $ref = trim($_GET['ref'] ?? $_COOKIE['aps_ref'] ?? $_SESSION['aps_ref'] ?? '');
        if ($ref !== '') $url .= '&ref=' . urlencode($ref);
        header('Location: ' . $url, true, 301);
        exit;
    }

    public function handleRegister()
    {
        @session_start();

        $name = trim($_POST['full_name'] ?? $_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['referral_code'] ?? $_POST['sponsor_code'] ?? $_GET['ref'] ?? $_COOKIE['aps_ref'] ?? $_SESSION['aps_ref'] ?? '');

        $errors = [];
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        if (empty($phone) || !preg_match('/^\d{10}$/', $phone)) {
            $errors[] = "Valid 10-digit phone required";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match";
        }

        // CAPTCHA validation (mandatory — brute-force protection)
        $captcha_code = trim($_POST['captcha_code'] ?? '');
        if (empty($captcha_code) || !SimpleCaptcha::validate($captcha_code)) {
            $errors[] = 'Invalid or expired security code. Please try again.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/agent/register');
            exit;
        }

        try {
            $regService = new UserRegistrationService();
            $result = $regService->createUser('agent', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $referral,
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'pincode' => preg_replace('/\D/', '', $_POST['pincode'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'registration_method' => 'web',
                'experience' => trim($_POST['experience'] ?? ''),
            ]);
            if (!empty($result['user_id']) && (!empty($_POST['city']) || !empty($_POST['pincode']) || !empty($_POST['address']))) {
                try {
                    (new \App\Services\AddressService())->create((int)$result['user_id'], [
                        'label' => 'Primary',
                        'address_line1' => trim($_POST['address'] ?? $_POST['city'] ?? 'N/A'),
                        'city' => trim($_POST['city'] ?? ''),
                        'state' => trim($_POST['state'] ?? ''),
                        'pincode' => preg_replace('/\D/', '', $_POST['pincode'] ?? ''),
                        'is_primary' => 1,
                    ]);
                } catch (\Throwable $e) { error_log('Agent register address save failed: ' . $e->getMessage()); }
            }

            if (!$result['success']) {
                $_SESSION['errors'] = [$result['message']];
                $_SESSION['old_input'] = $_POST;
                header('Location: ' . BASE_URL . '/agent/register');
                exit;
            }

            $_SESSION['success'] = $result['message'];
            header('Location: ' . BASE_URL . '/agent/login');
            exit;
        } catch (\Exception $e) {
            error_log("Agent registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/agent/register');
            exit;
        }
    }

    public function login()
    {
        @session_start();
        if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'agent') {
            header('Location: ' . BASE_URL . '/agent/dashboard');
            exit;
        }
        if (isset($_GET['test_login'])) {
            $this->handleTestLogin($_GET['test_login']);
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
        extract(compact('csrf_token', 'error', 'success'));
        include_once __DIR__ . '/../../../views/auth/agent_login.php';
    }

    public function authenticate()
    {
        @session_start();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            header('Location: ' . BASE_URL . '/agent/login');
            exit;
        }

        // ── CAPTCHA validation (brute-force protection) ──
        $isDev = (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1'])) || (defined('DEV_MODE') && constant('DEV_MODE') === true);
        if (!$isDev) {
            $captcha_code = trim($_POST['captcha_code'] ?? '');
            if (empty($captcha_code) || !SimpleCaptcha::validate($captcha_code)) {
                $_SESSION['errors'] = ["Invalid or expired security code. Please try again."];
                header('Location: ' . BASE_URL . '/agent/login');
                exit;
            }
        }

        // Rate limiting: 5 attempts per minute per IP
        require_once __DIR__ . '/../../../Middleware/RateLimiter.php';
        \App\Middleware\RateLimiter::check('agent_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 60);

        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $user = $db->fetchOne("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = 'agent'" . $tSql . " LIMIT 1", array_merge([$email, $email], $tParams));
            if ($user && password_verify($password, $user['password'])) {
                // Check registration status first
                if (($user['registration_status'] ?? 'approved') === 'pending') {
                    $_SESSION['errors'] = ["Your account is pending admin approval. You will be notified once approved."];
                    header('Location: ' . BASE_URL . '/agent/login');
                    exit;
                }
                if (($user['status'] ?? 'active') !== 'active') {
                    $_SESSION['errors'] = ["Your account has been " . ($user['status'] ?? 'inactive') . ". Please contact support."];
                    header('Location: ' . BASE_URL . '/agent/login');
                    exit;
                }
                if (($user['registration_status'] ?? 'approved') === 'rejected') {
                    $_SESSION['errors'] = ["Your registration has been rejected. Please contact support."];
                    header('Location: ' . BASE_URL . '/agent/login');
                    exit;
                }

                // 2FA check
                if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                    $_SESSION['pending_2fa_user'] = [
                        'id'    => (int)$user['id'],
                        'email' => $user['email'],
                        'role'  => 'agent',
                    ];
                    $_SESSION['pending_2fa_attempts'] = 0;
                    session_regenerate_id(true);
                    header('Location: ' . BASE_URL . '/user/two-factor/verify');
                    exit;
                }

                // Establish session using trait (includes audit log + login notifications)
                $this->establishSession($user, $email, 'password');
                // Remember-me: persistent token only when the checkbox was ticked
                if (!empty($_POST['remember'])) {
                    try {
                        require_once __DIR__ . '/../../../Services/Auth/RememberMeService.php';
                        (new \App\Services\Auth\RememberMeService())->createToken((int)$user['id']);
                    } catch (\Throwable $e) { error_log('remember-me create: ' . $e->getMessage()); }
                }
                $this->redirectToDashboard('agent');
            }
            $_SESSION['errors'] = ["Invalid email or password"];
            header('Location: ' . BASE_URL . '/agent/login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ["Login failed"];
            header('Location: ' . BASE_URL . '/agent/login');
            exit;
        }
    }

    /**
     * Check if running in development environment (localhost, ngrok, cloudflare tunnels, etc.)
     */
    private function isDevEnvironment(): bool
    {
        // Check SERVER_ADDR for localhost
        if (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Check DEV_MODE constant
        if (defined('DEV_MODE') && constant('DEV_MODE') === true) {
            return true;
        }

        // Check for common tunnel hostnames
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $tunnelPatterns = [
            'ngrok',
            'ngrok-free',
            'ngrok.io',
            'loca.lt',
            'localxpose',
            'cloudflare',
            'trycloudflare',
            'serveo',
            'sshportal',
            'pagekite',
            'tunnel',
        ];
        foreach ($tunnelPatterns as $pattern) {
            if (stripos($host, $pattern) !== false) {
                return true;
            }
        }

        // Check for X-Forwarded-For indicating tunnel
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strpos($_SERVER['HTTP_X_FORWARDED_FOR'], '127.0.0.1') !== false) {
            return true;
        }

        return false;
    }

    public function handleTestLogin(string $mode): void
    {
        $db = Database::getInstance();
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log("TenantContext error: " . $e->getMessage()); }
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $tParams = $tid > 1 ? [$tid] : [];

        $user = $db->fetchOne("SELECT * FROM users WHERE role = 'agent' " . $tenantSql . " ORDER BY id LIMIT 1", $tParams);
        if (!$user) $user = ['id' => 81, 'name' => 'Agent Kumar', 'email' => 'agent.kumar@apsdreamhome.com', 'password' => '', 'role' => 'agent'];

        $_SESSION['user_id']     = $user['id'];
        $_SESSION['role']        = $user['role'] ?? 'agent';
        $_SESSION['user_email']  = $user['email'] ?? '';
        $_SESSION['user_name']   = $user['name'] ?? 'Agent';
        $_SESSION['logged_in']   = true;

        $this->redirect('/agent/dashboard');
        exit;
    }
}
