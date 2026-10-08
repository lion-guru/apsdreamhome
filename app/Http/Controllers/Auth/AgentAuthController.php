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
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        extract(compact('csrf_token', 'errors', 'old'));
        include_once __DIR__ . '/../../../views/auth/agent_register.php';
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
        $captcha_code = trim($_POST['captcha_code'] ?? '');
        if (empty($captcha_code) || !SimpleCaptcha::validate($captcha_code)) {
            $_SESSION['errors'] = ["Invalid or expired security code. Please try again."];
            header('Location: ' . BASE_URL . '/agent/login');
            exit;
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

    public function logout()
    {
        @session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}
