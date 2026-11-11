<?php

namespace App\Services;

use App\Services\Lead\LeadService as UnifiedLeadService;

/**
 * LeadRoutingService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\Lead\LeadService instead
 * Lead routing & smart assignment merged into unified LeadService.
 */
class LeadRoutingService extends UnifiedLeadService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
