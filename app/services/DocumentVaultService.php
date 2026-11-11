<?php

namespace App\Services;

/**
 * DocumentVaultService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Document vault/storage merged into unified DocumentService.
 */
class DocumentVaultService extends DocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
