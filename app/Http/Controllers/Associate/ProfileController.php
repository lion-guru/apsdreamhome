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

            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);

            // Get associate info
            $assoc = $db->fetchOne("SELECT * FROM associates WHERE user_id = ?{$tidSql} LIMIT 1", $params);

            // Get wallet
            $wallet = $db->fetchOne("SELECT balance FROM wallet_points WHERE user_id = ?{$tidSql} LIMIT 1", $params);
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

        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);

        $this->render('associate/settings', [
            'page_title' => 'Settings - Associate Portal',
            'page_description' => 'Manage your account settings',
            'user' => $user,
        ], 'layouts/associate');
    }
}

