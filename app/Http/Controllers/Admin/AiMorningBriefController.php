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

        $data = [
            'page_title' => 'AI Morning Brief — Sales Director',
            'page_description' => 'Today\'s 3-minute brief: who to call, what to chase, what\'s at risk.',
            'top_leads' => $topLeads,
            'visits_today' => $todayVisits,
            'closed_not_booked' => $closedNotBooked,
            'cold_revivable' => $coldReadyToRevive,
            'in_negotiation' => $inNegotiation,
            'booking_breakdown' => $bookingBreakdown,
            'stats' => $stats,
        ];

        $this->render('admin/ai_morning_brief', $data);
    }
}
