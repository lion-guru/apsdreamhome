<?php

namespace App\Services;

/**
 * DocumentLockerService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Document locker/storage merged into unified DocumentService.
 */
class DocumentLockerService extends DocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
