<?php
// Admin AI Morning Brief — Daily Sales Director morning dashboard.
// Surfaces the 10 highest-value actions for today:
//   - Top 5 leads to call today (highest lead_score, contact today)
//   - Site visits today (and overdue confirmation)
//   - Closed_won leads awaiting booking
//   - Cold leads > 7 days silent ready for re-engagement
// Read-only queries; all requires admin role.

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\AI\DemandForecastService;
use App\Services\AI\LeadChurnPredictionService;
use App\Services\AI\SmartLeadAssignmentService;

class AiMorningBriefController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $tid = 1;
        $today = date('Y-m-d');

        // ── 1. Top 5 hot new leads (highest lead_score) ──
        $topLeads = [];
        try {
            $topLeads = $this->db->fetchAll(
                "SELECT id, name, phone, source, status, lead_score, assigned_to, DATE_FORMAT(COALESCE(first_response_at, created_at), '%d %b') AS last_contact
                 FROM leads
                 WHERE status NOT IN ('closed_won','closed_lost','converted')
                   AND deleted_at IS NULL
                 ORDER BY lead_score DESC, id DESC
                 LIMIT 5"
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief topLeads: ' . $e->getMessage()); }

        // ── 2. Site visits today ──
        $todayVisits = [];
        try {
            $todayVisits = $this->db->fetchAll(
                "SELECT sv.id, sv.colony_id, sv.visitor_name, sv.visitor_phone, sv.visit_time, sv.status, sv.assigned_to,
                        COALESCE(c.name, CONCAT('Colony #', IFNULL(sv.colony_id, '?'))) AS colony_name,
                        u.name AS owner_name
                 FROM site_visits sv
                 LEFT JOIN colonies c ON c.id = sv.colony_id
                 LEFT JOIN users u ON u.id = sv.assigned_to
                 WHERE sv.visit_date = ?
                 ORDER BY sv.visit_time ASC",
                [$today]
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief todayVisits: ' . $e->getMessage()); }

        // ── 3. Closed_won leads awaiting booking (lost in pipeline) ──
        $closedNotBooked = [];
        try {
            $closedNotBooked = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.source, l.lead_score, l.created_at,
                        u.name AS owner_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_to
                 LEFT JOIN plot_bookings pb ON pb.lead_id = l.id
                 WHERE l.status = 'closed_won'
                   AND l.deleted_at IS NULL
                   AND (pb.id IS NULL OR pb.id = 0)
                 ORDER BY l.lead_score DESC, l.id DESC
                 LIMIT 6"
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief closedNotBooked: ' . $e->getMessage()); }

        // ── 4. Cold leads ready for re-engagement (score < 30) ──
        $coldReadyToRevive = [];
        try {
            $coldReadyToRevive = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.source, l.lead_score, l.created_at
                 FROM leads l
                 WHERE l.status NOT IN ('closed_won','closed_lost','converted')
                   AND l.lead_score < 30
                   AND l.source != 'walk_in'
                   AND l.deleted_at IS NULL
                   AND l.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY l.lead_score DESC, l.id DESC
                 LIMIT 4"
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief coldReadyToRevive: ' . $e->getMessage()); }

        // ── 5. Negotiation stage — likely closing this week ──
        $inNegotiation = [];
        try {
            $inNegotiation = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.source, l.lead_score
                 FROM leads l
                 WHERE l.status = 'negotiation'
                   AND l.deleted_at IS NULL
                 ORDER BY l.lead_score DESC, l.id DESC
                 LIMIT 5"
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief inNegotiation: ' . $e->getMessage()); }

        // ── KPI summary ──
        $stats = [
            'top_leads'     => count($topLeads),
            'visits_today'  => count($todayVisits),
            'lost_to_booking' => count($closedNotBooked),
            'revivable'     => count($coldReadyToRevive),
            'in_negotiation' => count($inNegotiation),
        ];

        // ── 6. Booking status breakdown (real money) ──
        $bookingBreakdown = [];
        try {
            $bookingBreakdown = $this->db->fetchAll(
                "SELECT status, COUNT(*) AS cnt, COALESCE(SUM(agreement_value), 0) AS total
                 FROM plot_bookings
                 WHERE status NOT IN ('cancelled','defaulted','transferred')
                 GROUP BY status
                 ORDER BY cnt DESC"
            );
        } catch (\Throwable $e) { error_log('AiMorningBrief bookingBreakdown: ' . $e->getMessage()); }

        // ── 7. AI insight panels (advisory, read-only compute; churn table is a cache) ──
        $demandForecast = [];
        try {
            $demandForecast = (new DemandForecastService($this->db))->colonyRows();
        } catch (\Throwable $e) { error_log('AiMorningBrief demandForecast: ' . $e->getMessage()); }

        $churnStats = null;
        $highRiskLeads = [];
        try {
            $churnSvc = new LeadChurnPredictionService();
            $churnStats = $churnSvc->getStats();
            $highRiskLeads = $churnSvc->getHighRiskLeads(6);
        } catch (\Throwable $e) { error_log('AiMorningBrief churn: ' . $e->getMessage()); }

        // ── 8. Unassigned leads — smart routing advisory (zero writes on GET) ──
        $unassignedCount = 0;
        $unassignedOldest = null;
        try {
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) AS c, MIN(created_at) AS oldest
                 FROM leads
                 WHERE assigned_to IS NULL
                   AND deleted_at IS NULL
                   AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled')"
            );
            $unassignedCount = (int)($row['c'] ?? 0);
            $unassignedOldest = $row['oldest'] ?? null;
        } catch (\Throwable $e) { error_log('AiMorningBrief unassignedCount: ' . $e->getMessage()); }

        $unassignedPreview = [];
        try {
            $rows = $this->db->fetchAll(
                "SELECT id, name, phone, source, status, lead_score, created_at
                 FROM leads
                 WHERE assigned_to IS NULL
                   AND deleted_at IS NULL
                   AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled')
                 ORDER BY lead_score DESC, id DESC
                 LIMIT 5"
            );
            if (!empty($rows)) {
                $smartSvc = new SmartLeadAssignmentService($this->db);
                foreach ($rows as $r) {
                    $rec = $smartSvc->recommendForLead((int)$r['id'], 1);
                    $best = (($rec['success'] ?? false) && !empty($rec['recommendations'])) ? $rec['recommendations'][0] : null;
                    $unassignedPreview[] = [
                        'lead_id' => (int) $r['id'],
                        'name' => $r['name'] ?? '',
                        'phone' => $r['phone'] ?? '',
                        'source' => $r['source'] ?? '',
                        'lead_score' => (int) ($r['lead_score'] ?? 0),
                        'created_at' => $r['created_at'] ?? null,
                        'best_fit_name' => $best['name'] ?? null,
                        'best_fit_role' => $best['role'] ?? null,
                        'best_fit_score' => isset($best['total_score']) ? round((float)$best['total_score'], 1) : null,
                    ];
                }
            }
        } catch (\Throwable $e) { error_log('AiMorningBrief unassignedPreview: ' . $e->getMessage()); }

        $data = [
            'page_title' => 'AI Morning Brief - Sales Director',
            'page_description' => 'Today\'s 3-minute brief: who to call, what to chase, what\'s at risk.',
            'top_leads' => $topLeads,
            'visits_today' => $todayVisits,
            'closed_not_booked' => $closedNotBooked,
            'cold_revivable' => $coldReadyToRevive,
            'in_negotiation' => $inNegotiation,
            'booking_breakdown' => $bookingBreakdown,
            'stats' => $stats,
            'demand_forecast' => $demandForecast,
            'churn_stats' => $churnStats,
            'high_risk_leads' => $highRiskLeads,
            'unassigned_count' => $unassignedCount,
            'unassigned_oldest' => $unassignedOldest,
            'unassigned_preview' => $unassignedPreview,
        ];

        $this->render('admin/ai_morning_brief', $data);
    }

    /**
     * POST /admin/ai-morning-brief/run-smart-assignment
     * Bounded smart re-route of unassigned open leads (max 50 per click).
     * Admin-triggered ONLY — never runs on page load. Writes leads.assigned_to
     * + crm_assignments (reason smart_assign). CSRF-gated.
     */
    public function runSmartAssignment()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $assigned = 0;
            $failed = 0;
            $total = 0;
            $smartSvc = new SmartLeadAssignmentService($this->db);
            $results = $smartSvc->autoAssignBatch(50);
            $assigned = (int)($results['assigned'] ?? 0);
            $failed = (int)($results['failed'] ?? 0);
            $total = (int)($results['total'] ?? 0);
            $this->setFlash('success', "Smart re-route complete: {$assigned} assigned, {$failed} failed, of {$total} unassigned scanned.");
        } catch (\Throwable $e) {
            error_log('AiMorningBrief runSmartAssignment: ' . $e->getMessage());
            $this->setFlash('error', 'Smart re-route failed — see error log. No partial writes beyond what the batch already committed.');
        }

        $this->redirect('/admin/ai-morning-brief');
    }

    /**
     * POST /admin/ai-morning-brief/run-churn-analysis
     * One-click refresh of the churn-risk cache (lead_churn_predictions).
     * Bounded (300 leads per run, upsert-safe), admin-only, CSRF-gated.
     * Nothing money-related is touched; the table is a recomputable cache.
     */
    public function runChurnAnalysis()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $churnSvc = new LeadChurnPredictionService();
            $results = $churnSvc->predictAll(300);
            $predicted = (int)($results['predicted'] ?? 0);
            $skipped = (int)($results['skipped'] ?? 0);
            $this->setFlash('success', "Churn analysis complete: {$predicted} leads scored, {$skipped} skipped.");
        } catch (\Throwable $e) {
            error_log('AiMorningBrief runChurnAnalysis: ' . $e->getMessage());
            $this->setFlash('error', 'Churn analysis failed - see error log. No partial data was saved.');
        }

        $this->redirect('/admin/ai-morning-brief');
    }
}
