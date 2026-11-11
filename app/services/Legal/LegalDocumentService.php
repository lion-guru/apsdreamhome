<?php

namespace App\Services\Legal;

use App\Services\DocumentService as UnifiedDocumentService;

/**
 * LegalDocumentService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Legal document service merged into unified DocumentService.
 */
class LegalDocumentService extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
