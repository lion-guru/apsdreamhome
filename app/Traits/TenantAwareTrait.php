<?php
namespace App\Traits;

/**
 * TenantAwareTrait — Reusable tenant enforcement + usage tracking for controllers.
 *
 * Usage in any controller:
 *   use \App\Traits\TenantAwareTrait;
 *   // Then call:
 *   $this->tenantEnforce('create_property');  // blocks if over limit
 *   $this->tenantTrackUsage('properties');     // increments counter
 *   $this->tenantId();                         // current tenant ID
 *   $this->tenantWhere();                      // returns " AND tenant_id = ?" with param
 */
trait TenantAwareTrait
{
    /**
     * Check if current tenant can perform an action. Returns true if allowed.
     * If blocked, sets flash error and returns false.
     */
    protected function tenantEnforce(string $action): bool
    {
        if (!class_exists('\App\Core\Middleware\TenantContext') || !class_exists('\App\Services\TenantEnforcement')) {
            return true;
        }

        try {
            $tenantId = \App\Core\Middleware\TenantContext::getId();
            if ($tenantId <= 1) return true; // APS Dream Home always allowed

            $enforcement = \App\Services\TenantEnforcement::getInstance();
            $result = $enforcement->canPerform($tenantId, $action);

            if (!$result['allowed']) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['error'] = $result['reason'];
                }
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            error_log('TenantAwareTrait::tenantEnforce error: ' . $e->getMessage());
            return true; // fail open
        }
    }

    /**
     * Track usage metric for current tenant.
     */
    protected function tenantTrackUsage(string $metric): void
    {
        if (!class_exists('\App\Core\Middleware\TenantContext') || !class_exists('\App\Services\TenantService')) {
            return;
        }

        try {
            $tenantId = \App\Core\Middleware\TenantContext::getId();
            if ($tenantId > 1) {
                \App\Services\TenantService::getInstance()->incrementUsage($tenantId, $metric);
            }
        } catch (\Throwable $e) {
            error_log('TenantAwareTrait::tenantTrackUsage error: ' . $e->getMessage());
        }
    }

    /**
     * Get current tenant ID.
     */
    protected function tenantId(): int
    {
        if (!class_exists('\App\Core\Middleware\TenantContext')) return 1;
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Get tenant-scoped WHERE clause for queries.
     * Returns ['AND tenant_id = ?', [$tenantId]] or ['', []] for superadmin tenant.
     */
    protected function tenantWhere(): array
    {
        $tid = $this->tenantId();
        if ($tid <= 1) return ['', []];
        return [' AND tenant_id = ?', [$tid]];
    }

    /**
     * Get tenant-scoped INSERT columns.
     * Returns ['tenant_id' => $tid] or [] for superadmin tenant.
     */
    protected function tenantInsertData(): array
    {
        $tid = $this->tenantId();
        if ($tid <= 1) return [];
        return ['tenant_id' => $tid];
    }
}
