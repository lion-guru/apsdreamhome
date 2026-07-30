<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class FarmerAuthController extends BaseController
{
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

        if (empty($phone)) {
            $_SESSION['flash_error'] = 'Please enter your phone number';
            header('Location: ' . BASE_URL . '/farmer/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $farmer = $db->fetchOne("SELECT * FROM farmers WHERE phone = ? $tSql LIMIT 1", array_merge([$phone], $tParams));

            if ($farmer) {
                $_SESSION['farmer_id'] = $farmer['id'];
                $_SESSION['farmer_name'] = $farmer['name'];
                $_SESSION['farmer_phone'] = $farmer['phone'];
                $_SESSION['farmer_email'] = $farmer['email'] ?? '';
                $_SESSION['farmer_role'] = 'farmer';

                header('Location: ' . BASE_URL . '/farmer/dashboard');
                exit;
            } else {
                $_SESSION['flash_error'] = 'No farmer account found with this phone number. Please contact admin.';
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
