<?php

namespace App\Services\Legacy\Automation;

use App\Core\App;

/**
 * WorkflowEngine - Backend engine for automated AI workflows
 */
class WorkflowEngine {
    private $db;
    private $agents = [];

    public function __construct($db = null) {
        $this->db = $db ?: App::database();
    }

    /**
     * Register an agent to be used in workflows
     */
    public function registerAgent($name, $agentInstance) {
        $this->agents[$name] = $agentInstance;
    }

    /**
     * Execute a workflow by ID
     */
    public function executeWorkflow($workflowId, $triggerData = []) {
        $workflow = $this->getWorkflow($workflowId);
        if (!$workflow) return false;

        $nodes = json_decode($workflow['nodes'], true);
        $edges = json_decode($workflow['edges'], true);

        // Start from trigger node
        $currentNode = $this->findStartNode($nodes);
        $context = $triggerData;

        while ($currentNode) {
            $result = $this->executeNode($currentNode, $context);
            $context = array_merge($context, $result['output'] ?? []);

            if ($result['status'] === 'failed' && !($currentNode['continue_on_fail'] ?? false)) {
                break;
            }

            $currentNode = $this->getNextNode($currentNode, $edges, $result['branch'] ?? 'default');

            // Handle delays
            if (isset($currentNode['delay'])) {
                sleep($currentNode['delay']);
            }
        }

        return $context;
    }

    private function executeNode($node, &$context) {
        switch ($node['type']) {
            case 'agent_action':
                $agent = $this->agents[$node['agent_name']] ?? null;
                if ($agent) {
                    return $agent->process($node['input'], $context);
                }
                break;

            case 'condition':
                $condition = $node['condition'];
                // Simple eval or logic parser
                $isMet = $this->evaluateCondition($condition, $context);
                return ['status' => 'success', 'branch' => $isMet ? 'true' : 'false'];

            case 'api_call':
                return $this->makeApiCall($node['url'], $node['method'], $context);
        }

        return ['status' => 'failed', 'error' => 'Unknown node type'];
    }

    private function getWorkflow($id) {
        return $this->db->fetch("SELECT * FROM workflows WHERE id = ?", [$id]);
    }

    private function evaluateCondition($condition, $context) {
        // Mock condition evaluation
        // e.g., "context.sentiment < 0"
        return true;
    }

    private function findStartNode($nodes) {
        foreach ($nodes as $node) {
            if ($node['type'] === 'trigger') return $node;
        }
        return null;
    }

    private function getNextNode($currentNode, $edges, $branch) {
        foreach ($edges as $edge) {
            if ($edge['from'] === $currentNode['id'] && (($edge['branch'] ?? 'default') === $branch || ($edge['branch'] ?? 'default') === 'default')) {
                return $edge['to_node']; // Simplified
            }
        }
        return null;
    }

    private function makeApiCall($url, $method, $data) {
        // Implementation for integration agent features
        return ['status' => 'success'];
    }
}
