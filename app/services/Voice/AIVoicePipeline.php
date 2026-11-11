<?php

namespace App\Services\Voice;

use App\Services\AI\VoiceService as UnifiedAIService;

/**
 * AIVoicePipeline - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * AI voice pipeline merged into unified VoiceService.
 */
class AIVoicePipeline extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}