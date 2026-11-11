<?php

namespace App\Services;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * PropertyChatbotService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Property-specific chatbot merged into unified AIService.
 */
class PropertyChatbotService extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}