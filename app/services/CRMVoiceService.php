<?php

namespace App\Services;

use App\Services\AI\VoiceService as UnifiedVoiceService;

/**
 * CRMVoiceService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * CRM voice integration merged into unified VoiceService.
 */
class CRMVoiceService extends UnifiedVoiceService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}