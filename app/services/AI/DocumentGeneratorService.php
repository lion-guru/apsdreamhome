<?php

namespace App\Services\AI;

use App\Services\DocumentService as UnifiedDocumentService;

/**
 * DocumentGeneratorService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * AI document generation service merged into unified DocumentService.
 */
class DocumentGeneratorService extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
