<?php

namespace App\Services\AI;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * AIManager - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * AI Manager merged into unified AIService.
 */
class AIManager extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}