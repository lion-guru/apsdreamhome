<?php
/**
 * SmartLeadQualifierAgent — Auto-qualifies leads 24/7
 * 
 * For every new lead:
 * 1. Analyzes message intent (Hindi/English)
 * 2. Scores based on budget, urgency, engagement
 * 3. Assigns qualification: hot/warm/cold
 * 4. Suggests next action
 * 5. Auto-responds via WhatsApp/chat if configured
 * 6. Escalates hot leads to human instantly
 */

namespace App\Services\AI\Agents;

use App\Core\Database\Database;
use App\Services\AI\AIGateway;
use App\Services\AI\IntentDetector;
use App\Services\AI\LeadScorer;

class SmartLeadQualifierAgent
{
    private $db;
    private $gateway;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gateway = AIGateway::getInstance();
    }

    /**
     * Process a single lead — qualify, score, act
     */
    public function qualifyLead(int $leadId): array
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return ['error' => 'Lead not found'];

        // 1. AI-powered qualification via gateway
        $qualResult = $this->gateway->process('qualify_lead', [
            'name' => $lead['name'],
            'phone' => $lead['phone'],
            'email' => $lead['email'],
            'message' => $lead['notes'] ?? '',
            'budget' => $lead['budget'],
            'source' => $lead['source'],
        ], ['user_id' => $lead['assigned_to']]);

        $qualification = $qualResult['result']['qualification'] ?? 'cold';
        $score = $qualResult['result']['score'] ?? 0;
        $nextAction = $qualResult['result']['next_action'] ?? 'Follow up';

        // 2. Lead scoring
        $scoreResult = $this->gateway->process('score_lead', ['lead_id' => $leadId]);
        $aiScore = $scoreResult['result']['score']['score'] ?? $score;
        $grade = $scoreResult['result']['score']['grade'] ?? 'D';

        // 3. Update lead
        $this->db->getConnection()->prepare(
            "UPDATE leads SET lead_score = ?, lead_category = ?, priority = ?, updated_at = NOW() WHERE id = ?"
        )->execute([
            $aiScore,
            $qualification,
            $qualification === 'hot' ? 'high' : ($qualification === 'warm' ? 'medium' : 'low'),
            $leadId
        ]);

        // 4. Auto-assign if hot and unassigned
        if ($qualification === 'hot' && empty($lead['assigned_to'])) {
            $this->autoAssignHotLead($leadId);
        }

        // 5. Create follow-up task
        $taskDelay = $qualification === 'hot' ? '1 hour' : ($qualification === 'warm' ? '24 hours' : '7 days');
        $this->createFollowUpTask($leadId, $qualification, $taskDelay);

        // 6. Log the qualification
        $this->logQualification($leadId, $qualification, $aiScore, $nextAction, $qualResult['engine'] ?? 'unknown');

        // 7. Escalate hot leads
        if ($qualification === 'hot') {
            $this->escalateHotLead($leadId, $lead, $aiScore);
        }

        return [
            'lead_id' => $leadId,
            'qualification' => $qualification,
            'score' => $aiScore,
            'grade' => $grade,
            'next_action' => $nextAction,
            'engine' => $qualResult['engine'] ?? 'unknown',
            'escalated' => $qualification === 'hot',
        ];
    }

    /**
     * Batch process — qualify all unprocessed leads
     */
    public function processBatch(int $limit = 50): array
    {
        $leads = $this->db->fetchAll(
            "SELECT id FROM leads WHERE deleted_at IS NULL AND (lead_score IS NULL OR lead_score = 0) ORDER BY created_at DESC LIMIT ?",
            [$limit]
        ) ?: [];

        $results = ['processed' => 0, 'hot' => 0, 'warm' => 0, 'cold' => 0, 'errors' => 0];
        foreach ($leads as $lead) {
            $result = $this->qualifyLead((int)$lead['id']);
            if (isset($result['error'])) {
                $results['errors']++;
            } else {
                $results['processed']++;
                $results[$result['qualification']]++;
            }
        }
        return $results;
    }

    /**
     * Auto-respond to lead via chat/WhatsApp
     */
    public function autoRespond(int $leadId): ?string
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return null;

        $chatResult = $this->gateway->process('chat', [
            'message' => $lead['notes'] ?? 'Hello, I am interested in your properties',
        ], [
            'user_id' => $lead['id'],
            'user_role' => 'lead',
            'history' => [],
        ]);

        $response = $chatResult['result']['text'] ?? $chatResult['result']['parsed']['text'] ?? null;
        if ($response) {
            $this->db->getConnection()->prepare(
                "INSERT INTO crm_interactions (lead_id, interaction_type, direction, content, created_at) VALUES (?, 'auto_reply', 'outbound', ?, NOW())"
            )->execute([$leadId, $response]);
        }
        return $response;
    }

    // ─────── Helpers ─────────────────────────────────────────────────

    private function getLead(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM leads WHERE id = ? AND deleted_at IS NULL", [$id]) ?: null;
    }

    private function autoAssignHotLead(int $leadId): void
    {
        $bestAgent = $this->db->fetch(
            "SELECT u.id FROM users u LEFT JOIN leads l ON l.assigned_to = u.id AND l.deleted_at IS NULL
             WHERE u.role IN ('associate','agent','employee') AND u.is_active = 1
             GROUP BY u.id ORDER BY COUNT(l.id) ASC LIMIT 1"
        );
        if ($bestAgent) {
            $this->db->getConnection()->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$bestAgent['id'], $leadId]);
        }
    }

    private function createFollowUpTask(int $leadId, string $qualification, string $delay): void
    {
        $priority = $qualification === 'hot' ? 'urgent' : ($qualification === 'warm' ? 'high' : 'medium');
        $lead = $this->getLead($leadId);

        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO crm_tasks (lead_id, assigned_to, task_type, title, priority, due_date, status, created_at)
                 VALUES (?, ?, 'follow_up', ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 'pending', NOW())"
            )->execute([
                $leadId,
                $lead['assigned_to'] ?? 1,
                "Follow up: {$lead['name']} ($qualification lead)",
                $priority,
            ]);
        } catch (\Throwable $e) { /* table may not have all columns */ }
    }

    private function logQualification(int $leadId, string $qualification, int $score, string $action, string $engine): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO agent_task_logs (agent_type, action_type, lead_id, details, status, created_at)
                 VALUES ('smart_qualifier', 'qualify', ?, ?, 'completed', NOW())"
            )->execute([$leadId, "Qualified as $qualification (score: $score) — $action [engine: $engine]"]);
        } catch (\Throwable $e) { /* non-critical */ }
    }

    private function escalateHotLead(int $leadId, array $lead, int $score): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO agent_escalations (agent_type, lead_id, escalation_type, priority, details, status, created_at)
                 VALUES ('smart_qualifier', ?, 'hot_lead', 'urgent', ?, 'pending', NOW())"
            )->execute([
                $leadId,
                "HOT LEAD: {$lead['name']} ({$lead['phone']}) — Score: $score. Immediate action required."
            ]);
        } catch (\Throwable $e) { /* non-critical */ }
    }
}
