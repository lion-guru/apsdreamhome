<?php

namespace App\Services\AI;

use App\Services\DocumentService as UnifiedDocumentService;

/**
 * DocumentGeneratorAgent - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * AI document generation agent merged into unified DocumentService.
 */
class DocumentGeneratorAgent extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
