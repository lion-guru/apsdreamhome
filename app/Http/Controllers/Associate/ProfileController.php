<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateProfileController
 * Handles associate profile and settings
 */
class ProfileController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $token = $_POST['csrf_token'] ?? '';
                if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                    $_SESSION['flash_error'] = 'Invalid or expired session token. Please try again.';
                    $this->redirect('/associate/profile');
                    return;
                }

                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $state = trim($_POST['state'] ?? '');
                $pincode = trim($_POST['pincode'] ?? '');

                if (empty($name)) {
                    $_SESSION['flash_error'] = 'Name is required.';
                    $this->redirect('/associate/profile');
                    return;
                }

                $updateSql = "UPDATE users SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?, updated_at = NOW() WHERE id = ?{$tidSql}";
                $upParams = array_merge([$name, $phone, $address, $city, $state, $pincode, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
                $db->prepare($updateSql)->execute($upParams);
                $_SESSION['user_name'] = $name;
                $_SESSION['flash_success'] = 'Profile updated successfully.';
                $this->redirect('/associate/profile');
                return;
            }

            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);

            // Get associate info
            $assoc = $db->fetchOne("SELECT * FROM associates WHERE user_id = ?{$tidSql} LIMIT 1", $params);

            // Get wallet
            $wallet = $db->fetchOne("SELECT points_balance AS balance FROM wallet_points WHERE user_id = ?{$tidSql} LIMIT 1", $params);
            $walletBalance = $wallet ? (float)$wallet['balance'] : 0.0;

            // Get commission summary
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) AS total, COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) AS pending FROM mlm_commission_ledger WHERE beneficiary_user_id = ?{$tidSql}");
            $stmt->execute($params);
            $commissions = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

            // Get property count
            $propCount = (int)$db->fetchOne("SELECT COUNT(*) as count FROM user_properties WHERE user_id = ?{$tidSql}", $params)['count'] ?? 0;

            // Get lead count
            $leadCount = (int)$db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ?{$tidSql}", $params)['count'] ?? 0;

            $this->render('associate/profile', [
                'page_title' => 'My Profile - Associate Portal',
                'page_description' => 'View and edit your profile',
                'user' => $user,
                'associate' => $assoc,
                'wallet_balance' => $walletBalance,
                'total_commissions' => (float)($commissions['total'] ?? 0),
                'pending_commissions' => (float)($commissions['pending'] ?? 0),
                'property_count' => $propCount,
                'lead_count' => $leadCount,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateProfileController error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'An unexpected error occurred. Please try again.';
            $this->redirect('/associate/dashboard');
        }
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $_SESSION['flash_error'] = 'Invalid or expired session token. Please try again.';
                $this->redirect('/associate/settings');
                return;
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                $currentUser = $db->fetchOne("SELECT password FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);
                if (!$currentUser || !password_verify($currentPassword, $currentUser['password'])) {
                    $_SESSION['flash_error'] = 'Current password is incorrect.';
                    $this->redirect('/associate/settings');
                    return;
                }

                if (strlen($newPassword) < 8) {
                    $_SESSION['flash_error'] = 'New password must be at least 8 characters long.';
                    $this->redirect('/associate/settings');
                    return;
                }

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['flash_error'] = 'New passwords do not match.';
                    $this->redirect('/associate/settings');
                    return;
                }

                $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                $upParams = array_merge([$hashed, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
                $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?{$tidSql}")->execute($upParams);
                $_SESSION['flash_success'] = 'Password updated successfully.';
                $this->redirect('/associate/settings');
                return;
            }

            if ($action === 'update_notifications') {
                $notifs = [
                    'email_leads' => !empty($_POST['email_leads']) ? 1 : 0,
                    'email_commissions' => !empty($_POST['email_commissions']) ? 1 : 0,
                    'whatsapp_alerts' => !empty($_POST['whatsapp_alerts']) ? 1 : 0,
                    'sms_important' => !empty($_POST['sms_important']) ? 1 : 0,
                    'marketing_emails' => !empty($_POST['marketing_emails']) ? 1 : 0,
                ];
                $json = json_encode($notifs);
                $upParams = array_merge([$json, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
                $db->prepare("UPDATE users SET notification_preferences = ?, updated_at = NOW() WHERE id = ?{$tidSql}")->execute($upParams);
                $_SESSION['flash_success'] = 'Notification preferences updated successfully.';
                $this->redirect('/associate/settings');
                return;
            }

            if ($action === 'toggle_2fa') {
                $enable = !empty($_POST['enable']) ? 1 : 0;
                $upParams = array_merge([$enable, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
                $db->prepare("UPDATE users SET two_factor_enabled = ?, updated_at = NOW() WHERE id = ?{$tidSql}")->execute($upParams);
                $_SESSION['flash_success'] = $enable ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.';
                $this->redirect('/associate/settings');
                return;
            }

            $this->redirect('/associate/settings');
            return;
        }

        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);

        $this->render('associate/settings', [
            'page_title' => 'Settings - Associate Portal',
            'page_description' => 'Manage your account settings',
            'user' => $user,
        ], 'layouts/associate');
    }
}

