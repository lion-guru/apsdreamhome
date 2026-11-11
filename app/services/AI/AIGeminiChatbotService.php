<?php

namespace App\Services\AI;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * AIGeminiChatbotService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Gemini chatbot merged into unified AIService.
 */
class AIGeminiChatbotService extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}