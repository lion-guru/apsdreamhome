<?php
/**
 * AIAnalyticsController - Smart CRM Dashboard Analytics
 * Provides auto-insights: "Sales dropped 15% in Colony X because..."
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AI\FreeAIEngines;
use App\Services\AI\RAGKnowledgeManager;

class AIAnalyticsController extends BaseController
{
    protected $aiEngines;
    protected $ragManager;

    public function __construct()
    {
        parent::__construct();
        $this->aiEngines = FreeAIEngines::getInstance();
        $this->ragManager = RAGKnowledgeManager::getInstance();
    }

    protected function setCorsHeaders(): void
    {
        // CORS handled at router level; no-op for API controllers
    }

    /**
     * GET /api/ai/admin/dashboard
     * Smart CRM Dashboard with auto-insights
     */
    public function dashboard()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            // Check admin permissions
            if (!$this->isAdminOrManager($userId)) {
                return $this->jsonError('Insufficient permissions', 403);
            }

            $tid = \App\Core\Middleware\TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Fetch key metrics
            $metrics = $this->fetchDashboardMetrics($tenantWhere, $tenantParams);
            
            // Generate AI insights
            $insights = $this->generateInsights($metrics, $tenantWhere, $tenantParams);

            return $this->jsonSuccess([
                'metrics' => $metrics,
                'insights' => $insights,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'AI Analytics Dashboard');
        }
    }

    /**
     * GET /api/ai/admin/insights
     * Auto-generated insights with explanations
     */
    public function insights()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            if (!$this->isAdminOrManager($userId)) {
                return $this->jsonError('Insufficient permissions', 403);
            }

            $tid = \App\Core\Middleware\TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $metrics = $this->fetchDashboardMetrics($tenantWhere, $tenantParams);
            $insights = $this->generateInsights($metrics, $tenantWhere, $tenantParams, true);

            return $this->jsonSuccess([
                'insights' => $insights,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'AI Insights');
        }
    }

    /**
     * Fetch key dashboard metrics
     */
    private function fetchDashboardMetrics($tenantWhere, $tenantParams)
    {
        $metrics = [];

        // Total leads
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM leads WHERE 1=1 {$tenantWhere}");
        $stmt->execute($tenantParams);
        $metrics['total_leads'] = (int)$stmt->fetchColumn();

        // Leads by status
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as c FROM leads WHERE 1=1 {$tenantWhere} GROUP BY status");
        $stmt->execute($tenantParams);
        $metrics['leads_by_status'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Leads by source
        $stmt = $this->db->prepare("SELECT source, COUNT(*) as c FROM leads WHERE 1=1 {$tenantWhere} GROUP BY source ORDER BY c DESC LIMIT 10");
        $stmt->execute($tenantParams);
        $metrics['leads_by_source'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Total bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM plot_bookings WHERE 1=1 {$tenantWhere}");
        $stmt->execute($tenantParams);
        $metrics['total_bookings'] = (int)$stmt->fetchColumn();

        // Booking revenue
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_plot_value), 0) as total FROM plot_bookings WHERE 1=1 {$tenantWhere} AND status IN ('fully_paid', 'partially_paid', 'emi_active', 'registration_done', 'agreement_signed')");
        $stmt->execute($tenantParams);
        $metrics['booking_revenue'] = (float)$stmt->fetchColumn();

        // Colonies performance
        $stmt = $this->db->prepare("SELECT c.id, c.name, COUNT(p.id) as total_plots, SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available, SUM(CASE WHEN p.status IN ('booked','sold') THEN 1 ELSE 0 END) as sold FROM colonies c LEFT JOIN plots p ON p.colony_id = c.id {$tenantWhere} GROUP BY c.id ORDER BY sold DESC");
        $stmt->execute($tenantParams);
        $metrics['colonies'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Lead conversion rate (last 30 days)
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM leads WHERE 1=1 {$tenantWhere} AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute($tenantParams);
        $recentLeads = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM plot_bookings pb JOIN leads l ON l.id = pb.lead_id WHERE 1=1 {$tenantWhere} AND pb.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute($tenantParams);
        $recentBookings = (int)$stmt->fetchColumn();

        $metrics['conversion_rate_30d'] = $recentLeads > 0 ? round(($recentBookings / $recentLeads) * 100, 2) : 0;

        // Associate performance
        $stmt = $this->db->prepare("SELECT u.id, u.name, COUNT(l.id) as leads_count, COUNT(pb.id) as bookings_count FROM users u LEFT JOIN leads l ON l.assigned_to = u.id {$tenantWhere} LEFT JOIN plot_bookings pb ON pb.lead_id = l.id WHERE u.role IN ('associate', 'agent') GROUP BY u.id ORDER BY bookings_count DESC LIMIT 10");
        $stmt->execute($tenantParams);
        $metrics['top_associates'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $metrics;
    }

    /**
     * Generate AI-powered insights from metrics
     */
    private function generateInsights($metrics, $tenantWhere, $tenantParams, $detailed = false)
    {
        $insights = [];

        // Insight 1: Conversion rate analysis
        $conversionRate = $metrics['conversion_rate_30d'] ?? 0;
        if ($conversionRate < 10) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'conversion',
                'title' => 'Low Lead Conversion Rate',
                'message' => "30-day conversion rate is {$conversionRate}%. Industry benchmark is 15-20%. Review lead qualification and follow-up processes.",
                'action' => 'Review lead qualification criteria and increase follow-up frequency',
                'priority' => 'high'
            ];
        } elseif ($conversionRate > 25) {
            $insights[] = [
                'type' => 'success',
                'category' => 'conversion',
                'title' => 'Excellent Conversion Rate',
                'message' => "30-day conversion rate is {$conversionRate}% - exceeding industry benchmarks!",
                'action' => 'Maintain current follow-up strategy',
                'priority' => 'low'
            ];
        }

        // Insight 2: Colony performance
        $colonies = $metrics['colonies'] ?? [];
        foreach ($colonies as $colony) {
            $total = (int)$colony['total_plots'];
            $sold = (int)$colony['sold'];
            $available = (int)$colony['available'];
            $soldRate = $total > 0 ? round(($sold / $total) * 100, 2) : 0;

            if ($soldRate < 20 && $available > 20) {
                $insights[] = [
                    'type' => 'warning',
                    'category' => 'inventory',
                    'title' => "Slow Sales in {$colony['name']}",
                    'message' => "{$colony['name']} has {$available} available plots but only {$soldRate}% sold. Consider pricing review or targeted campaigns.",
                    'action' => "Review pricing for {$colony['name']} and launch targeted marketing",
                    'priority' => 'medium',
                    'colony_id' => $colony['id']
                ];
            }
        }

        // Insight 3: Source quality
        $sources = $metrics['leads_by_source'] ?? [];
        if (!empty($sources)) {
            arsort($sources);
            $topSource = array_key_first($sources);
            $topCount = $sources[$topSource];
            $total = array_sum($sources);
            $topPercent = round(($topCount / $total) * 100, 1);

            if ($topPercent > 60) {
                $insights[] = [
                    'type' => 'info',
                    'category' => 'sources',
                    'title' => "Over-reliance on {$topSource} Source",
                    'message' => "{$topPercent}% of leads come from {$topSource}. Diversify lead sources to reduce risk.",
                    'action' => 'Invest in additional lead channels (referrals, organic, paid ads)',
                    'priority' => 'medium'
                ];
            }
        }

        // Insight 4: Associate performance
        $associates = $metrics['top_associates'] ?? [];
        if (!empty($associates)) {
            $top = $associates[0];
            $bottom = end($associates);
            
            if (count($associates) > 3 && ($top['bookings_count'] ?? 0) > 5 * ($bottom['bookings_count'] ?? 1)) {
                $insights[] = [
                    'type' => 'info',
                    'category' => 'team',
                    'title' => 'Performance Gap Among Associates',
                    'message' => "Top performer ({$top['name']}) has {$top['bookings_count']} bookings vs {$bottom['name']} with {$bottom['bookings_count']}. Consider mentorship program.",
                    'action' => 'Pair top performers with struggling associates for knowledge sharing',
                    'priority' => 'medium'
                ];
            }
        }

        // Insight 5: Revenue trend (compare with previous period)
        $currentRevenue = $metrics['booking_revenue'] ?? 0;
        if ($currentRevenue > 0) {
            // Simple trend - would need historical data for real comparison
            $insights[] = [
                'type' => 'info',
                'category' => 'revenue',
                'title' => 'Current Period Revenue',
                'message' => 'Total booking revenue: ₹' . number_format($currentRevenue, 0),
                'action' => 'Track weekly to identify trends early',
                'priority' => 'low'
            ];
        }

        // Sort by priority
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($insights, function($a, $b) use ($priorityOrder) {
            return ($priorityOrder[$a['priority']] ?? 3) - ($priorityOrder[$b['priority']] ?? 3);
        });

        return $insights;
    }

    /**
     * Check if user is admin or manager
     */
    private function isAdminOrManager($userId)
    {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user && in_array($user['role'] ?? '', ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'director']);
    }
}