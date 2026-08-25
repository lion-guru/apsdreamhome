<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateReferralController
 * Handles referral management
 */
class ReferralController extends BaseController
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
     * Referral dashboard
     */
    public function referral()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Get user's referral code
            $user = $db->fetchOne("SELECT referral_code FROM users WHERE id = ?{$tidSql} LIMIT 1", [$userId]);
            $referralCode = $user['referral_code'] ?? '';

            // Direct referrals
            $direct = $db->fetchAll("
                SELECT u.*, a.status as associate_status, mp.current_level
                FROM users u
                LEFT JOIN associates a ON a.user_id = u.id
                LEFT JOIN mlm_profiles mp ON mp.user_id = u.id
                WHERE u.referred_by = ?{$tidSql}
                ORDER BY u.created_at DESC
            ", $params) ?: [];

            // Referral stats
            $totalReferrals = count($direct);
            $activeReferrals = count(array_filter($direct, fn($d) => ($d['status'] ?? '') === 'active'));

            // Commission from referrals
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'direct_sale'{$tidSql}");
            $stmt->execute($params);
            $referralCommissions = (float)($stmt->fetchColumn() ?? 0);

            // Tier info
            $tier = $this->getReferralTier($totalReferrals);
            $nextTier = $this->getNextTier($totalReferrals);

            $this->render('associate/referral', [
                'page_title' => 'My Referrals - Associate Portal',
                'page_description' => 'Manage your referral network',
                'referral_code' => $referralCode,
                'direct_referrals' => $direct,
                'total_referrals' => $totalReferrals,
                'active_referrals' => $activeReferrals,
                'referral_commissions' => $referralCommissions,
                'tier' => $tier,
                'next_tier' => $nextTier,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateReferralController error: ' . $e->getMessage());
        }
    }

    /**
     * Get referral tier based on count
     */
    private function getReferralTier(int $count): array
    {
        if ($count >= 50) return ['name' => 'Platinum', 'bonus' => 5000, 'per_booking' => 1000, 'color' => '#e5e7eb'];
        if ($count >= 20) return ['name' => 'Gold', 'bonus' => 2500, 'per_booking' => 500, 'color' => '#fbbf24'];
        if ($count >= 10) return ['name' => 'Silver', 'bonus' => 1000, 'per_booking' => 200, 'color' => '#9ca3af'];
        return ['name' => 'Bronze', 'bonus' => 500, 'per_booking' => 100, 'color' => '#cd7f32'];
    }

    /**
     * Get next tier info
     */
    private function getNextTier(int $count): ?array
    {
        if ($count < 10) return ['name' => 'Silver', 'need' => 10 - $count];
        if ($count < 20) return ['name' => 'Gold', 'need' => 20 - $count];
        if ($count < 50) return ['name' => 'Platinum', 'need' => 50 - $count];
        return null;
    }
}

