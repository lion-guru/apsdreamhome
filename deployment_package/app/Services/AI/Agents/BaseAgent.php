<?php

namespace App\Services\AI\Agents;

use App\Core\App;
use App\Services\Legacy\PermissionManager;
use function App\Services\Legacy\audit_log;
use Exception;

/**
 * Base AI Agent Class
 * Provides common functionality for all specialized agents.
 *
 * @property \App\Core\Database $db
 */

abstract class BaseAgent implements AgentInterface {
    protected $agentId;
    protected $agentName;
    protected $config = [];
    protected $logger;
    protected $status = 'idle';
    /** @var \App\Core\Database */
    protected $db;

    public function __construct($agentId, $agentName, $db = null) {
        $this->agentId = $agentId;
        $this->agentName = $agentName;
        $this->db = $db ?: \App\Core\App::database();
    }

    public function initialize($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->logActivity("INITIALIZED", "Agent {$this->agentName} started.");
        $this->status = 'ready';
        return true;
    }

    protected function logActivity($type, $message, $data = null) {
        $logData = [
            'agent_id' => $this->agentId,
            'agent_name' => $this->agentName,
            'type' => $type,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Audit log integration
        if (\function_exists('App\Services\Legacy\audit_log')) {
            audit_log("AI_AGENT_{$type}", "{$this->agentName}: {$message}");
        }

        // Potential database logging for agent specific metrics
        try {
            $this->db->execute("INSERT INTO ai_agent_logs (agent_id, log_type, message, details) VALUES (?, ?, ?, ?)", [
                $this->agentId, $type, $message, $logData['data']
            ]);
        } catch (Exception $e) {
            // Fallback to error_log if table doesn't exist yet
            error_log("AI Agent Log Error: " . $e->getMessage());
        }
    }

    public function getStatus() {
        return [
            'id' => $this->agentId,
            'name' => $this->agentName,
            'status' => $this->status,
            'config' => $this->config
        ];
    }

    public function handleError($error) {
        $this->status = 'error';
        $this->logActivity("ERROR", $error);
        return ['success' => false, 'error' => $error];
    }

    /**
     * Check if the current context has permission for this agent's actions
     */
    protected function checkPermission($action) {
        if (function_exists('has_permission')) {
            return has_permission($action);
        }
        return true; // Default to true if RBAC is not yet fully integrated
    }
}
