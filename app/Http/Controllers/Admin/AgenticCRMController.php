<?php
/**
 * Agentic CRM AI — Intelligent automated CRM tasks
 * Like a human sales manager: follow-ups, scoring, routing, insights
 */
namespace App\Http\Controllers\Admin;

use App\Core\Database;
use App\Services\CRMService;

class AgenticCRMController extends AdminController
{
    /**
     * Dashboard — Agentic AI status and actions
     */
    public function index()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $service = new CRMService();

        // Get agent AI stats
        $stats = [
            'auto_followups' => $this->getAutoFollowupCount($db),
            'score_adjustments' => $this->getScoreAdjustmentCount($db),
            'auto_assignments' => $this->getAutoAssignmentCount($db),
            'insights_generated' => $this->getInsightCount($db),
            'overdue_leads' => $service->getOverdueTasks(),
            'hot_leads' => $this->getHotLeads($db),
            'cold_leads' => $this->getColdLeads($db),
            'dormant_leads' => $this->getDormantLeads($db),
        ];

        // Recent AI actions
        $recentActions = $this->getRecentActions($db);

        return $this->render('admin/crm/agentic/dashboard', [
            'stats' => $stats,
            'recent_actions' => $recentActions,
            'page_title' => 'Agentic CRM AI',
        ]);
    }

    /**
     * Run auto-followup agent — finds leads that need follow-up and sends reminders
     */
    public function runAutoFollowup()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $service = new CRMService();

        $results = ['processed' => 0, 'reminders_sent' => 0, 'details' => []];

        // Find leads with overdue or due-today follow-ups
        $overdue = $db->query(
            "SELECT l.id, l.name, l.phone, l.status, l.assigned_to, l.last_activity_date,
                    u.name as assignee_name
             FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.deleted_at IS NULL
               AND l.status NOT IN ('converted','closed','dead','won')
               AND (l.last_activity_date IS NULL OR l.last_activity_date < DATE_SUB(NOW(), INTERVAL 3 DAY))
             ORDER BY l.last_activity_date ASC
             LIMIT 50"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($overdue as $lead) {
            // Create a follow-up task
            $service->createTask([
                'lead_id' => $lead['id'],
                'assigned_to' => $lead['assigned_to'] ?? 1,
                'task_type' => 'follow_up',
                'title' => "Auto follow-up: {$lead['name']} — last contact " . ($lead['last_activity_date'] ? date('d M', strtotime($lead['last_activity_date'])) : 'never'),
                'description' => "This lead hasn't been contacted in 3+ days. Please reach out.",
                'priority' => 'high',
                'due_date' => date('Y-m-d'),
            ]);

            // Log the AI action
            $this->logAIAction($db, 'auto_followup', $lead['id'], $lead['assigned_to'], "Auto-followup task created for {$lead['name']}");

            $results['processed']++;
            $results['details'][] = "Created follow-up for: {$lead['name']} ({$lead['status']})";
        }

        $this->setFlash('success', "Auto-followup: {$results['processed']} tasks created");
        return $this->redirect('/admin/crm/agentic');
    }

    /**
     * Run lead scoring agent — recalculate scores for all leads
     */
    public function runScoreRecalculation()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $service = new CRMService();

        $leads = $db->query("SELECT id, name FROM leads WHERE deleted_at IS NULL ORDER BY updated_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $adjusted = 0;
        foreach ($leads as $lead) {
            $oldScore = (int)$db->query("SELECT lead_score FROM leads WHERE id = {$lead['id']}")->fetchColumn();
            $newScore = $service->recalculateScore($lead['id']);
            if ($oldScore !== $newScore) {
                $adjusted++;
                $this->logAIAction($db, 'score_adjustment', $lead['id'], null, "Score {$oldScore} → {$newScore} for {$lead['name']}");
            }
        }

        $this->setFlash('success', "Score recalculation: {$adjusted} leads adjusted out of " . count($leads));
        return $this->redirect('/admin/crm/agentic');
    }

    /**
     * Run auto-assignment agent — assigns unassigned leads
     */
    public function runAutoAssignment()
    {
        $this->requireAdmin();
        $result = (new CRMService())->autoAssignLeads('round_robin');
        $count = $result['assigned'] ?? 0;
        $this->logAIAction(Database::getInstance()->getConnection(), 'auto_assignment', null, null, "Auto-assigned {$count} leads via round-robin");
        $this->setFlash('success', "Auto-assignment: {$count} leads assigned");
        return $this->redirect('/admin/crm/agentic');
    }

    /**
     * Generate insights — AI analysis of pipeline health
     */
    public function generateInsights()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $service = new CRMService();

        $insights = [];

        // Pipeline health
        $pipeline = $db->query("SELECT status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $total = array_sum(array_column($pipeline, 'cnt'));
        $newLeads = 0;
        $stuckLeads = 0;
        foreach ($pipeline as $p) {
            if ($p['status'] === 'new') $newLeads = (int)$p['cnt'];
            if (in_array($p['status'], ['contacted', 'qualified'])) $stuckLeads += (int)$p['cnt'];
        }

        // Conversion analysis
        $won = $db->query("SELECT COUNT(*) FROM leads WHERE status='won' AND deleted_at IS NULL")->fetchColumn();
        $convRate = $total > 0 ? round(($won / $total) * 100, 1) : 0;

        if ($newLeads > 50) {
            $insights[] = ['type' => 'warning', 'icon' => 'fa-exclamation-triangle', 'title' => 'High New Lead Volume', 'detail' => "{$newLeads} new leads need initial contact. Consider auto-assignment."];
        }
        if ($stuckLeads > 100) {
            $insights[] = ['type' => 'info', 'icon' => 'fa-info-circle', 'title' => 'Leads Stuck in Pipeline', 'detail' => "{$stuckLeads} leads in contacted/qualified stage. Run auto-followup agent."];
        }
        if ($convRate < 5) {
            $insights[] = ['type' => 'danger', 'icon' => 'fa-ban', 'title' => 'Low Conversion Rate', 'detail' => "{$convRate}% conversion is below industry average. Review lead quality."];
        } elseif ($convRate > 15) {
            $insights[] = ['type' => 'success', 'icon' => 'fa-check-circle', 'title' => 'Excellent Conversion', 'detail' => "{$convRate}% conversion rate! Well above industry average."];
        }

        // Score distribution
        $hotCount = (int)$db->query("SELECT COUNT(*) FROM leads WHERE lead_score >= 70 AND deleted_at IS NULL")->fetchColumn();
        $coldCount = (int)$db->query("SELECT COUNT(*) FROM leads WHERE lead_score < 30 AND deleted_at IS NULL")->fetchColumn();

        if ($hotCount > 0) {
            $insights[] = ['type' => 'success', 'icon' => 'fa-fire', 'title' => 'Hot Leads Ready', 'detail' => "{$hotCount} leads with score ≥70 are ready for conversion."];
        }
        if ($coldCount > 500) {
            $insights[] = ['type' => 'warning', 'icon' => 'fa-snowflake', 'title' => 'Cold Lead Cleanup', 'detail' => "{$coldCount} leads with score <30. Consider archiving or re-engagement."];
        }

        // Save insights
        foreach ($insights as $insight) {
            $this->logAIAction($db, 'insight', null, null, $insight['title'] . ': ' . $insight['detail']);
        }

        $this->setFlash('success', "Generated " . count($insights) . " insights");
        return $this->redirect('/admin/crm/agentic');
    }

    /**
     * Run all agents at once
     */
    public function runAll()
    {
        $this->requireAdmin();
        $this->runAutoFollowup();
        $this->runScoreRecalculation();
        $this->runAutoAssignment();
        $this->generateInsights();
        $this->setFlash('success', 'All agents completed successfully');
        return $this->redirect('/admin/crm/agentic');
    }

    // ─────── Helper methods ────────────────────────────────────────────

    private function getAutoFollowupCount($db): int
    {
        return (int)$db->query("SELECT COUNT(*) FROM crm_tasks WHERE task_type='follow_up' AND DATE(created_at)=CURDATE()")->fetchColumn();
    }

    private function getScoreAdjustmentCount($db): int
    {
        return (int)$db->query("SELECT COUNT(*) FROM lead_scores WHERE DATE(calculated_at)=CURDATE()")->fetchColumn();
    }

    private function getAutoAssignmentCount($db): int
    {
        return (int)$db->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NOT NULL AND DATE(updated_at)=CURDATE() AND assigned_to != 0")->fetchColumn();
    }

    private function getInsightCount($db): int
    {
        try {
            return (int)$db->query("SELECT COUNT(*) FROM agent_insights WHERE DATE(created_at)=CURDATE()")->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getHotLeads($db): array
    {
        return $db->query("SELECT l.id, l.name, l.phone, l.lead_score, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE l.lead_score >= 70 AND l.deleted_at IS NULL ORDER BY l.lead_score DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function getColdLeads($db): array
    {
        return $db->query("SELECT COUNT(*) as cnt FROM leads WHERE lead_score < 30 AND deleted_at IS NULL")->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getDormantLeads($db): array
    {
        return $db->query("SELECT l.id, l.name, l.phone, l.last_activity_date, DATEDIFF(NOW(), l.last_activity_date) as days_inactive FROM leads l WHERE l.deleted_at IS NULL AND l.status NOT IN ('converted','closed','dead') AND (l.last_activity_date IS NULL OR l.last_activity_date < DATE_SUB(NOW(), INTERVAL 7 DAY)) ORDER BY l.last_activity_date ASC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function getRecentActions($db): array
    {
        try {
            return $db->query("SELECT * FROM agent_task_logs WHERE DATE(created_at)=CURDATE() ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function logAIAction($db, string $action, ?int $leadId, ?int $userId, string $details)
    {
        try {
            $db->query(
                "INSERT INTO agent_task_logs (agent_type, action_type, lead_id, user_id, details, status, created_at) VALUES ('crm_ai', ?, ?, ?, ?, 'completed', NOW())",
                [$action, $leadId, $userId, $details]
            );
        } catch (\Throwable $e) {
            error_log('AgenticCRMController::logAIAction error: ' . $e->getMessage());
        }
    }
}