<?php

namespace App\Services;

use App\Services\Lead\LeadService as UnifiedLeadService;

/**
 * LeadScoringService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\Lead\LeadService instead
 * Lead scoring & deal probability merged into unified LeadService.
 */
class LeadScoringService extends UnifiedLeadService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
