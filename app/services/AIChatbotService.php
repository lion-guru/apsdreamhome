<?php

namespace App\Services;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * AIChatbotService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * AI chatbot functionality merged into unified AIService.
 */
class AIChatbotService extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}