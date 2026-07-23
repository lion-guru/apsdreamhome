<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use Exception;

/**
 * Workflow Engine Service
 * Visual workflow automation with 12 node types
 */
class WorkflowEngine
{
    private $db;
    private $nodes = [];
    private $connections = [];
    private $context = [];
    private $executionId = null;
    
    // Node type constants
    const NODE_TRIGGER = 'trigger';
    const NODE_HTTP = 'http_request';
    const NODE_DB = 'database';
    const NODE_AI = 'ai_model';
    const NODE_NOTIFICATION = 'notification';
    const NODE_TELECALLING = 'telecalling';
    const NODE_LOGIC = 'logic_gate';
    const NODE_EMAIL = 'email';
    const NODE_SOCIAL = 'social_media';
    const NODE_SMS = 'sms';
    const NODE_CALENDAR = 'calendar';
    const NODE_PAYMENT = 'payment';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Execute a workflow
     */
    public function execute(int $workflowId, array $triggerData = []): array
    {
        try {
            // Get workflow definition
            $workflow = $this->getWorkflow($workflowId);
            if (!$workflow) {
                return ['success' => false, 'message' => 'Workflow not found'];
            }
            
            $this->nodes = json_decode($workflow['nodes_json'], true) ?? [];
            $this->connections = json_decode($workflow['connections_json'], true) ?? [];
            $this->context = $triggerData;
            
            // Create execution record
            $this->executionId = $this->createExecution($workflowId, $triggerData);
            
            // Find trigger nodes
            $triggerNodes = array_filter($this->nodes, fn($n) => $n['type'] === self::NODE_TRIGGER);
            
            $results = [];
            $visited = [];
            foreach ($triggerNodes as $node) {
                $result = $this->processNode($node, $visited, $results);
                $results[] = $result;
            }
            
            $this->updateExecution($this->executionId, 'completed', 'Workflow executed successfully', $this->context);
            
            return ['success' => true, 'execution_id' => $this->executionId, 'results' => $results];
            
        } catch (Exception $e) {
            if ($this->executionId) {
                $this->updateExecution($this->executionId, 'failed', $e->getMessage(), $this->context);
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Process a single node
     */
    private function processNode(array $node, array &$visitedNodes, array &$results): array
    {
        $nodeId = $node['id'];
        
        if (isset($visitedNodes[$nodeId])) {
            return ['success' => true, 'skipped' => true];
        }
        
        $visitedNodes[$nodeId] = true;
        
        $result = $this->runNodeLogic($node);
        
        $this->updateExecution($this->executionId, 'running', "Node {$node['name']} completed", $this->context);
        
        // Process connected nodes
        $outgoing = array_filter($this->connections, fn($c) => $c['source'] === $nodeId);
        foreach ($outgoing as $conn) {
            $targetNode = null;
            foreach ($this->nodes as $n) {
                if ($n['id'] === $conn['target']) {
                    $targetNode = $n;
                    break;
                }
            }
            if ($targetNode) {
                $this->processNode($targetNode, $visitedNodes, $results);
            }
        }
        
        return $result;
    }
    
    /**
     * Run node logic based on type
     */
    private function runNodeLogic(array $node): array
    {
        $type = $node['type'];
        $config = $node['config'] ?? [];
        
        switch ($type) {
            case self::NODE_HTTP:
                return $this->executeHttpNode($config);
            case self::NODE_DB:
                return $this->executeDbNode($config);
            case self::NODE_AI:
                return $this->executeAiNode($config);
            case self::NODE_NOTIFICATION:
                return $this->executeNotificationNode($config);
            case self::NODE_TELECALLING:
                return $this->executeTelecallingNode($config);
            case self::NODE_LOGIC:
                return $this->executeLogicNode($config);
            case self::NODE_EMAIL:
                return $this->executeEmailNode($config);
            case self::NODE_SOCIAL:
                return $this->executeSocialNode($config);
            case self::NODE_SMS:
                return $this->executeSmsNode($config);
            case self::NODE_CALENDAR:
                return $this->executeCalendarNode($config);
            case self::NODE_PAYMENT:
                return $this->executePaymentNode($config);
            default:
                return ['success' => false, 'message' => "Unknown node type: $type"];
        }
    }
    
    private function executeHttpNode(array $config): array
    {
        $url = $config['url'] ?? '';
        $method = $config['method'] ?? 'GET';
        $headers = $config['headers'] ?? [];
        $body = $config['body'] ?? null;
        
        // Replace variables in URL/body
        $url = $this->replaceVariables($url);
        if ($body) $body = $this->replaceVariables($body);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $outputVar = $config['output_variable'] ?? 'http_response';
        $this->context[$outputVar] = $response;
        
        return ['success' => $httpCode >= 200 && $httpCode < 300, 'status_code' => $httpCode, 'response' => $response];
    }
    
    private function executeDbNode(array $config): array
    {
        $query = $this->replaceVariables($config['query'] ?? '');
        $params = $config['params'] ?? [];
        
        // Replace variables in params
        foreach ($params as $key => $value) {
            $params[$key] = $this->replaceVariables($value);
        }
        
        try {
            if (stripos($query, 'SELECT') === 0) {
                $result = $this->db->fetchAll($query, $params);
            } else {
                $this->db->query($query, $params);
                $result = ['affected_rows' => $this->db->rowCount(), 'insert_id' => $this->db->lastInsertId()];
            }
            
            $outputVar = $config['output_variable'] ?? 'db_result';
            $this->context[$outputVar] = $result;
            
            return ['success' => true, 'result' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
private function executeAiNode(array $config): array
    {
        // Integration with existing AI services
        $prompt = $this->replaceVariables($config['prompt'] ?? '');
        $task = $config['task'] ?? 'analyze';
        
        try {
            // Use existing AIGateway for AI processing
            $aiGateway = new \App\Services\AI\AIGateway();
            $result = $aiGateway->process($prompt, $task, ['type' => 'analysis']);
            
            $outputVar = $config['output_variable'] ?? 'ai_result';
            $this->context[$outputVar] = $result;
            
            return ['success' => true, 'result' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeNotificationNode(array $config): array
    {
        $type = $config['type'] ?? 'push';
        $title = $this->replaceVariables($config['title'] ?? '');
        $message = $this->replaceVariables($config['message'] ?? '');
        $recipients = $config['recipients'] ?? [];
        
        // Use existing notification services
        try {
            if ($type === 'push') {
                $pushService = new \App\Services\Communication\PushSender();
                foreach ($recipients as $recipient) {
                    $pushService->sendToUser((int)$recipient, $title, $message);
                }
            }
            
            return ['success' => true, 'sent' => count($recipients)];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeTelecallingNode(array $config): array
    {
        $scriptId = $config['script_id'] ?? 0;
        $leadId = $this->replaceVariables($config['lead_id'] ?? '');
        $phone = $this->replaceVariables($config['phone'] ?? '');
        
        try {
            // Schedule a call via existing system
            $this->db->query("
                INSERT INTO ai_calling_schedule (lead_id, script_id, phone, scheduled_date, scheduled_time, priority, status)
                VALUES (?, ?, ?, CURDATE(), CURTIME(), 'high', 'pending')
            ", [$leadId, $scriptId, $phone]);
            
            return ['success' => true, 'call_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeLogicNode(array $config): array
    {
        $condition = $this->replaceVariables($config['condition'] ?? '');
        $trueNode = $config['true_node'] ?? null;
        $falseNode = $config['false_node'] ?? null;
        
        // Simple condition evaluation
        // In production, use a proper expression evaluator
        $result = false;
        try {
            // Replace variables and evaluate
            $condition = str_replace(
                array_keys($this->context),
                array_map(fn($v) => is_string($v) ? "'$v'" : $v, array_values($this->context)),
                $condition
            );
            $result = (bool)eval("return $condition;");
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Logic evaluation failed'];
        }
        
        $this->context['logic_result'] = $result;
        
        // Return which branch to follow
        return [
            'success' => true,
            'branch' => $result ? 'true' : 'false',
            'next_node' => $result ? $trueNode : $falseNode
        ];
    }
    
    private function executeEmailNode(array $config): array
    {
        $to = $this->replaceVariables($config['to'] ?? '');
        $subject = $this->replaceVariables($config['subject'] ?? '');
        $template = $config['template'] ?? 'default';
        $data = $config['data'] ?? [];
        
        // Replace variables in data
        foreach ($data as $key => $value) {
            $data[$key] = $this->replaceVariables($value);
        }
        
        try {
            $emailService = new \App\Services\Communication\EmailService();
            $emailService->send($to, $subject, $template, $data);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeSocialNode(array $config): array
    {
        $platform = $config['platform'] ?? 'facebook';
        $content = $this->replaceVariables($config['content'] ?? '');
        $imageUrl = $config['image_url'] ?? null;
        $accountId = $config['account_id'] ?? 1;
        
        try {
            $socialService = new \App\Services\SocialMediaService();
            $result = $socialService->createPost(['id' => $accountId], [
                'platform' => $platform,
                'post_content' => $content,
                'image_url' => $imageUrl,
                'status' => 'published'
            ]);
            
            return ['success' => true, 'post_id' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeSmsNode(array $config): array
    {
        $to = $this->replaceVariables($config['to'] ?? '');
        $message = $this->replaceVariables($config['message'] ?? '');
        $template = $config['template'] ?? null;
        
        try {
            $smsService = new \App\Services\Communication\SMSService();
            $smsService->sendSMS($to, $message, $template);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executeCalendarNode(array $config): array
    {
        $action = $config['action'] ?? 'create'; // create, update, delete
        $title = $this->replaceVariables($config['title'] ?? '');
        $start = $this->replaceVariables($config['start_time'] ?? '');
        $end = $this->replaceVariables($config['end_time'] ?? '');
        $attendees = $config['attendees'] ?? [];
        
        try {
            $meetingService = new \App\Services\MeetingService();
            
            if ($action === 'create') {
                $result = $meetingService->createMeeting([
                    'title' => $title,
                    'start_time' => $start,
                    'end_time' => $end,
                    'attendees' => $attendees
                ]);
            }
            
            return ['success' => true, 'meeting_id' => $result['id'] ?? null];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function executePaymentNode(array $config): array
    {
        $amount = $this->replaceVariables($config['amount'] ?? '0');
        $currency = $config['currency'] ?? 'INR';
        $method = $config['method'] ?? 'razorpay';
        $description = $this->replaceVariables($config['description'] ?? '');
        
        try {
            $paymentService = new \App\Services\Payment\PaymentGatewayService();
            $order = $paymentService->createOrder((float)$amount, $currency, $description);
            
            $outputVar = $config['output_variable'] ?? 'payment_order';
            $this->context[$outputVar] = $order;
            
            return ['success' => true, 'order' => $order];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function replaceVariables(string $text): string
    {
        foreach ($this->context as $key => $value) {
            $text = str_replace("{{$key}}", is_array($value) ? json_encode($value) : (string)$value, $text);
            $text = str_replace("{{context.$key}}", is_array($value) ? json_encode($value) : (string)$value, $text);
        }
        return $text;
    }
    
    private function createExecution(int $workflowId, array $triggerData): int
    {
        $this->db->query("
            INSERT INTO workflow_executions (workflow_id, trigger_data, status, context, started_at)
            VALUES (?, ?, 'running', ?, NOW())
        ", [$workflowId, json_encode($triggerData), json_encode($triggerData)]);
        
        return $this->db->lastInsertId();
    }
    
    private function updateExecution(int $executionId, string $status, string $log, array $context): void
    {
        $this->db->query("
            UPDATE workflow_executions 
            SET status = ?, log = CONCAT(IFNULL(log, ''), ?, '\\n'), context = ?, updated_at = NOW()
            WHERE id = ?
        ", [$status, $log, json_encode($context), $executionId]);
    }
    
    private function getWorkflow(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM workflows WHERE id = ? AND is_active = 1", [$id]);
    }
    
    // CRUD methods for workflow definitions
    public function createWorkflow(array $data): array
    {
        try {
            $this->db->query("
                INSERT INTO workflows (name, description, nodes_json, connections_json, is_active, created_by)
                VALUES (?, ?, ?, ?, 1, ?)
            ", [
                $data['name'],
                $data['description'] ?? '',
                json_encode($data['nodes'] ?? []),
                json_encode($data['connections'] ?? []),
                $data['created_by'] ?? 0
            ]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getAllWorkflows(): array
    {
        try {
            return $this->db->fetchAll("SELECT * FROM workflows WHERE is_active = 1 ORDER BY created_at DESC") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
}