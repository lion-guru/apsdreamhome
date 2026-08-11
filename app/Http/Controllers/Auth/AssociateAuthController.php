<?php

/**
 * Associate Authentication Controller
 *
 * @deprecated Use CoreAuthController instead. Kept for backward compatibility.
 *             Registration now delegates to UserRegistrationService.
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\UserRegistrationService;
use App\Core\Middleware\TenantContext;

class AssociateAuthController extends BaseController
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

    private function getPublicStats(): array
    {
        static $cached = null;
        if ($cached !== null) return $cached;
        try {
            $db = Database::getInstance();
            $totalPaid = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE status IN ('approved','paid','pending')")['total'] ?? 0;
            $commissionCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_commission_ledger")['cnt'] ?? 0;
            $rankCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_rank_benefits WHERE is_active = 1")['cnt'] ?? 7;
            $maxRate = $db->fetchOne("SELECT MAX(direct_sale_pct) as max_rate FROM mlm_rank_benefits WHERE is_active = 1")['max_rate'] ?? 20;
            $cached = [
                'total_paid' => $totalPaid,
                'commission_count' => $commissionCount,
                'rank_count' => $rankCount,
                'max_rate' => $maxRate,
            ];
        } catch (\Exception $e) {
            $cached = ['total_paid' => 10560320, 'commission_count' => 311, 'rank_count' => 7, 'max_rate' => 20];
        }
        return $cached;
    }

    public function associateRegister()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        $base = BASE_URL;
        $stats = $this->getPublicStats();
        extract(compact('csrf_token', 'errors', 'old', 'stats'));
        include __DIR__ . '/../../../views/auth/associate_register.php';
    }

    public function handleAssociateRegister()
    {
        @session_start();

        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['sponsor_code'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }

        try {
            $regService = new UserRegistrationService();
            $result = $regService->createUser('associate', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $referral,
                'registration_method' => 'web',
            ]);

            if (!$result['success']) {
                $_SESSION['errors'] = [$result['message']];
                $_SESSION['old_input'] = $_POST;
                header('Location: ' . BASE_URL . '/associate/register');
                exit;
            }

            $_SESSION['success'] = $result['message'];

            // Mark visitor as converted
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($result['user_id']);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }

            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            error_log("Associate registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }
    }

    public function associateLogin()
    {
        @session_start();
        if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'associate') {
            header('Location: ' . BASE_URL . '/associate/dashboard');
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
        $base = BASE_URL;
        $stats = $this->getPublicStats();
        extract(compact('csrf_token', 'error', 'success', 'stats'));
        include __DIR__ . '/../../../views/auth/associate_login.php';
    }

    public function authenticateAssociate()
    {
        @session_start();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $user = $db->fetchOne("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = 'associate'" . $tSql . " LIMIT 1", array_merge([$email, $email], $tParams));
            if ($user && password_verify($password, $user['password'])) {
                // Check registration status first
                if (($user['registration_status'] ?? 'approved') === 'pending') {
                    $_SESSION['errors'] = ["Your account is pending admin approval. You will be able to login once approved."];
                    header('Location: ' . BASE_URL . '/associate/login');
                    exit;
                }
                if (($user['status'] ?? 'active') !== 'active') {
                    $_SESSION['errors'] = ["Your account has been " . ($user['status'] ?? 'inactive') . ". Please contact support."];
                    header('Location: ' . BASE_URL . '/associate/login');
                    exit;
                }
                if (($user['registration_status'] ?? 'approved') === 'rejected') {
                    $_SESSION['errors'] = ["Your registration has been rejected. Please contact support."];
                    header('Location: ' . BASE_URL . '/associate/login');
                    exit;
                }

                $_SESSION['user_id'] = $user['id'];
                
                // Fetch associate_id from associates table
                try {
                    $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tSql . " LIMIT 1", array_merge([$user['id']], $tParams));
                    if ($ass) {
                        $_SESSION['associate_id'] = (int)$ass['id'];
                    } else {
                        // Fallback (though usually it should exist)
                    }
                } catch (\Exception $e) {
                }

                $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['role'] = $user['role'] ?? 'associate';
                $_SESSION['referral_code'] = $user['referral_code'] ?? '';
                $_SESSION['associate_logged_in'] = true;
                $_SESSION['logged_in'] = true;

                // Force redirect to associate dashboard when logging in via associate login
                header('Location: ' . BASE_URL . '/associate/dashboard');
                exit;
            }
            $_SESSION['errors'] = ["Invalid email or password"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ["Login failed"];
            header('Location: ' . BASE_URL . '/associate/login');
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

    /**
     * Get redirect URL based on user type and role
     */
    private function getRedirectUrl($userType, $role)
    {
        // Executive Level
        if (in_array($role, ['super_admin', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro'])) {
            return '/admin/dashboard';
        }

        // Management Level
        if (in_array($role, ['director', 'sales_director', 'marketing_director', 'construction_director'])) {
            return '/admin/dashboard';
        }

        // Departmental Level
        if (in_array($role, ['department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager', 'finance_manager', 'property_manager', 'it_manager', 'operations_manager'])) {
            return '/admin/dashboard';
        }

        // User Type Based Redirect
        switch ($userType) {
            case 'admin':
                return '/admin/dashboard';
            case 'associate':
                return '/associate/dashboard';
            case 'agent':
                return '/agent/dashboard';
            case 'employee':
                return '/employee/dashboard';
            case 'customer':
            default:
                return '/user/dashboard';
        }
    }
}
