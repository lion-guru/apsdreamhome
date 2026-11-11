<?php

namespace App\Services\AI;

use App\Services\DocumentService as UnifiedDocumentService;

/**
 * DocumentAIService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * AI document analysis merged into unified DocumentService.
 */
class DocumentAIService extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
