<?php
namespace App\Services;

use PDO;

use \App\Traits\ServiceTenantTrait;

/**
 * AgentOrchestrator - background agent execution + workflow automation
 */
class AgentOrchestrator
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function listTasks(int $agentId = 0, string $status = ''): array
    {
        try {
            $sql = "SELECT t.* FROM agent_tasks t WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND t.status = :s"; $params[':s'] = $status; }
            $sql .= " ORDER BY t.assigned_at DESC LIMIT 200";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgentOrchestrator::listTasks error: " . $e->getMessage());
            return [];
        }
    }

    public function createTask(int $agentId, string $type, array $payload, int $priority = 5): array
    {
        $st = $this->db->prepare("INSERT INTO agent_tasks (agent_id, task_type, task_payload, priority, status) VALUES (:a, :t, :p, :pr, 'queued')");
        $st->execute([':a' => $agentId, ':t' => $type, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':pr' => $priority]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function executeTask(int $taskId): array
    {
        $task = $this->getTask($taskId);
        if (!$task) return ['error' => 'Task not found'];
        if ($task['status'] !== 'pending') return ['error' => 'Task not pending'];

        $this->updateTaskStatus($taskId, 'running');
        $startTime = microtime(true);

        try {
            $result = $this->runTaskLogic($task);
            $duration = (int)((microtime(true) - $startTime) * 1000);

            $st = $this->db->prepare("INSERT INTO agent_executions (task_id, agent_id, status, result, duration_ms, completed_at) VALUES (:t, :a, 'success', :r, :d, NOW())");
            $st->execute([':t' => $taskId, ':a' => $task['agent_id'], ':r' => json_encode($result, JSON_UNESCAPED_UNICODE), ':d' => $duration]);
            $execId = (int)$this->db->lastInsertId();

            $this->updateTaskStatus($taskId, 'completed', $execId);
            $this->updateState($task['agent_id'], $task['task_type'], $result);

            return ['ok' => true, 'execution_id' => $execId, 'result' => $result, 'duration_ms' => $duration];
        } catch (\Throwable $e) {
            $st = $this->db->prepare("INSERT INTO agent_executions (task_id, agent_id, status, error_message, duration_ms, completed_at) VALUES (:t, :a, 'failed', :e, :d, NOW())");
            $st->execute([':t' => $taskId, ':a' => $task['agent_id'], ':e' => $e->getMessage(), ':d' => (int)((microtime(true) - $startTime) * 1000)]);
            $this->updateTaskStatus($taskId, 'failed');
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function runTaskLogic(array $task): array
    {
        $payload = json_decode($task['payload'] ?? '{}', true) ?: [];
        switch ($task['task_type']) {
            case 'lead_score':
                require_once __DIR__ . '/AI/AIManager.php';
                $ai = new \App\Services\AI\AIManager($this->db);
                $score = $ai->scoreLead((int)($payload['lead_id'] ?? 0));
                return $score;
            case 'send_notification':
                require_once __DIR__ . '/NotificationService.php';
                $ns = new NotificationService($this->db);
                return $ns->send((int)$payload['user_id'], $payload['channel'] ?? 'email', $payload['subject'] ?? '', $payload['message'] ?? '', $payload);
            case 'price_predict':
                require_once __DIR__ . '/AI/AIManager.php';
                $ai = new \App\Services\AI\AIManager($this->db);
                return $ai->predictPrice((float)$payload['area_sqft'], $payload['location'] ?? '', $payload['property_type'] ?? '');
            case 'generate_forecast':
                require_once __DIR__ . '/AnalyticsService.php';
                $an = new AnalyticsService($this->db);
                return $an->generateForecast($payload['metric'] ?? 'revenue', (int)($payload['periods'] ?? 6));
            default:
                return ['status' => 'noop', 'message' => 'Unknown task type: ' . $task['task_type']];
        }
    }

    public function updateTaskStatus(int $id, string $status, ?int $executionId = null): array
    {
        $sql = "UPDATE agent_tasks SET status = :s, completed_at = NOW()";
        $params = [':s' => $status, ':id' => $id];
        if ($executionId) { $sql .= ", execution_id = :e"; $params[':e'] = $executionId; }
        $sql .= " WHERE id = :id";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return ['ok' => true];
    }

    public function getTask(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM agent_tasks WHERE id = :id");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function listExecutions(int $taskId = 0, int $limit = 100): array
    {
        try {
            $sql = "SELECT e.* FROM agent_executions e WHERE 1=1";
            $params = [];
            $sql .= " ORDER BY e.execution_end DESC LIMIT :lim";
            $st = $this->db->prepare($sql);
            foreach ($params as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgentOrchestrator::listExecutions error: " . $e->getMessage());
            return [];
        }
    }

    public function updateState(int $agentId, string $context, array $data, int $ttl = 3600): array
    {
        $expires = date('Y-m-d H:i:s', time() + $ttl);
        $st = $this->db->prepare("INSERT INTO agent_state (agent_id, context, state_data, expires_at, updated_at) VALUES (:a, :c, :d, :e, NOW())
                                  ON DUPLICATE KEY UPDATE state_data = VALUES(state_data), expires_at = VALUES(expires_at), updated_at = NOW()");
        $st->execute([':a' => $agentId, ':c' => $context, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE), ':e' => $expires]);
        return ['ok' => true];
    }

    public function getState(int $agentId, string $context): ?array
    {
        $st = $this->db->prepare("SELECT * FROM agent_state WHERE agent_id = :a AND context = :c AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY updated_at DESC LIMIT 1");
        $st->execute([':a' => $agentId, ':c' => $context]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $r['state_data'] = json_decode($r['state_data'] ?? '{}', true) ?: [];
        return $r;
    }

    public function clearExpiredState(): int
    {
        $st = $this->db->query("DELETE FROM agent_state WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)($st->rowCount() ?? 0);
    }

    public function listWorkflows(int $limit = 50): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM workflow_automations WHERE 1=1 ORDER BY id DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgentOrchestrator::listWorkflows error: " . $e->getMessage());
            return [];
        }
    }

    public function createWorkflow(string $name, string $trigger, array $steps, ?int $createdBy = null): array
    {
        $st = $this->db->prepare("INSERT INTO workflow_automations (automation_name, trigger_event, actions, is_active) VALUES (:n, :t, :s, 1)");
        $st->execute([':n' => $name, ':t' => $trigger, ':s' => json_encode($steps, JSON_UNESCAPED_UNICODE)]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function triggerWorkflow(string $trigger, array $context): array
    {
        $st = $this->db->prepare("SELECT * FROM workflow_automations WHERE trigger_event = :t AND is_active = 1");
        $st->execute([':t' => $trigger]);
        $workflows = $st->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($workflows as $wf) {
            $steps = json_decode($wf['actions'] ?? '[]', true) ?: [];
            $execResults = [];
            foreach ($steps as $step) {
                $execResults[] = $this->executeStep($step, $context);
            }
            $results[] = ['workflow' => $wf['automation_name'], 'id' => $wf['id'], 'results' => $execResults];
        }
        return ['ok' => true, 'workflows_triggered' => count($workflows), 'results' => $results];
    }

    private function executeStep(array $step, array $context): array
    {
        $type = $step['type'] ?? 'noop';
        try {
            switch ($type) {
                case 'send_email':
                case 'send_sms':
                case 'send_whatsapp':
                case 'send_push':
                    require_once __DIR__ . '/NotificationService.php';
                    $ns = new NotificationService($this->db);
                    $channel = str_replace('send_', '', $type);
                    $userId = (int)($context['user_id'] ?? $step['user_id'] ?? 0);
                    return $ns->send($userId, $channel, $step['subject'] ?? '', $step['message'] ?? '', $step);
                case 'agent_task':
                    $payload = array_merge($step['payload'] ?? [], $context);
                    return $this->createTask((int)($step['agent_id'] ?? 1), $step['task_type'] ?? 'lead_score', $payload, (int)($step['priority'] ?? 5));
                case 'create_lead':
                    $st = $this->db->prepare("INSERT INTO leads (name, email, phone, source, lead_number) VALUES (:n, :e, :p, :s, :num)");
                    $st->execute([':n' => $context['name'] ?? '', ':e' => $context['email'] ?? '', ':p' => $context['phone'] ?? '', ':s' => $context['source'] ?? 'workflow', ':num' => 'WF-' . date('Ymd') . '-' . substr(uniqid(), -4)]);
                    return ['ok' => true, 'lead_id' => (int)$this->db->lastInsertId()];
                default:
                    return ['ok' => true, 'noop' => $type];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function processPendingTasks(int $maxTasks = 50): array
    {
        $st = $this->db->prepare("SELECT id FROM agent_tasks WHERE status = 'queued' ORDER BY priority DESC, assigned_at ASC LIMIT :lim");
        $st->bindValue(':lim', $maxTasks, PDO::PARAM_INT);
        $st->execute();
        $ids = $st->fetchAll(PDO::FETCH_COLUMN);

        $results = [];
        foreach ($ids as $id) {
            $results[] = ['task_id' => $id, 'result' => $this->executeTask((int)$id)];
        }
        return ['ok' => true, 'processed' => count($results), 'results' => $results];
    }
}
