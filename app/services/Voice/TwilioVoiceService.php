<?php

namespace App\Services\Voice;

use App\Services\AI\VoiceService as UnifiedVoiceService;

/**
 * TwilioVoiceService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * Twilio voice integration merged into unified VoiceService.
 */
class TwilioVoiceService extends UnifiedVoiceService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}