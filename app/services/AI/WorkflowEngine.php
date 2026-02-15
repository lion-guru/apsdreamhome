<?php

namespace App\Services\AI;

/**
 * Advanced Graph-based Workflow Engine
 * Handles complex node connections and state-aware execution.
 */
class WorkflowEngine {
    private $db;
    private $aiManager;
    private $executionLog = [];

    public function __construct($aiManager) {
        $this->db = \App\Core\App::database();
        $this->aiManager = $aiManager;
    }

    /**
     * Execute a workflow by ID
     */
    public function execute($workflowId, $triggerData = []) {
        $this->executionLog = []; // Reset log for new execution
        $workflow = $this->getWorkflow($workflowId);
        if (!$workflow) {
            $this->aiManager->auditLog('workflow_execution', ['workflow_id' => $workflowId, 'error' => 'Workflow not found'], 'error');
            return ['status' => 'error', 'message' => 'Workflow not found'];
        }

        $this->aiManager->auditLog('workflow_execution', [
            'workflow_id' => $workflowId,
            'workflow_name' => $workflow['name'],
            'trigger_data' => $triggerData
        ], 'info');

        // Create execution record
        $workflowIdInt = intval($workflowId);
        $this->db->execute("INSERT INTO workflow_executions (workflow_id, status) VALUES (?, 'running')", [$workflowIdInt]);
        $executionId = $this->db->lastInsertId();

        $graph = json_decode($workflow['nodes'], true);
        if (!$graph || !isset($graph['nodes'])) {
            $this->updateExecution($executionId, 'failed', ['error' => 'Invalid workflow graph']);
            return ['status' => 'error', 'message' => 'Invalid workflow graph'];
        }

        $nodes = $graph['nodes'];
        $connections = $graph['connections'] ?? [];

        $startTime = microtime(true);
        $context = $triggerData;

        // Inject Metadata
        $context['meta'] = [
            'workflow_id' => $workflowId,
            'execution_id' => $executionId,
            'start_time' => date('Y-m-d H:i:s'),
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'timestamp' => time()
        ];

        $visited = [];

        // Find entry nodes (triggers)
        $startNodes = array_filter($nodes, function($node) {
            return $node['type'] === 'trigger';
        });

        if (empty($startNodes)) {
            $startNodes = [reset($nodes)];
        }

        foreach ($startNodes as $startNode) {
            $this->processNode($startNode, $nodes, $connections, $context, $visited);
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $status = 'success';
        foreach ($this->executionLog as $log) {
            if ($log['status'] === 'failed') {
                $status = 'failed';
                break;
            }
        }

        // Update final execution record
        $this->updateExecution($executionId, $status, $this->executionLog, $context, $duration);

        // Update last run
        $this->db->execute("UPDATE ai_workflows SET last_run = NOW() WHERE id = ?", [intval($workflowId)]);

        return [
            'status' => $status,
            'execution_id' => $executionId,
            'workflow' => $workflow['name'],
            'log' => $this->executionLog,
            'final_context' => $context
        ];
    }

    private function updateExecution($id, $status, $log, $context = [], $duration = 0) {
        $logStr = json_encode($log);
        $ctxStr = json_encode($context);
        $sql = "UPDATE workflow_executions SET status = ?, logs = ?, context = ?, duration = ?, completed_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$status, $logStr, $ctxStr, $duration, $id]);
    }

    /**
     * Recursive node processor
     */
    private function processNode($node, &$nodes, &$connections, &$context, &$visited) {
        $nodeId = $node['id'];
        if (isset($visited[$nodeId])) return;
        $visited[$nodeId] = true;

        $startTime = microtime(true);
        $maxRetries = $node['config']['retry_count'] ?? 0;
        $attempt = 0;
        $result = ['status' => 'failed', 'error' => 'Initial attempt'];

        while ($attempt <= $maxRetries) {
            $result = $this->runNodeLogic($node, $context);
            if ($result['status'] === 'success') break;
            $attempt++;
            if ($attempt <= $maxRetries) {
                $delay = ($node['config']['retry_delay'] ?? 1) * 1000; // ms
                usleep($delay * 1000);
            }
        }
        $duration = microtime(true) - $startTime;

        $this->executionLog[] = [
            'node_id' => $nodeId,
            'node_name' => $node['name'] ?? $node['type'],
            'status' => $result['status'],
            'duration' => round($duration, 4),
            'output' => $result['output'] ?? null,
            'error' => $result['error'] ?? null
        ];

        if ($result['status'] === 'success') {
            // Update context with node output
            if (isset($result['output'])) {
                $context['nodes'][$nodeId] = $result['output'];

                // Support node aliasing
                if (isset($node['alias']) && !empty($node['alias'])) {
                    $context[$node['alias']] = $result['output'];
                }

                $context['last_output'] = $result['output'];
            }

            // Find next nodes based on connections
            $nextConnections = array_filter($connections, function($conn) use ($nodeId, $result) {
                if ($conn['from'] != $nodeId) return false;

                // Handle conditional branching if source node is a logic condition
                if (isset($result['output']['result']) && is_bool($result['output']['result'])) {
                    $condition = $conn['condition'] ?? 'true';
                    $outcome = $result['output']['result'] ? 'true' : 'false';
                    return $condition === $outcome;
                }

                return true;
            });

            foreach ($nextConnections as $conn) {
                $nextNodeId = $conn['to'];
                $nextNode = array_filter($nodes, function($n) use ($nextNodeId) {
                    return $n['id'] == $nextNodeId;
                });

                if (!empty($nextNode)) {
                    $this->processNode(reset($nextNode), $nodes, $connections, $context, $visited);
                }
            }
        } else {
            // Handle error / retry logic here if needed
            $this->handleNodeError($node, $result);
        }
    }

    /**
     * Run logic based on node type
     */
    private function runNodeLogic($node, $context) {
        $nodeType = $node['type'];
        $config = $node['config'] ?? [];

        switch ($nodeType) {
            case 'trigger':
                return ['status' => 'success', 'output' => $context];

            case 'http_request':
                require_once __DIR__ . '/nodes/HTTPNode.php';
                $nodeObj = new HTTPNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'database':
                require_once __DIR__ . '/nodes/DBNode.php';
                $nodeObj = new DBNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'ai_model':
                require_once __DIR__ . '/nodes/AINode.php';
                $nodeObj = new AINode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'notification':
                require_once __DIR__ . '/nodes/NotificationNode.php';
                $nodeObj = new NotificationNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'telecalling':
                require_once __DIR__ . '/nodes/TelecallingNode.php';
                $nodeObj = new TelecallingNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'logic_gate':
                require_once __DIR__ . '/nodes/LogicNode.php';
                $nodeObj = new LogicNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'email':
                require_once __DIR__ . '/nodes/EmailNode.php';
                $nodeObj = new EmailNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'social_media':
                require_once __DIR__ . '/nodes/SocialMediaNode.php';
                $nodeObj = new SocialMediaNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'sms':
                require_once __DIR__ . '/nodes/SMSNode.php';
                $nodeObj = new SMSNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'calendar':
                require_once __DIR__ . '/nodes/CalendarNode.php';
                $nodeObj = new CalendarNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            case 'payment':
                require_once __DIR__ . '/nodes/PaymentNode.php';
                $nodeObj = new PaymentNode($this->aiManager);
                return $nodeObj->executeWithSelfHeal($config, $context);

            default:
                return ['status' => 'failed', 'error' => 'Unknown node type: ' . $nodeType];
        }
    }

    private function handleNodeError($node, $result) {
        // Log error to system_logs or ai_agent_logs
        $errorMsg = $result['error'] ?? 'Unknown error';
        $nodeId = $node['id'];

        $sql = "INSERT INTO system_activities (level, message, context) VALUES ('error', ?, ?)";
        $logMsg = "Workflow Node Error: " . $node['type'] . " ($nodeId)";
        $logCtx = json_encode(['node' => $node, 'result' => $result]);

        return $this->db->execute($sql, [$logMsg, $logCtx]);
    }

    private function getWorkflow($id) {
        $sql = "SELECT * FROM ai_workflows WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
}
