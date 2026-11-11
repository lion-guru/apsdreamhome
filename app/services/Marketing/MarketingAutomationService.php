<?php

namespace App\Services\Marketing;

use App\Services\Campaign\CampaignService as UnifiedCampaignService;

/**
 * @deprecated Use App\Services\Campaign\CampaignService instead
 * 
 * Marketing automation functionality has been merged into the unified CampaignService.
 */
class MarketingAutomationService extends UnifiedCampaignService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}