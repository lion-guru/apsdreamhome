<?php

namespace App\Services\AI;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * ConversationalAIService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Conversational AI merged into unified AIService.
 */
class ConversationalAIService extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}