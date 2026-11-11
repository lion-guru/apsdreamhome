<?php

namespace App\Services;

use App\Services\Lead\LeadService as UnifiedLeadService;

/**
 * LeadWorkflowService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\Lead\LeadService instead
 * Lead workflow automation merged into unified LeadService.
 */
class LeadWorkflowService extends UnifiedLeadService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
