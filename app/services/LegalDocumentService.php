<?php

namespace App\Services;

/**
 * LegalDocumentService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Legal document processing merged into unified DocumentService.
 */
class LegalDocumentService extends DocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
