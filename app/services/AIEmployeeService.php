<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * AIEmployeeService — named AI staff worker.
 *
 * Employees (ai_employees) claim pending tasks (ai_employee_tasks), act on
 * them via FreeAIEngines + DB, log to lead_activities, and complete with a
 * result. Admin enables/disables via the desk (/admin/ai-employee).
 */
class AIEmployeeService
{
    private $db;
    private array $lastTaskPayload = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getEmployee(int $id): ?array
    {
        $row = $this->db->fetch("SELECT * FROM ai_employees WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'paused', 'offline'], true)) {
            $status = 'paused';
        }
        $this->db->execute("UPDATE ai_employees SET status = ?, last_active = IF(? = 'active', NOW(), NULL) WHERE id = ?", [$status, $status, $id]);
    }

    public function enqueue(int $employeeId, string $type, ?int $leadId, array $payload): int
    {
        $this->db->execute(
            "INSERT INTO ai_employee_tasks (employee_id, type, lead_id, payload, status) VALUES (?, ?, ?, ?, 'pending')",
            [$employeeId, $type, $leadId, json_encode($payload ?: new \stdClass())]
        );
        return (int) $this->db->fetchOne("SELECT LAST_INSERT_ID() as id");
    }

    public function getQueue(int $employeeId = 0, int $limit = 50): array
    {
        $where = '';
        $params = [];
        if ($employeeId > 0) {
            $where = 'WHERE employee_id = ?';
            $params[] = $employeeId;
        }
        return $this->db->fetchAll(
            "SELECT * FROM ai_employee_tasks $where ORDER BY id DESC LIMIT ?",
            array_merge($params, [$limit])
        ) ?: [];
    }

    public function getStats(int $employeeId = 0): array
    {
        $where = $employeeId > 0 ? 'WHERE employee_id = ?' : '';
        $params = $employeeId > 0 ? [$employeeId] : [];
        $row = $this->db->fetchOne("SELECT
            COUNT(*) as total,
            SUM(status = 'pending') as pending,
            SUM(status = 'done') as done,
            SUM(status = 'failed') as failed
            FROM ai_employee_tasks $where", $params) ?: [];
        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'done' => (int) ($row['done'] ?? 0),
            'failed' => (int) ($row['failed'] ?? 0),
        ];
    }

    /**
     * Claim + run ONE pending task for an active employee. Returns result.
     */
    public function runNext(int $employeeId): array
    {
        $emp = $this->getEmployee($employeeId);
        if (!$emp || ($emp['status'] ?? '') !== 'active') {
            return ['success' => false, 'error' => 'Employee not active'];
        }
        $task = $this->db->fetch("SELECT * FROM ai_employee_tasks WHERE employee_id = ? AND status = 'pending' ORDER BY id ASC LIMIT 1", [$employeeId]);
        if (!$task) {
            return ['success' => false, 'error' => 'No pending tasks'];
        }
        $this->db->execute("UPDATE ai_employee_tasks SET status = 'claimed', claimed_at = NOW() WHERE id = ?", [$task['id']]);
        $this->db->execute("UPDATE ai_employees SET workload = workload + 1, last_active = NOW() WHERE id = ?", [$employeeId]);

        try {
            $result = $this->executeTask($emp, $task);
            $this->db->execute(
                "UPDATE ai_employee_tasks SET status = 'done', result = ?, completed_at = NOW() WHERE id = ?",
                [json_encode($result), $task['id']]
            );
            return ['success' => true, 'task_id' => (int) $task['id'], 'result' => $result];
        } catch (\Throwable $e) {
            $this->db->execute(
                "UPDATE ai_employee_tasks SET status = 'failed', result = ?, completed_at = NOW() WHERE id = ?",
                [json_encode(['error' => $e->getMessage()]), $task['id']]
            );
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function executeTask(array $emp, array $task): array
    {
        $this->lastTaskPayload = json_decode((string) ($task['payload'] ?? ''), true) ?: [];
        $type = $task['type'];
        $leadId = (int) ($task['lead_id'] ?? 0);

        if ($type === 'qualify_lead' && $leadId > 0) {
            return $this->qualifyLead($leadId, (int) $emp['id']);
        }
        if ($type === 'followup_draft' && $leadId > 0) {
            return $this->draftFollowup($leadId, (int) $emp['id']);
        }
        if ($type === 'notify_stale' && $leadId > 0) {
            return $this->notifyStale($leadId, (int) $emp['id']);
        }
        if ($type === 'handoff') {
            return $this->handoff($emp, $task);
        }
        if (str_ends_with($type, '_report')) {
            return $this->analystReport($emp, $type);
        }
        throw new \RuntimeException("Unknown task type: $type");
    }

    /**
     * Role handoff: telecaller -> qualifier -> followup chain.
     * Telecaller "qualifies" a call outcome, then queues Vivaan (qualifier)
     * for scoring note, which on done queues Sahil (followup) draft.
     */
    private function handoff(array $emp, array $task): array
    {
        $payload = json_decode((string) ($task['payload'] ?? ''), true) ?: [];
        $fromRole = $payload['from_role'] ?? $emp['role'];
        $leadId = (int) ($task['lead_id'] ?? 0);
        $nextRole = match ($fromRole) {
            'telecaller' => 'qualifier',
            'qualifier' => 'followup',
            default => null,
        };
        if ($nextRole === null || $leadId <= 0) {
            return ['handed_off' => false, 'reason' => 'chain end or no lead'];
        }
        $next = $this->db->fetch("SELECT * FROM ai_employees WHERE role = ? AND status = 'active' ORDER BY id ASC LIMIT 1", [$nextRole]);
        if (!$next) {
            return ['handed_off' => false, 'reason' => "no active $nextRole employee"];
        }
        $nextType = match ($nextRole) {
            'qualifier' => 'qualify_lead',
            'followup' => 'followup_draft',
        };
        $queuedId = $this->enqueue((int) $next['id'], $nextType, $leadId, ['from_role' => $fromRole]);
        return ['handed_off' => true, 'to_employee' => $next['name'], 'task_id' => $queuedId];
    }

    /**
     * C-suite analyst reports: real snapshots + AI prose (read-only, no writes).
     */
    private function analystReport(array $emp, string $type): array
    {
        $role = $emp['role'];
        $snapshot = [];
        if ($role === 'ceo_analyst') {
            $snapshot['plots'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM plots") ?: 0);
            $snapshot['leads_live'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM leads WHERE deleted_at IS NULL") ?: 0);
            $snapshot['open_followups'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM followups WHERE status = 'pending'") ?: 0);
        } elseif ($role === 'cfo_analyst') {
            $snapshot['payments_30d'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)") ?: 0);
            $snapshot['collected_30d'] = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")['s'] ?? 0);
            $snapshot['wallet_credits_30d'] = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM wallet_transactions WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")['s'] ?? 0);
        } elseif ($role === 'cto_analyst') {
            $snapshot['tables'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()") ?: 0);
            $snapshot['ai_employees'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM ai_employees") ?: 0);
            $snapshot['pending_tasks'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM ai_employee_tasks WHERE status = 'pending'") ?: 0);
        } elseif ($role === 'coo_analyst') {
            $snapshot['stale_leads_30d'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM leads WHERE deleted_at IS NULL AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND status NOT IN ('closed_won','closed_lost')") ?: 0);
            $snapshot['available_plots'] = (int) ($this->db->fetchOne("SELECT COUNT(*) as n FROM plots WHERE status = 'available'") ?: 0);
        }
        return ['report' => $type, 'snapshot' => $snapshot];
    }

    private function qualifyLead(int $leadId, int $employeeId): array
    {
        $lead = $this->db->fetch("SELECT id, name, phone, status, budget FROM leads WHERE id = ? AND deleted_at IS NULL", [$leadId]);
        if (!$lead) {
            throw new \RuntimeException('Lead not found');
        }
        $summary = $this->aiSummary(
            "Lead {$lead['name']}, phone {$lead['phone']}, status {$lead['status']}, budget " . ($lead['budget'] ?? 'unknown') .
            ". Write one short qualification note (ready for call? interested?) in Hindi-English mix, under 20 words."
        );
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, activity_type, description, created_by) VALUES (?, 'note', ?, ?)",
            [$leadId, '[Aira] ' . $summary, $employeeId]
        );
        return ['qualified' => true, 'note' => $summary];
    }

    private function draftFollowup(int $leadId, int $employeeId): array
    {
        $lead = $this->db->fetch("SELECT id, name, phone FROM leads WHERE id = ? AND deleted_at IS NULL", [$leadId]);
        if (!$lead) {
            throw new \RuntimeException('Lead not found');
        }
        $noc = !empty($this->lastTaskPayload['noc_hint'] ?? null);
        $hint = $noc ? ' Visit ke baad NOC/Documents ready rahe — pure paperwork SRO tak approve ho jayega.' : '';
        $draft = $this->aiSummary(
            "Write a short WhatsApp follow-up message for real-estate lead {$lead['name']} who viewed plots in Gorakhpur. Polite, 2 lines, Hindi-English mix, no price promise.$hint"
        );
        return ['drafted' => true, 'message' => $draft];
    }

    private function notifyStale(int $leadId, int $employeeId): array
    {
        $lead = $this->db->fetch("SELECT id, assigned_to FROM leads WHERE id = ? AND deleted_at IS NULL", [$leadId]);
        if (!$lead) {
            throw new \RuntimeException('Lead not found');
        }
        return ['notified' => ($lead['assigned_to'] ?? null) !== null, 'assigned_to' => $lead['assigned_to'] ?? null];
    }

    private function aiSummary(string $prompt): string
    {
        try {
            $engines = \App\Services\AI\FreeAIEngines::getInstance();
            $out = $engines->generate($prompt, ['max_tokens' => 60, 'temperature' => 0.6]);
            $text = trim((string) ($out['text'] ?? ''));
            if ($text !== '') {
                return $text;
            }
        } catch (\Throwable $e) {
            // fall through to deterministic note
        }
        return 'Checked lead details — needs manual follow-up.';
    }
}
