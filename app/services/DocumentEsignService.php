<?php

namespace App\Services;

/**
 * DocumentEsignService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Document e-signing merged into unified DocumentService.
 */
class DocumentEsignService extends DocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
