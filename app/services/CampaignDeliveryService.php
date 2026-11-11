<?php

namespace App\Services;

use App\Services\Campaign\CampaignService as UnifiedCampaignService;

/**
 * @deprecated Use App\Services\Campaign\CampaignService instead
 * 
 * Campaign delivery functionality has been merged into the unified CampaignService.
 */
class CampaignDeliveryService extends UnifiedCampaignService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}