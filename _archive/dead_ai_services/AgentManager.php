<?php

namespace App\Services\AI\Agents;

use App\Services\AI\VoiceAgents\SiteVisitBookingAgent;
use App\Services\AI\VoiceAgents\PropertyInquiryAgent;
use App\Services\AI\VoiceAgents\LeadFollowUpAgent;
use App\Services\AI\Agents\specialized\LeadGenerationAgent;
use App\Services\AI\Agents\specialized\EMICollectionAgent;
use App\Services\AI\Agents\specialized\ResearchAgent;
use App\Services\AI\Agents\specialized\DataAnalysisAgent;
use App\Services\AI\Agents\specialized\ContentCreationAgent;

/**
 * AgentManager - Central registry and orchestrator for all AI agents
 */

class AgentManager {
    private static $instance = null;
    private $agents = [];

    private function __construct() {
        $this->initializeDefaultAgents();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeDefaultAgents() {
        $this->registerAgent('whatsapp', new WhatsAppAgent());
        $this->registerAgent('lead_gen', new LeadGenerationAgent());
        $this->registerAgent('emi_collector', new EMICollectionAgent());
        $this->registerAgent('researcher', new ResearchAgent());
        $this->registerAgent('analyst', new DataAnalysisAgent());
        $this->registerAgent('content_creator', new ContentCreationAgent());

        $this->registerAgent('site_visit_booking', new SiteVisitBookingAgent());
        $this->registerAgent('property_inquiry', new PropertyInquiryAgent());
        $this->registerAgent('lead_followup', new LeadFollowUpAgent());
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
        } catch (\Exception $e) {
            return $agent->handleError($e->getMessage());
        }
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
