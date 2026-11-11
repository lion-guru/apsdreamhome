<?php

namespace App\Services;

use App\Services\Lead\LeadService as UnifiedLeadService;

/**
 * LeadFollowUpService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\Lead\LeadService instead
 * Lead follow-up merged into unified LeadService.
 */
class LeadFollowUpService extends UnifiedLeadService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
