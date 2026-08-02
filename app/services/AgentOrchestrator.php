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
            $sql = "SELECT t.* FROM agent_tasks t WHERE 1=1" . $this->tenantSql();
            $params = [];
            if ($status) { $sql .= " AND t.status = :s"; $params[':s'] = $status; }
            $sql .= " ORDER BY t.assigned_at DESC LIMIT 200";
            $st = $this->db->prepare($sql);
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgentOrchestrator::listTasks error: " . $e->getMessage());
            return [];
        }
    }

    public function createTask(int $agentId, string $type, array $payload, int $priority = 5): array
    {
        $st = $this->db->prepare("INSERT INTO agent_tasks (agent_id, task_type, task_payload, priority, status" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:a, :t, :p, :pr, 'queued'" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
        $params = [':a' => $agentId, ':t' => $type, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':pr' => $priority];
        if (!empty($insertData = $this->tenantInsertData())) $params = array_merge($params, $insertData);
        $st->execute($params);
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

            $st = $this->db->prepare("INSERT INTO agent_executions (task_id, agent_id, status, result, duration_ms, completed_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:t, :a, 'success', :r, :d, NOW()" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
            $eparams = [':t' => $taskId, ':a' => $task['agent_id'], ':r' => json_encode($result, JSON_UNESCAPED_UNICODE), ':d' => $duration];
            if (!empty($insertData = $this->tenantInsertData())) $eparams = array_merge($eparams, $insertData);
            $st->execute($eparams);
            $execId = (int)$this->db->lastInsertId();

            $this->updateTaskStatus($taskId, 'completed', $execId);
            $this->updateState($task['agent_id'], $task['task_type'], $result);

            return ['ok' => true, 'execution_id' => $execId, 'result' => $result, 'duration_ms' => $duration];
        } catch (\Throwable $e) {
            $st = $this->db->prepare("INSERT INTO agent_executions (task_id, agent_id, status, error_message, duration_ms, completed_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:t, :a, 'failed', :e, :d, NOW()" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
            $eparams = [':t' => $taskId, ':a' => $task['agent_id'], ':e' => $e->getMessage(), ':d' => (int)((microtime(true) - $startTime) * 1000)];
            if (!empty($insertData = $this->tenantInsertData())) $eparams = array_merge($eparams, $insertData);
            $st->execute($eparams);
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
        $sql = "UPDATE agent_tasks SET status = :s, completed_at = NOW()" . ($executionId ? ", execution_id = :e" : "") . " WHERE id = :id" . $this->tenantSql();
        $params = [':s' => $status, ':id' => $id];
        if ($executionId) $params[':e'] = $executionId;
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return ['ok' => true];
    }

    public function getTask(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM agent_tasks WHERE id = :id" . $this->tenantSql());
        $sparams = [':id' => $id];
        if ($this->tenantId() > 1) $sparams[] = $this->tenantId();
        $st->execute($sparams);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function listExecutions(int $taskId = 0, int $limit = 100): array
    {
        try {
            $sql = "SELECT e.* FROM agent_executions e WHERE 1=1" . $this->tenantSql();
            $params = [];
            $sql .= " ORDER BY e.execution_end DESC LIMIT :lim";
            $st = $this->db->prepare($sql);
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
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
$st = $this->db->prepare("INSERT INTO agent_state (agent_id, context, state_data, expires_at, updated_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:a, :c, :d, :e, NOW()" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")
                                   ON DUPLICATE KEY UPDATE state_data = VALUES(state_data), expires_at = VALUES(expires_at), updated_at = NOW()");
        $sparams = [':a' => $agentId, ':c' => $context, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE), ':e' => $expires];
        if (!empty($insertData = $this->tenantInsertData())) $sparams = array_merge($sparams, $insertData);
        $st->execute($sparams);
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
        $st = $this->db->query("DELETE FROM agent_state WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSql());
        return (int)($st->rowCount() ?? 0);
    }

    public function listWorkflows(int $limit = 50): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM workflow_automations WHERE 1=1" . $this->tenantSql() . " ORDER BY id DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgentOrchestrator::listWorkflows error: " . $e->getMessage());
            return [];
        }
    }

    public function createWorkflow(string $name, string $trigger, array $steps, ?int $createdBy = null): array
    {
        $st = $this->db->prepare("INSERT INTO workflow_automations (automation_name, trigger_event, actions, is_active" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:n, :t, :s, 1" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
        $wparams = [':n' => $name, ':t' => $trigger, ':s' => json_encode($steps, JSON_UNESCAPED_UNICODE)];
        if (!empty($insertData = $this->tenantInsertData())) $wparams = array_merge($wparams, $insertData);
        $st->execute($wparams);
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
                    $leadParams = [':n' => $context['name'] ?? '', ':e' => $context['email'] ?? '', ':p' => $context['phone'] ?? '', ':s' => $context['source'] ?? 'workflow', ':num' => 'WF-' . date('Ymd') . '-' . substr(uniqid(), -4)];
                    $leadSql = "INSERT INTO leads (name, email, phone, source, lead_number" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:n, :e, :p, :s, :num" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")";
                    if (!empty($insertData = $this->tenantInsertData())) $leadParams = array_merge($leadParams, $insertData);
                    $st = $this->db->prepare($leadSql);
                    $st->execute($leadParams);
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
        $sql = "SELECT id FROM agent_tasks WHERE status = 'queued'" . $this->tenantSql() . " ORDER BY priority DESC, assigned_at ASC LIMIT :lim";
        $st = $this->db->prepare($sql);
        $params = [];
        if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
        foreach ($params as $k => $v) $st->bindValue($k, $v);
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
