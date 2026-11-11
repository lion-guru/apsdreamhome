<?php

namespace App\Services;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * WhatsAppAIBot - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * WhatsApp AI bot merged into unified AIService.
 */
class WhatsAppAIBot extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}