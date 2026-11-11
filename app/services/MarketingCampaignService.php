<?php

namespace App\Services;

use App\Services\Campaign\CampaignService as UnifiedCampaignService;

/**
 * @deprecated Use App\Services\Campaign\CampaignService instead
 * 
 * Marketing campaign functionality has been merged into the unified CampaignService.
 */
class MarketingCampaignService extends UnifiedCampaignService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
    }
}