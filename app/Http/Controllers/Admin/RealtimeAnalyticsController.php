<?php

namespace App\Http\Controllers\Admin;

use App\Services\WebSocketBroadcaster;

class RealtimeAnalyticsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Renders the real-time analytics dashboard page.
     */
    public function dashboard()
    {
        $this->requireAdmin();

        $metrics = $this->computeMetrics();
        $chartData = $this->computeChartData();
        $activities = $this->getRealtimeActivities();

        return $this->render('admin/analytics/realtime', [
            'page_title' => 'Real-Time Analytics — APS Dream Home',
            'metrics'    => $metrics,
            'chart_data' => $chartData,
            'activities' => $activities,
        ]);
    }

    /**
     * JSON endpoint: live KPI metrics.
     */
    public function apiMetrics()
    {
        $this->requireAdmin();
        $this->json($this->computeMetrics());
    }

    /**
     * JSON endpoint: chart data (7-day lead trend, revenue by colony, lead sources, 30-day booking trend).
     */
    public function apiChartData()
    {
        $this->requireAdmin();
        $this->json($this->computeChartData());
    }

    /* ──────────────────────────── private helpers ──────────────────────────── */

    private function computeMetrics(): array
    {
        $m = [];

        // Leads today
        try {
            $m['leads_today'] = (int)($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM leads WHERE DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0);
        } catch (\Exception $e) { $m['leads_today'] = 0; }

        // Bookings this month
        try {
            $m['bookings_month'] = (int)($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM plot_bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status NOT IN ('cancelled','transferred')"
            )['cnt'] ?? 0);
        } catch (\Exception $e) { $m['bookings_month'] = 0; }

        // Revenue this month (total_plot_value of non-cancelled bookings)
        try {
            $m['revenue_month'] = (float)($this->db->fetch(
                "SELECT COALESCE(SUM(total_plot_value),0) AS total FROM plot_bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status NOT IN ('cancelled','transferred')"
            )['total'] ?? 0);
        } catch (\Exception $e) { $m['revenue_month'] = 0; }

        // Collections today (daily_cash_book receipts)
        try {
            $m['collections_today'] = (float)($this->db->fetch(
                "SELECT COALESCE(SUM(amount),0) AS total FROM daily_cash_book WHERE transaction_date = CURDATE() AND transaction_type = 'receipt'"
            )['total'] ?? 0);
        } catch (\Exception $e) { $m['collections_today'] = 0; }

        return $m;
    }

    private function computeChartData(): array
    {
        $d = [];

        // 1) Leads last 7 days
        try {
            $d['leads_7d'] = $this->db->fetchAll(
                "SELECT DATE(created_at) AS dt, COUNT(*) AS cnt
                 FROM leads
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                 GROUP BY DATE(created_at) ORDER BY dt"
            ) ?: [];
        } catch (\Exception $e) { $d['leads_7d'] = []; }

        // 2) Revenue by colony
        try {
            $d['revenue_by_colony'] = $this->db->fetchAll(
                "SELECT c.name AS colony, COALESCE(SUM(pb.total_plot_value),0) AS revenue
                 FROM plot_bookings pb
                 JOIN plots p ON pb.plot_id = p.id
                 JOIN colonies c ON p.colony_id = c.id
                 WHERE pb.status NOT IN ('cancelled','transferred')
                 GROUP BY c.name ORDER BY revenue DESC"
            ) ?: [];
        } catch (\Exception $e) { $d['revenue_by_colony'] = []; }

        // 3) Lead sources breakdown
        try {
            $d['lead_sources'] = $this->db->fetchAll(
                "SELECT COALESCE(source,'Other') AS source, COUNT(*) AS cnt
                 FROM leads GROUP BY source ORDER BY cnt DESC"
            ) ?: [];
        } catch (\Exception $e) { $d['lead_sources'] = []; }

        // 4) Booking trend last 30 days
        try {
            $d['bookings_30d'] = $this->db->fetchAll(
                "SELECT DATE(created_at) AS dt, COUNT(*) AS cnt
                 FROM plot_bookings
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                   AND status NOT IN ('cancelled','transferred')
                 GROUP BY DATE(created_at) ORDER BY dt"
            ) ?: [];
        } catch (\Exception $e) { $d['bookings_30d'] = []; }

        return $d;
    }

    private function getRealtimeActivities(): array
    {
        $activities = [];

        // Recent leads
        try {
            $leads = $this->db->fetchAll(
                "SELECT 'lead' AS type, CONCAT(name,' — ',COALESCE(source,'Unknown')) AS description, created_at
                 FROM leads ORDER BY created_at DESC LIMIT 5"
            ) ?: [];
            foreach ($leads as &$l) { $l['icon'] = 'fa-user-plus'; $l['color'] = '#3b82f6'; }
            $activities = array_merge($activities, $leads);
        } catch (\Exception $e) {}

        // Recent bookings
        try {
            $bookings = $this->db->fetchAll(
                "SELECT 'booking' AS type,
                        CONCAT('Plot #',COALESCE(p.plot_no,'?')) AS description,
                        pb.created_at
                 FROM plot_bookings pb
                 LEFT JOIN plots p ON pb.plot_id = p.id
                 WHERE pb.status NOT IN ('cancelled','transferred')
                 ORDER BY pb.created_at DESC LIMIT 5"
            ) ?: [];
            foreach ($bookings as &$b) { $b['icon'] = 'fa-file-signature'; $b['color'] = '#10b981'; }
            $activities = array_merge($activities, $bookings);
        } catch (\Exception $e) {}

        // Recent payments
        try {
            $payments = $this->db->fetchAll(
                "SELECT 'payment' AS type,
                        CONCAT(transaction_type,' — ₹',FORMAT(amount,0)) AS description,
                        created_at
                 FROM daily_cash_book
                 ORDER BY created_at DESC LIMIT 5"
            ) ?: [];
            foreach ($payments as &$p) { $p['icon'] = 'fa-rupee-sign'; $p['color'] = '#14b8a6'; }
            $activities = array_merge($activities, $payments);
        } catch (\Exception $e) {}

        // Sort by created_at desc, take top 10
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        return array_slice($activities, 0, 10);
    }
}
