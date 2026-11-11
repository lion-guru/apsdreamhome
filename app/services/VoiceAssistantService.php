<?php

namespace App\Services;

use App\Services\AI\VoiceService as UnifiedVoiceService;

/**
 * VoiceAssistantService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * Voice assistant functionality merged into unified VoiceService.
 */
class VoiceAssistantService extends UnifiedVoiceService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}