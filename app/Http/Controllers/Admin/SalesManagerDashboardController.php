<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace

/**
 * Sales Manager Dashboard
 * Key metrics, leaderboard, recent activity for sales team
 */
class SalesManagerDashboardController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $stats = $this->getStats();
        $leaderboard = $this->getLeaderboard();
        $recentLeads = $this->getRecentLeads();
        $recentBookings = $this->getRecentBookings();
        $pipelineByStage = $this->getPipelineByStage();
        $monthlyTrend = $this->getMonthlyTrend();
        $this->render('admin/sales_dashboard/index', [
            'page_title' => 'Sales Dashboard - APS Dream Home',
            'stats' => $stats,
            'leaderboard' => $leaderboard,
            'recentLeads' => $recentLeads,
            'recentBookings' => $recentBookings,
            'pipelineByStage' => $pipelineByStage,
            'monthlyTrend' => $monthlyTrend
        ]);
    }

    private function getStats(): array
    {
        $stats = [
            'total_leads' => 0, 'new_leads_today' => 0, 'new_leads_week' => 0,
            'total_bookings' => 0, 'bookings_month' => 0, 'revenue_month' => 0,
            'conversion_rate' => 0, 'avg_deal_size' => 0,
            'active_agents' => 0, 'commissions_month' => 0
        ];
        try {
            $stats['total_leads'] = (int)$this->fetchScalar("SELECT COUNT(*) FROM leads");
            $stats['new_leads_today'] = (int)$this->fetchScalar("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()");
            $stats['new_leads_week'] = (int)$this->fetchScalar("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['total_bookings'] = (int)$this->fetchScalar("SELECT COUNT(*) FROM bookings");
            $stats['bookings_month'] = (int)$this->fetchScalar("SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['revenue_month'] = (float)$this->fetchScalar("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status NOT IN ('cancelled')");
            $totalLeads = $stats['total_leads'];
            $won = (int)$this->fetchScalar("SELECT COUNT(*) FROM leads WHERE status IN ('closed_won')");
            $stats['conversion_rate'] = $totalLeads > 0 ? round(($won / $totalLeads) * 100, 1) : 0;
            $stats['avg_deal_size'] = $stats['bookings_month'] > 0 ? $stats['revenue_month'] / $stats['bookings_month'] : 0;
            $stats['active_agents'] = (int)$this->fetchScalar("SELECT COUNT(DISTINCT user_id) FROM leads WHERE user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['commissions_month'] = (float)$this->fetchScalar("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'paid'");
        } catch (\Throwable $e) {
            // Tables may be missing
        }
        return $stats;
    }

    private function fetchScalar(string $sql)
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getLeaderboard(): array
    {
        try {
            $sql = "SELECT u.id, u.name, COUNT(l.id) as lead_count, SUM(CASE WHEN l.status = 'closed_won' THEN 1 ELSE 0 END) as won_count FROM users u LEFT JOIN leads l ON l.user_id = u.id AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE u.role IN ('agent', 'associate', 'employee') GROUP BY u.id, u.name HAVING lead_count > 0 ORDER BY won_count DESC, lead_count DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getRecentLeads(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone, source, status, score, created_at FROM leads ORDER BY created_at DESC LIMIT 10");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getRecentBookings(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT b.id, b.status, b.amount, b.created_at, u.name as user_name FROM bookings b LEFT JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT 10");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getPipelineByStage(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT status, COUNT(*) as count, COALESCE(SUM(score), 0) as value FROM leads GROUP BY status");
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $r) $result[$r['status']] = $r;
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getMonthlyTrend(): array
    {
        $months = [];
        try {
            $stmt = $this->db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
            $stmt->execute();
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                $months[$r['month']] = (int)$r['count'];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $result[] = ['month' => $m, 'label' => date('M', strtotime($m . '-01')), 'count' => $months[$m] ?? 0];
        }
        return $result;
    }
}
