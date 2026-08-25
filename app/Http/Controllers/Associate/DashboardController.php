<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;

/**
 * AssociateDashboardController
 * Handles associate dashboard
 */
class DashboardController extends BaseController
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
     * Associate dashboard
     */
    public function dashboard()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Get user info
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get wallet balance
            $stmt = $db->prepare("SELECT balance FROM wallet_points WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
            $walletBalance = $wallet ? (float)$wallet['balance'] : 0.0;

            // Get commission stats
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) AS total, COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) AS pending FROM mlm_commission_ledger WHERE beneficiary_user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $commissions = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalCommissions = (float)($commissions['total'] ?? 0);
            $pendingCommissions = (float)($commissions['pending'] ?? 0);

            // Get direct referrals count
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM mlm_network_tree WHERE sponsor_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $directRefs = (int)($stmt->fetchColumn() ?? 0);

            // Get total team size
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM mlm_network_tree WHERE associate_id IN (SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?)" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $stmt->execute([$userId]);
            $teamSize = (int)($stmt->fetchColumn() ?? 0);

            // Get recent commissions
            $stmt = $db->prepare("SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 5");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $recentCommissions = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Get my properties
            $stmt = $db->prepare("SELECT * FROM user_properties WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 5");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $myProperties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Get recent leads
            $stmt = $db->prepare("SELECT * FROM leads WHERE assigned_to = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 5");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $recentLeads = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $this->render('associate/dashboard', [
                'page_title' => 'Associate Dashboard - APS Dream Home',
                'page_description' => 'Welcome to your Associate Dashboard',
                'user' => $user,
                'wallet_balance' => $walletBalance,
                'total_commissions' => $totalCommissions,
                'pending_commissions' => $pendingCommissions,
                'direct_referrals' => $directRefs,
                'team_size' => $teamSize,
                'recent_commissions' => $recentCommissions,
                'my_properties' => $myProperties,
                'recent_leads' => $recentLeads,
            ], 'layouts/associate');

        } catch (\Throwable $e) {
            error_log('AssociateDashboardController error: ' . $e->getMessage());
            $this->render('associate/dashboard', [
                'page_title' => 'Associate Dashboard',
                'user' => [],
                'wallet_balance' => 0,
                'total_commissions' => 0,
                'pending_commissions' => 0,
                'direct_referrals' => 0,
                'team_size' => 0,
                'recent_commissions' => [],
                'my_properties' => [],
                'recent_leads' => [],
            ], 'layouts/associate');
        }
    }
}

