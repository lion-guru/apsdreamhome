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
            $stmt = $db->prepare("SELECT points_balance AS balance FROM wallet_points WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1");
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

            // Get associate_id for this user
            $stmt = $db->prepare("SELECT id FROM associates WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $associateRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            $associateId = $associateRow ? (int)$associateRow['id'] : 0;

            // My bookings count
            $myBookings = 0;
            $overdueEmis = 0;
            $emiThisMonth = 0.0;
            if ($associateId > 0) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM plot_bookings WHERE associate_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " AND status NOT IN ('cancelled')");
                $params = [$associateId];
                if ($tid > 1) $params[] = $tid;
                $stmt->execute($params);
                $myBookings = (int)$stmt->fetchColumn();

                // Overdue EMIs
                $stmt = $db->prepare("SELECT COUNT(*) FROM booking_payment_schedules bps
                    JOIN plot_bookings pb ON pb.id = bps.booking_id
                    WHERE pb.associate_id = ? AND bps.status = 'overdue'" . ($tid > 1 ? " AND bps.tenant_id = ?" : ""));
                $params = [$associateId];
                if ($tid > 1) $params[] = $tid;
                $stmt->execute($params);
                $overdueEmis = (int)$stmt->fetchColumn();

                // EMI due this month (pending + overdue)
                $stmt = $db->prepare("SELECT COALESCE(SUM(bps.amount), 0) FROM booking_payment_schedules bps
                    JOIN plot_bookings pb ON pb.id = bps.booking_id
                    WHERE pb.associate_id = ? AND bps.status IN ('pending','overdue')
                    AND YEAR(bps.due_date) = YEAR(CURDATE()) AND MONTH(bps.due_date) = MONTH(CURDATE())" . ($tid > 1 ? " AND bps.tenant_id = ?" : ""));
                $params = [$associateId];
                if ($tid > 1) $params[] = $tid;
                $stmt->execute($params);
                $emiThisMonth = (float)$stmt->fetchColumn();
            }

            // Team monthly sales volume per generation (L1/L2/L3): sqft + value + deals
            // closed this month by downline associates (plot_bookings via associates.user_id).
            $teamVolume = ['L1' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L2' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L3' => ['sqft' => 0, 'value' => 0, 'deals' => 0]];
            try {
                $genIds = [$userId];
                for ($gen = 1; $gen <= 3; $gen++) {
                    if (empty($genIds)) break;
                    $ph = implode(',', array_fill(0, count($genIds), '?'));
                    $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
                    $stmt = $db->prepare("SELECT associate_id FROM mlm_network_tree WHERE sponsor_id IN ($ph)" . $tSql);
                    $gp = $genIds;
                    if ($tid > 1) $gp[] = $tid;
                    $stmt->execute($gp);
                    $genIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
                    if (empty($genIds)) break;
                    $ph2 = implode(',', array_fill(0, count($genIds), '?'));
                    $vSql = "SELECT COUNT(DISTINCT pb.id) AS deals, COALESCE(SUM(p.area_sqft),0) AS sqft, COALESCE(SUM(pb.total_plot_value),0) AS value
                             FROM associates a
                             JOIN plot_bookings pb ON pb.associate_id = a.id
                             LEFT JOIN plots p ON p.id = pb.plot_id
                             WHERE a.user_id IN ($ph2) AND pb.status NOT IN ('cancelled')
                               AND YEAR(pb.created_at) = YEAR(CURDATE()) AND MONTH(pb.created_at) = MONTH(CURDATE())";
                    $vp = $genIds;
                    $vStmt = $db->prepare($vSql);
                    $vStmt->execute($vp);
                    $vr = $vStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                    $teamVolume['L' . $gen] = [
                        'sqft' => (float)($vr['sqft'] ?? 0),
                        'value' => (float)($vr['value'] ?? 0),
                        'deals' => (int)($vr['deals'] ?? 0),
                    ];
                }
            } catch (\Throwable $e) { error_log('Associate dashboard team volume: ' . $e->getMessage()); }

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
                'team_volume' => $teamVolume ?? ['L1' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L2' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L3' => ['sqft' => 0, 'value' => 0, 'deals' => 0]],
                'my_bookings' => $myBookings,
                'overdue_emis' => $overdueEmis,
                'emi_this_month' => $emiThisMonth,
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
                'team_volume' => ['L1' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L2' => ['sqft' => 0, 'value' => 0, 'deals' => 0], 'L3' => ['sqft' => 0, 'value' => 0, 'deals' => 0]],
                'my_bookings' => 0,
                'overdue_emis' => 0,
                'emi_this_month' => 0.0,
            ], 'layouts/associate');
        }
    }
}

