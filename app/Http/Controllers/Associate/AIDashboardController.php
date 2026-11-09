<?php
/**
 * AIDashboardController — AI-powered dashboard for associates
 * Provides next best actions, churn alerts, priority leads, deal predictions
 */

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Services\LeadScoringService;
use App\Services\DealProbabilityService;
use App\Services\PropertyRecommendationService;
use App\Services\SmartRoutingService;
use App\Core\Database\Database;

class AIDashboardController extends BaseController
{
    protected $db;
    private $leadScoringService;
    private $dealProbabilityService;
    private $recommendationService;
    private $routingService;
    private $cacheService;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->leadScoringService = new LeadScoringService();
        $this->dealProbabilityService = new DealProbabilityService();
        $this->recommendationService = new PropertyRecommendationService();
        $this->routingService = new SmartRoutingService();
        $this->cacheService = new \App\Services\Cache\CacheService();
    }

    private function requireAuth(array $allowedRoles = null)
    {
        @session_start();
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login to access this page';
            $this->redirect('/associate/login');
            exit;
        }
        $allowedRoles = $allowedRoles ?? ['associate', 'agent'];
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['admin_role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
            exit;
        }
    }

    /**
     * AI Dashboard main page
     * GET /associate/ai-dashboard
     */
    public function index(): void
    {
        $this->requireAuth(['associate', 'agent']);
        
        $associateId = $_SESSION['user_id'] ?? 0;

        // Dashboard aggregate cache (300s, same pattern as other 7 dashboards).
        $cacheKey = 'associate_ai_dashboard:' . $associateId;
        $cached = $this->cacheService->get($cacheKey);
        if (is_array($cached) && isset($cached['page_title'])) {
            $this->render('associate/ai_dashboard', $cached);
            return;
        }

        $priorityLeads = $this->getPriorityLeads($associateId);

        $data = [
            'page_title' => 'AI Dashboard',
            'priority_leads' => $priorityLeads,
            'churn_alerts' => $this->getChurnAlerts($associateId),
            'next_best_actions' => $this->getNextBestActions($associateId, $priorityLeads),
            'deal_predictions' => $this->getDealPredictions($associateId),
            'performance_score' => $this->getPerformanceScore($associateId),
            'ai_insights' => $this->getAIInsights($associateId),
            'recommendations' => $this->getRecommendations($associateId),
        ];

        $this->cacheService->set($cacheKey, $data, 300);
        $this->render('associate/ai_dashboard', $data);
    }

    /**
     * Get priority leads needing attention
     */
    private function getPriorityLeads(int $associateId): array
    {
        $result = $this->leadScoringService->getPriorityLeads(10, $associateId);
        return $result['leads'] ?? [];
    }

    /**
     * Get churn alerts for associate
     */
    private function getChurnAlerts(int $associateId): array
    {
        $alerts = [];

        // Leads with no activity in 7+ days
        $staleLeads = $this->db->fetchAll("
            SELECT l.id, l.name, l.phone, l.status, l.created_at,
                   DATEDIFF(NOW(), l.updated_at) as days_stale
            FROM leads l
            WHERE (l.assigned_to = ? OR l.created_by = ?)
            AND l.deleted_at IS NULL
            AND l.status NOT IN ('closed_won', 'closed_lost', 'nurture')
            AND l.updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY days_stale DESC
            LIMIT 10
        ", [$associateId, $associateId]);

        foreach ($staleLeads as $lead) {
            $alerts[] = [
                'type' => 'stale_lead',
                'severity' => $lead['days_stale'] > 14 ? 'high' : 'medium',
                'title' => 'Stale Lead',
                'message' => "{$lead['name']} hasn't been contacted in {$lead['days_stale']} days",
                'lead_id' => $lead['id'],
                'action_url' => "/associate/leads/{$lead['id']}",
            ];
        }

        // High-score leads with no follow-up
        $unfollowed = $this->db->fetchAll("
            SELECT l.id, l.name, l.phone, ls.score
            FROM leads l
            JOIN lead_scores ls ON ls.lead_id = l.id
            WHERE (l.assigned_to = ? OR l.created_by = ?)
            AND l.deleted_at IS NULL
            AND ls.score >= 70
            AND l.status NOT IN ('closed_won', 'closed_lost')
            AND NOT EXISTS (SELECT 1 FROM followups f WHERE f.lead_id = l.id AND f.status = 'pending')
            LIMIT 5
        ", [$associateId, $associateId]);

        foreach ($unfollowed as $lead) {
            $alerts[] = [
                'type' => 'no_followup',
                'severity' => 'high',
                'title' => 'Hot Lead Needs Follow-up',
                'message' => "{$lead['name']} (Score: {$lead['score']}) has no scheduled follow-up",
                'lead_id' => $lead['id'],
                'action_url' => "/associate/leads/{$lead['id']}",
            ];
        }

        return $alerts;
    }

    /**
     * Get next best actions for associate
     */
    private function getNextBestActions(int $associateId, array $priorityLeads = []): array
    {
        $actions = [];

        // 1. Priority leads (high score, stale)
        if (empty($priorityLeads)) {
            $priorityLeads = $this->getPriorityLeads($associateId);
        }
        foreach (array_slice($priorityLeads, 0, 3) as $lead) {
            $actions[] = [
                'type' => 'follow_up',
                'priority' => 'high',
                'title' => 'Follow up with ' . ($lead['name'] ?? 'lead'),
                'description' => 'Score: ' . ($lead['score'] ?? 0) . ' | ' . ($lead['days_since_activity'] ?? 0) . ' days since last contact',
                'icon' => 'fas fa-phone',
                'action_url' => "/associate/leads/{$lead['id']}",
                'color' => 'danger',
            ];
        }

        // 2. Pending follow-ups
        $pendingFollowups = $this->db->fetchAll("
            SELECT f.*, l.name as lead_name, l.phone as lead_phone
            FROM followups f
            LEFT JOIN leads l ON f.lead_id = l.id
            WHERE f.assigned_to = ? AND f.status = 'pending' AND f.followup_date <= NOW()
            ORDER BY f.followup_date ASC
            LIMIT 5
        ", [$associateId]);

        foreach ($pendingFollowups as $f) {
            $actions[] = [
                'type' => 'due_followup',
                'priority' => 'high',
                'title' => 'Due: ' . ($f['lead_name'] ?? 'Follow-up'),
                'description' => 'Scheduled: ' . date('d M H:i', strtotime($f['followup_date'])),
                'icon' => 'fas fa-calendar-check',
                'action_url' => "/associate/leads/{$f['lead_id']}",
                'color' => 'warning',
            ];
        }

        // 3. Site visits scheduled
        $visits = $this->db->fetchAll("
            SELECT sv.*, l.name as lead_name
            FROM site_visits sv
            LEFT JOIN leads l ON sv.lead_id = l.id
            WHERE sv.assigned_to = ? AND sv.status = 'scheduled' AND sv.visit_date = CURDATE()
            LIMIT 3
        ", [$associateId]);

        foreach ($visits as $v) {
            $actions[] = [
                'type' => 'site_visit',
                'priority' => 'medium',
                'title' => 'Site Visit: ' . ($v['lead_name'] ?? 'Customer'),
                'description' => 'Today at ' . ($v['scheduled_time'] ?? ''),
                'icon' => 'fas fa-map-marker-alt',
                'action_url' => "/associate/site-visits",
                'color' => 'info',
            ];
        }

        // 4. New leads assigned today
        $newLeads = $this->db->fetchAll("
            SELECT id, name, phone, source
            FROM leads
            WHERE assigned_to = ? AND DATE(created_at) = CURDATE() AND deleted_at IS NULL
            LIMIT 3
        ", [$associateId]);

        foreach ($newLeads as $l) {
            $actions[] = [
                'type' => 'new_lead',
                'priority' => 'medium',
                'title' => 'New Lead: ' . $l['name'],
                'description' => 'Source: ' . ($l['source'] ?? 'unknown') . ' | ' . ($l['phone'] ?? ''),
                'icon' => 'fas fa-user-plus',
                'action_url' => "/associate/leads/{$l['id']}",
                'color' => 'success',
            ];
        }

        return $actions;
    }

    /**
     * Get deal predictions for associate's pipeline
     */
    private function getDealPredictions(int $associateId): array
    {
        $bookings = $this->db->fetchAll("
            SELECT pb.id, pb.status, pb.total_plot_value as total_amount, pb.booking_amount as paid_amount, pb.lead_id,
                   l.name as lead_name
            FROM plot_bookings pb
            LEFT JOIN leads l ON pb.lead_id = l.id
            WHERE pb.associate_id = ? AND pb.status NOT IN ('cancelled', 'registration_done')
            ORDER BY pb.created_at DESC
            LIMIT 10
        ", [$associateId]);

        $predictions = [];
        foreach ($bookings as $booking) {
            $probResult = $this->dealProbabilityService->calculateProbability($booking['id']);
            if ($probResult['success']) {
                $predictions[] = [
                    'booking_id' => $booking['id'],
                    'lead_name' => $booking['lead_name'] ?? 'Unknown',
                    'status' => $booking['status'],
                    'total_amount' => $booking['total_amount'],
                    'probability' => $probResult['probability'],
                    'tier' => $probResult['tier'],
                    'risk_factors' => $probResult['risk_factors'],
                    'recommendations' => $probResult['recommendations'],
                ];
            }
        }

        return $predictions;
    }

    /**
     * Get performance score
     */
    private function getPerformanceScore(int $associateId): array
    {
        $stats = $this->db->fetch("
            SELECT 
                COUNT(DISTINCT l.id) as total_leads,
                SUM(CASE WHEN l.status IN ('closed_won','registration_done','deed_upload') THEN 1 ELSE 0 END) as closed_deals,
                (SELECT COUNT(*) FROM plot_bookings WHERE associate_id = ? AND status NOT IN ('cancelled')) as total_bookings,
                (SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ?) as total_commission
            FROM leads l
            WHERE (l.assigned_to = ? OR l.created_by = ?) AND l.deleted_at IS NULL
        ", [$associateId, $associateId, $associateId, $associateId]);

        $totalLeads = $stats['total_leads'] ?? 0;
        $closedDeals = $stats['closed_deals'] ?? 0;
        $totalBookings = $stats['total_bookings'] ?? 0;
        $totalCommission = $stats['total_commission'] ?? 0;

        $conversionRate = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;
        $bookingRate = $totalLeads > 0 ? round(($totalBookings / $totalLeads) * 100, 1) : 0;

        // Performance score (0-100)
        $score = min(100, round(
            ($conversionRate * 0.4) +
            ($bookingRate * 0.3) +
            (min(100, $totalCommission / 10000) * 0.3)
        ));

        return [
            'score' => $score,
            'total_leads' => $totalLeads,
            'closed_deals' => $closedDeals,
            'total_bookings' => $totalBookings,
            'total_commission' => $totalCommission,
            'conversion_rate' => $conversionRate,
            'booking_rate' => $bookingRate,
        ];
    }

    /**
     * Get AI insights for associate
     */
    private function getAIInsights(int $associateId): array
    {
        $insights = [];

        // Conversion trend
        $thisWeek = $this->db->fetchColumn("
            SELECT COUNT(*) FROM leads 
            WHERE (assigned_to = ? OR created_by = ?) 
            AND status IN ('closed_won','registration_done','deed_upload')
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ", [$associateId, $associateId]) ?? 0;

        $lastWeek = $this->db->fetchColumn("
            SELECT COUNT(*) FROM leads 
            WHERE (assigned_to = ? OR created_by = ?) 
            AND status IN ('closed_won','registration_done','deed_upload')
            AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 14 DAY) AND DATE_SUB(NOW(), INTERVAL 7 DAY)
        ", [$associateId, $associateId]) ?? 0;

        if ($thisWeek > $lastWeek) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Conversion Up!',
                'message' => "You converted {$thisWeek} leads this week (vs {$lastWeek} last week). Keep it up!",
                'icon' => 'fas fa-trophy',
                'color' => 'success',
            ];
        } elseif ($thisWeek < $lastWeek && $lastWeek > 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Conversion Down',
                'message' => "You converted {$thisWeek} leads this week (vs {$lastWeek} last week). Review your approach.",
                'icon' => 'fas fa-chart-line',
                'color' => 'warning',
            ];
        }

        // Best performing source
        $bestSource = $this->db->fetch("
            SELECT source, COUNT(*) as count
            FROM leads
            WHERE (assigned_to = ? OR created_by = ?) AND status IN ('closed_won','registration_done','deed_upload')
            GROUP BY source
            ORDER BY count DESC
            LIMIT 1
        ", [$associateId, $associateId]);

        if ($bestSource) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Best Lead Source',
                'message' => "'{$bestSource['source']}' is your best performing source with {$bestSource['count']} conversions.",
                'icon' => 'fas fa-star',
                'color' => 'info',
            ];
        }

        // Average response time
        $avgResponse = $this->db->fetchColumn("
            SELECT AVG(TIMESTAMPDIFF(HOUR, l.created_at, la.created_at))
            FROM leads l
            JOIN lead_activities la ON la.lead_id = l.id
            WHERE (l.assigned_to = ? OR l.created_by = ?) 
            AND la.activity_type = 'call' AND la.created_at = (
                SELECT MIN(created_at) FROM lead_activities 
                WHERE lead_id = l.id AND activity_type = 'call'
            )
        ", [$associateId, $associateId]) ?? 0;

        if ($avgResponse > 24) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Slow Response Time',
                'message' => "Your average response time is " . round($avgResponse, 1) . " hours. Try to respond within 4 hours.",
                'icon' => 'fas fa-clock',
                'color' => 'warning',
            ];
        } elseif ($avgResponse > 0 && $avgResponse <= 4) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Fast Response!',
                'message' => "Your average response time is " . round($avgResponse, 1) . " hours. Excellent!",
                'icon' => 'fas fa-bolt',
                'color' => 'success',
            ];
        }

        return $insights;
    }

    /**
     * Get property recommendations for associate
     */
    private function getRecommendations(int $associateId): array
    {
        // Get associate's active leads
        $leads = $this->db->fetchAll("
            SELECT id FROM leads 
            WHERE (assigned_to = ? OR created_by = ?) 
            AND deleted_at IS NULL 
            AND status NOT IN ('closed_won', 'closed_lost', 'nurture')
            LIMIT 5
        ", [$associateId, $associateId]);

        $recommendations = [];
        foreach ($leads as $lead) {
            $result = $this->recommendationService->getRecommendations([
                'lead_id' => $lead['id'],
                'limit' => 3,
            ]);
            if ($result['success'] && !empty($result['recommendations'])) {
                $recommendations = array_merge($recommendations, $result['recommendations']);
            }
        }

        // Deduplicate
        $seen = [];
        $unique = [];
        foreach ($recommendations as $rec) {
            if (isset($seen[$rec['id']])) continue;
            $seen[$rec['id']] = true;
            $unique[] = $rec;
        }

        return array_slice($unique, 0, 10);
    }
}