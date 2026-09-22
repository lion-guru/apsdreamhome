<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\AuthSessionTrait;

class FarmerAuthController extends BaseController
{
    use AuthSessionTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function getTenantSql(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) return [" AND tenant_id = ?", [$tid]];
        return ["", []];
    }

    public function loginForm()
    {
        @session_start();

        if (isset($_SESSION['farmer_id'])) {
            header('Location: ' . BASE_URL . '/farmer/dashboard');
            exit;
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $this->layout = false;
        ob_start();
        extract(compact('error'));
        $viewPath = __DIR__ . '/../../../views/farmer/login.php';
        if (file_exists($viewPath)) include $viewPath;
        echo ob_get_clean();
    }

    public function login()
    {
        @session_start();

        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($phone)) {
            $_SESSION['flash_error'] = 'Please enter your phone number';
            header('Location: ' . BASE_URL . '/farmer/login');
            exit;
        }

        if (empty($password)) {
            $_SESSION['flash_error'] = 'Please enter your password';
            header('Location: ' . BASE_URL . '/farmer/login');
            exit;
        }

        // Rate limiting: 5 attempts per minute per IP
        require_once __DIR__ . '/../../../Middleware/RateLimiter.php';
        \App\Middleware\RateLimiter::check('farmer_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 60);

        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            
            // Look up user in unified users table with role='farmer'
            $user = $db->fetchOne("SELECT * FROM users WHERE phone = ? AND role = 'farmer' AND status = 'active' $tSql LIMIT 1", array_merge([$phone], $tParams));

            if ($user && password_verify($password, $user['password'])) {
                // Check registration status
                if (($user['registration_status'] ?? 'approved') === 'pending') {
                    $_SESSION['flash_error'] = 'Your account is pending admin approval. You will be notified once approved.';
                    header('Location: ' . BASE_URL . '/farmer/login');
                    exit;
                }
                if (($user['registration_status'] ?? 'approved') === 'rejected') {
                    $_SESSION['flash_error'] = 'Registration has been rejected. Please contact support.';
                    header('Location: ' . BASE_URL . '/farmer/login');
                    exit;
                }
                if (($user['status'] ?? 'active') !== 'active') {
                    $_SESSION['flash_error'] = 'Your account has been ' . ($user['status'] ?? 'inactive') . '. Please contact support.';
                    header('Location: ' . BASE_URL . '/farmer/login');
                    exit;
                }

                // Establish session using trait
                $this->establishSession($user, $phone, 'password');
                
                // Sync farmer profile if exists
                $farmer = $db->fetchOne("SELECT * FROM farmers WHERE user_id = ? $tSql LIMIT 1", array_merge([$user['id']], $tParams));
                if ($farmer) {
                    $db->execute("UPDATE farmers SET user_id = ? WHERE id = ?", [$user['id'], $farmer['id']]);
                }

                header('Location: ' . BASE_URL . '/farmer/dashboard');
                exit;
            } else {
                $_SESSION['flash_error'] = 'Invalid phone number or password';
                header('Location: ' . BASE_URL . '/farmer/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Farmer login error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Login failed. Please try again.';
            header('Location: ' . BASE_URL . '/farmer/login');
            exit;
        }
    }

    public function logout()
    {
        @session_start();
        unset($_SESSION['farmer_id'], $_SESSION['farmer_name'], $_SESSION['farmer_phone'], $_SESSION['farmer_email'], $_SESSION['farmer_role']);
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}
