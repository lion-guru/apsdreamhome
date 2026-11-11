<?php

namespace App\Services\AI;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * AIAdvancedAgent - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Advanced AI agent merged into unified AIService.
 */
class AIAdvancedAgent extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}