<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateCommissionController
 * Handles associate commission tracking
 */
class CommissionController extends BaseController
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
     * Commission overview
     */
    public function commissions()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Commission summary
            $stmt = $db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) AS approved,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) AS pending,
                    COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS paid,
                    COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?{$tidSql}
            ");
            $stmt->execute($params);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

            // Commission by type
            $byType = $db->fetchAll("
                SELECT commission_type, status, COUNT(*) as count, SUM(amount) as total
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?{$tidSql}
                GROUP BY commission_type, status
                ORDER BY commission_type, status
            ", $params) ?: [];

            // Recent commissions
            $recent = $db->fetchAll("
                SELECT * FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?{$tidSql}
                ORDER BY created_at DESC LIMIT 20
            ", $params) ?: [];

            // Monthly trend (last 12 months)
            $monthly = $db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       SUM(amount) as total,
                       SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?{$tidSql}
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ", $params) ?: [];

            $this->render('associate/commissions', [
                'page_title' => 'Commissions - Associate Portal',
                'page_description' => 'Track your commissions',
                'summary' => $summary,
                'by_type' => $byType,
                'recent' => $recent,
                'monthly' => $monthly,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateCommissionController error: ' . $e->getMessage());
            $this->render('associate/commissions', [
                'page_title' => 'Commissions',
                'summary' => [],
                'by_type' => [],
                'recent' => [],
                'monthly' => [],
            ], 'layouts/associate');
        }
    }

    /**
     * Commission calculator
     */
    public function commissionCalculator()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = \App\Core\Database\Database::getInstance()->getConnection();
                $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
                $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];

                $saleAmount = (float)($_POST['sale_amount'] ?? 0);
                $plotArea = (float)($_POST['plot_area'] ?? 0);
                $plotPrice = (float)($_POST['plot_price'] ?? 0);
                $myRank = $_POST['my_rank'] ?? 'associate';

                if ($saleAmount <= 0) {
                    throw new Exception('Sale amount must be greater than 0');
                }

                // Get rank rate
                $stmt = $db->prepare("SELECT rate FROM mlm_rank_slabs WHERE rank_slug = ?{$tidSql} LIMIT 1");
                $stmt->execute([$myRank]);
                $rank = $stmt->fetch(\PDO::FETCH_ASSOC);
                $myRate = $rank ? (float)$rank['rate'] : 5.0;

                // Get plan caps
                $capsStmt = $db->prepare("SELECT global_cap_pct, track_a_pct, track_b_pct, track_c_pct FROM mlm_commission_plans WHERE status = 'active'{$tidSql} ORDER BY version DESC LIMIT 1");
                $capsStmt->execute($params);
                $caps = $capsStmt->fetch(\PDO::FETCH_ASSOC) ?: [
                    'global_cap_pct' => 20,
                    'track_a_pct' => 15,
                    'track_b_pct' => 3,
                    'track_c_pct' => 2,
                ];

                // Direct commission (Track A equivalent)
                $directCommission = min($saleAmount * ($myRate / 100), $saleAmount * ($caps['track_a_pct'] / 100));

                // Team override estimate (simplified)
                $teamOverride = $saleAmount * 0.02; // 2% estimate

                // Milestone escrow (Track C)
                $escrow = $saleAmount * 0.02;

                $totalEstimated = $directCommission + $teamOverride + $escrow;

                $result = [
                    'sale_amount' => $saleAmount,
                    'my_rate' => $myRate,
                    'direct_commission' => round($directCommission, 2),
                    'team_override_est' => round($teamOverride, 2),
                    'escrow_est' => round($escrow, 2),
                    'total_estimated' => round($directCommission + $teamOverride + $escrow, 2),
                ];
            } catch (\Throwable $e) {
                error_log('Commission calculator error: ' . $e->getMessage());
            }
        }

        // Get rank options
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $ranks = $db->fetchAll("SELECT rank_slug, rate FROM mlm_rank_slabs{$tidSql} ORDER BY min_gbv", $params) ?: [
            ['rank_slug' => 'associate', 'rate' => 5],
            ['rank_slug' => 'sr_associate', 'rate' => 7],
            ['rank_slug' => 'bdm', 'rate' => 10],
            ['rank_slug' => 'sr_bdm', 'rate' => 12],
            ['rank_slug' => 'vice_president', 'rate' => 15],
            ['rank_slug' => 'president', 'rate' => 18],
            ['rank_slug' => 'site_manager', 'rate' => 20],
        ];

        $this->render('associate/commission_calculator', [
            'page_title' => 'Commission Calculator - Associate Portal',
            'page_description' => 'Calculate your estimated commission',
            'result' => $result,
            'ranks' => $ranks,
        ], 'layouts/associate');
    }

    /**
     * Rank eligibility check
     */
    public function rankEligibility()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Get current rank and GBV
            $stmt = $db->prepare("SELECT current_level, lifetime_sales FROM mlm_profiles WHERE user_id = ?{$tidSql} LIMIT 1");
            $stmt->execute($params);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);

            $currentRank = $profile['current_level'] ?? 'associate';
            $gbv = (float)($profile['lifetime_sales'] ?? 0);

            // Get rank slabs
            $ranks = $db->fetchAll("SELECT * FROM mlm_rank_slabs{$tidSql} ORDER BY min_gbv", $params) ?: [];

            $eligible = [];
            foreach ($ranks as $rank) {
                $minGbv = (float)$rank['min_gbv'];
                $eligible[] = [
                    'rank' => $rank['rank_slug'],
                    'name' => $rank['rank_name'] ?? $rank['rank_slug'],
                    'rate' => (float)$rank['rate'],
                    'min_gbv' => $minGbv,
                    'eligible' => $gbv >= $minGbv,
                    'shortfall' => max(0, $minGbv - $gbv),
                ];
            }

            $currentRankInfo = null;
            foreach ($eligible as $e) {
                if ($e['rank'] === $currentRank) {
                    $currentRankInfo = $e;
                    break;
                }
            }

            $nextRank = null;
            foreach ($eligible as $e) {
                if (!$e['eligible']) {
                    $nextRank = $e;
                    break;
                }
            }

            $this->render('associate/rank_eligibility', [
                'page_title' => 'Rank Eligibility - Associate Portal',
                'page_description' => 'Check your rank progress',
                'current_rank' => $currentRankInfo,
                'next_rank' => $nextRank,
                'gbv' => $gbv,
                'all_ranks' => $eligible,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('Rank eligibility error: ' . $e->getMessage());
        }
    }
}

