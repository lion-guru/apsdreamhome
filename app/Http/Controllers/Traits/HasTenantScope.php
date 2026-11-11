<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasTenantScope
 *
 * Provides multi-tenant enforcement for controllers.
 * Ensures suspended/cancelled tenants cannot perform write operations.
 */
trait HasTenantScope
{
    /**
     * Enforce tenant status — block suspended/cancelled/expired tenants.
     * Runs on every request via BaseController::__construct().
     * Skips:
     * - APS Dream Home (tenant_id=1) — superadmin tenant
     * - Public pages (no session / no tenant)
     * - Auth routes (login/register)
     */
    protected function enforceTenantStatus(): void
    {
        if (!class_exists(\App\Core\Middleware\TenantContext::class)
            || !class_exists(\App\Services\TenantEnforcement::class)) {
            return;
        }

        try {
            $tenantId = \App\Core\Middleware\TenantContext::getId();

            // APS Dream Home (id=1) is never blocked
            if ($tenantId <= 1) return;

            // Only enforce for admin/API routes (skip public front-end pages)
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if (!str_starts_with($uri, '/admin')
                && !str_starts_with($uri, '/api')
                && !str_starts_with($uri, '/apsdreamhome/admin')
                && !str_starts_with($uri, '/apsdreamhome/api')) {
                return;
            }

            // Skip auth routes (login/register must work even for suspended tenants)
            if (preg_match('#/(login|register|logout|forgot-password|reset-password|tenant-signup)$#', $uri)) {
                return;
            }

            $enforcement = \App\Services\TenantEnforcement::getInstance();
            $isWrite = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'DELETE']);

            // Block all write operations for suspended/cancelled tenants
            if ($isWrite) {
                $result = $enforcement->canPerform($tenantId, 'create_lead');
                if (!$result['allowed'] && in_array($result['code'], ['SUSPENDED', 'CANCELLED'])) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => $result['reason'], 'code' => $result['code']]);
                        exit;
                    }
                    $_SESSION['error'] = $result['reason'];
                    header('Location: /admin/dashboard');
                    exit;
                }
            }
        } catch (\Throwable $e) {
            // Enforcement failure should not break the app
            error_log('HasTenantScope::enforceTenantStatus error: ' . $e->getMessage());
        }
    }

    /**
     * Get the current tenant ID from context.
     */
    protected function getCurrentTenantId(): ?int
    {
        if (!class_exists(\App\Core\Middleware\TenantContext::class)) {
            return null;
        }
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if current tenant is the superadmin tenant (id=1).
     */
    protected function isSuperadminTenant(): bool
    {
        $tenantId = $this->getCurrentTenantId();
        return $tenantId !== null && $tenantId <= 1;
    }
}