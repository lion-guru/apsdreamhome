<?php

/**
 * Mobile API Controller — PRUNED
 *
 * All routes now point to domain-specific split controllers:
 *   MobileAuthApiController, MobilePropertyApiController, MobileBookingApiController,
 *   MobileMLMApiController, MobileUserApiController, MobileAdminApiController, MobileSyncApiController
 *
 * This file is kept for backward compatibility (e.g. class_exists checks, DI, etc.)
 * but contains NO public endpoint methods. All helpers live in BaseController.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class MobileApiController extends BaseController
{
    use TenantAwareTrait;

    protected $apiAuthService;
    protected $syncService;
    protected $jwtService;

    public function __construct()
    {
        parent::__construct();
        $this->apiAuthService = new \App\Services\Auth\ApiAuthService();
        $this->syncService = new \App\Services\SyncService();
        $this->jwtService = new \App\Services\Auth\JWTAuthService();
    }

    /**
     * Mobile API uses JWT (stateless) — no session-based CSRF.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }
}
