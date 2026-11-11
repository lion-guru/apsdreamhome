<?php

namespace App\Services\AI;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * VoiceProviderManager - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Voice provider management merged into unified AIService.
 */
class VoiceProviderManager extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}