<?php

namespace App\Services\Legacy\Classes;
/**
 * Secure AI Assistant Proxy Class
 * Proxies calls to the modern AIService for backward compatibility
 */
use App\Services\AIService;

class AIAssistant {
    private $aiService;

    /**
     * Constructor
     */
    public function __construct($dbSecurity = null) {
        $this->aiService = new AIService();
    }

    /**
     * Generate AI-powered suggestions based on user role
     * @param int $userId User identifier
     * @param string $role User role
     * @param array $context Additional context
     * @return array Suggestions
     */
    public function generateRoleBasedSuggestions(int $userId, string $role, array $context = []) {
        return $this->aiService->generateRoleBasedSuggestions($userId, $role, $context);
    }

    /**
     * Generate AI-powered suggestions for real estate
     * @param int $userId User identifier
     * @param array $context Contextual information for suggestions
     * @return array AI-generated suggestions
     */
    public function generateSuggestions(int $userId, array $context = []) {
        // Map to generateRoleBasedSuggestions with a default role if needed,
        // or just use the modern service directly if it has a generic method
        return $this->aiService->generateRoleBasedSuggestions($userId, $context['role'] ?? 'customer', $context);
    }

    /**
     * Proxy any other methods to the modern service
     */
    public function __call($name, $arguments) {
        if (method_exists($this->aiService, $name)) {
            return call_user_func_array([$this->aiService, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy AIAssistant proxy or modern AIService.");
    }
}
