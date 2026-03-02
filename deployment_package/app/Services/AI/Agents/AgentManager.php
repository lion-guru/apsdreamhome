<?php

namespace App\Services\AI\Agents;
/**
 * AgentManager - Central registry and orchestrator for all AI Agents
 */

class AgentManager {
    private static $instance = null;
    private $agents = [];
    private $workflowEngine;

    private function __construct() {
        $this->workflowEngine = new WorkflowEngine();
        $this->initializeDefaultAgents();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeDefaultAgents() {
        // Register default agents
        $this->registerAgent('whatsapp', new WhatsAppAgent());
        $this->registerAgent('lead_gen', new LeadGenerationAgent());
        $this->registerAgent('emi_collector', new EMICollectionAgent());
        $this->registerAgent('researcher', new ResearchAgent());
        $this->registerAgent('analyst', new DataAnalysisAgent());
        $this->registerAgent('content_creator', new ContentCreationAgent());

        // Register agents with workflow engine
        foreach ($this->agents as $name => $agent) {
            $this->workflowEngine->registerAgent($name, $agent);
        }
    }

    public function registerAgent($name, $agentInstance) {
        $this->agents[$name] = $agentInstance;
    }

    public function getAgent($name) {
        return $this->agents[$name] ?? null;
    }

    /**
     * Dispatch a task to a specific agent
     */
    public function dispatch($agentName, $input, $context = []) {
        $agent = $this->getAgent($agentName);
        if (!$agent) {
            return ['success' => false, 'error' => "Agent '$agentName' not found"];
        }

        try {
            return $agent->process($input, $context);
        } catch (Exception $e) {
            return $agent->handleError($e->getMessage());
        }
    }

    /**
     * Run an automated workflow
     */
    public function runWorkflow($workflowId, $triggerData = []) {
        return $this->workflowEngine->executeWorkflow($workflowId, $triggerData);
    }

    /**
     * Get all registered agents and their status
     */
    public function getAllAgentsStatus() {
        $statuses = [];
        foreach ($this->agents as $name => $agent) {
            $statuses[$name] = $agent->getStatus();
        }
        return $statuses;
    }
}
