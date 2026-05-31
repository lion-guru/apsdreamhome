<?php

namespace App\Services\AI\users;

use App\Services\AI\VoiceAgents\SiteVisitBookingAgent;
use App\Services\AI\VoiceAgents\PropertyInquiryAgent;
use App\Services\AI\VoiceAgents\LeadFollowUpAgent;

/**
 * AgentManager - Central registry and orchestrator for all AI users
 */

class AgentManager {
    private static $instance = null;
    private $users = [];
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
        // Register default users
        $this->registerAgent('whatsapp', new WhatsAppAgent());
        $this->registerAgent('lead_gen', new LeadGenerationAgent());
        $this->registerAgent('emi_collector', new EMICollectionAgent());
        $this->registerAgent('researcher', new ResearchAgent());
        $this->registerAgent('analyst', new DataAnalysisAgent());
        $this->registerAgent('content_creator', new ContentCreationAgent());

        // Register Voice AI users
        $this->registerAgent('site_visit_booking', new SiteVisitBookingAgent());
        $this->registerAgent('property_inquiry', new PropertyInquiryAgent());
        $this->registerAgent('lead_followup', new LeadFollowUpAgent());

        // Register users with workflow engine
        foreach ($this->users as $name => $agent) {
            $this->workflowEngine->registerAgent($name, $agent);
        }
    }

    public function registerAgent($name, $agentInstance) {
        $this->users[$name] = $agentInstance;
    }

    public function getAgent($name) {
        return $this->users[$name] ?? null;
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
     * Get all registered users and their status
     */
    public function getAllAgentsStatus() {
        $statuses = [];
        foreach ($this->users as $name => $agent) {
            $statuses[$name] = $agent->getStatus();
        }
        return $statuses;
    }
}
