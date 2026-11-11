<?php

namespace App\Services\Loan;

use App\Services\DocumentService as UnifiedDocumentService;

/**
 * LoanDocumentService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Loan document processing merged into unified DocumentService.
 */
class LoanDocumentService extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
