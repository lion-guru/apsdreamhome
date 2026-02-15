<?php

namespace App\Services\AI\Agents;
/**
 * AgentInterface - Standard interface for all AI Agents
 */
interface AgentInterface {
    /**
     * Initialize the agent with specific configuration
     */
    public function initialize($config = []);

    /**
     * Process a task or message
     * @param mixed $input
     * @param array $context
     * @return mixed
     */
    public function process($input, $context = []);

    /**
     * Get agent status and health
     */
    public function getStatus();

    /**
     * Handle errors gracefully
     */
    public function handleError($error);
}
