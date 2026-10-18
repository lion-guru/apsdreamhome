<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * LeadWorkflowService — per-user lead automation rules (CRM v1).
 *
 * Distinct from WorkflowEngineService (multi-step APPROVAL flows for
 * bookings/commissions) and AI\WorkflowEngine (parallel AI lane):
 * this evaluates simple WHEN-trigger/THEN-action rules owned by one user
 * over their own leads. Callers MUST wrap handle() in try/catch —
 * automation never breaks the main write path.
 *
 * v1 triggers: lead_created, status_changed, note_added.
 * v1 actions: add_note {text}, set_status {status}, create_followup {days, type}.
 */
class LeadWorkflowService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Run all active rules of $userId matching $trigger against $leadId.
     * Returns number of actions applied. Throws on DB errors (caller catches).
     */
    public function handle($trigger, $leadId, $userId, array $context = [])
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $trigger];
        if ($tid > 1) $params[] = $tid;
        $rules = $this->db->fetchAll(
            "SELECT * FROM workflow_rules WHERE user_id = ? AND `trigger` = ? AND is_active = 1{$tidSql} ORDER BY id ASC",
            $params
        ) ?: [];
        if (empty($rules)) return 0;

        $lead = $this->ownedLead($leadId, $userId, $tid, $tidSql);
        if (!$lead) return 0;

        $done = 0;
        foreach ($rules as $rule) {
            if (!$this->conditionsMatch($rule, $lead, $context)) continue;
            if ($this->runAction($rule, $lead, $userId, $tid, $context)) $done++;
            // Re-read lead (an action may have changed status for the next rule).
            $lead = $this->ownedLead($leadId, $userId, $tid, $tidSql);
            if (!$lead) break;
        }
        return $done;
    }

    private function ownedLead($leadId, $userId, $tid, $tidSql)
    {
        $params = [$leadId, $userId, $userId];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne(
            "SELECT * FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} LIMIT 1",
            $params
        );
    }

    private function conditionsMatch($rule, $lead, $context)
    {
        $conds = json_decode($rule['conditions'] ?? '', true);
        if (!is_array($conds) || empty($conds)) return true;
        if (!empty($conds['status']) && !in_array($lead['status'] ?? '', (array)$conds['status'], true)) return false;
        if (!empty($conds['source']) && stripos((string)($lead['source'] ?? ''), (string)$conds['source']) === false) return false;
        if (!empty($conds['new_status']) && ($context['new_status'] ?? null) !== $conds['new_status']) return false;
        return true;
    }

    private function runAction($rule, $lead, $userId, $tid, $context)
    {
        $params = json_decode($rule['action_params'] ?? '', true) ?: [];
        switch ($rule['action']) {
            case 'add_note':
                $text = trim((string)($params['text'] ?? ''));
                if ($text === '') return false;
                $this->db->insert('lead_notes', [
                    'lead_id' => $lead['id'],
                    'created_by' => $userId,
                    'note' => mb_substr($text, 0, 1000),
                    'content' => mb_substr($text, 0, 1000),
                    'created_at' => date('Y-m-d H:i:s'),
                    'tenant_id' => $tid,
                ]);
                return true;
            case 'set_status':
                $valid = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
                $to = $params['status'] ?? '';
                if (!in_array($to, $valid, true)) return false;
                $this->db->execute("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?", [$to, $lead['id']]);
                $this->db->insert('lead_activities', [
                    'lead_id' => $lead['id'],
                    'activity_type' => 'status_change',
                    'description' => 'Workflow: ' . ($rule['name'] ?? 'rule'),
                    'old_value' => $lead['status'] ?? '',
                    'new_value' => $to,
                    'created_by' => $userId,
                    'tenant_id' => $tid,
                ]);
                return true;
            case 'create_followup':
                $days = max(1, min(90, (int)($params['days'] ?? 3)));
                $type = preg_replace('/[^a-z_]/', '', (string)($params['type'] ?? 'call')) ?: 'call';
                $this->db->insert('followups', [
                    'lead_id' => $lead['id'],
                    'assigned_to' => $userId,
                    'followup_type' => mb_substr($type, 0, 50),
                    'followup_date' => date('Y-m-d', strtotime("+$days days")),
                    'notes' => 'Workflow: ' . ($rule['name'] ?? 'rule'),
                    'status' => 'pending',
                    'tenant_id' => $tid,
                ]);
                return true;
            default:
                return false;
        }
    }
}
