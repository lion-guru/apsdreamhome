<?php

namespace App\Services\Voice;

use App\Services\AI\VoiceService as UnifiedVoiceService;

/**
 * VoiceCallService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * Voice call service merged into unified VoiceService.
 */
class VoiceCallService extends UnifiedVoiceService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}