<?php
namespace App\Helpers;

use App\Core\Middleware\TenantContext;
use App\Core\Database\Database;

/**
 * CronTenantHelper — Tenant-aware execution for CLI/cron scripts.
 *
 * Provides utilities for cron scripts to iterate over active tenants
 * and set tenant context so services and raw SQL queries respect
 * tenant data isolation.
 *
 * Usage:
 *   $tenants = CronTenantHelper::getActiveTenants($pdo);
 *   foreach ($tenants as $t) {
 *       CronTenantHelper::setTenantContext((int)$t['id'], $pdo);
 *       // ... process tenant-specific data ...
 *   }
 */
class CronTenantHelper
{
    /**
     * Get all active (non-deleted) tenants from the tenants table.
     * Returns empty array if table doesn't exist or has no rows.
     *
     * @param \PDO $pdo
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public static function getActiveTenants(\PDO $pdo): array
    {
        try {
            $stmt = $pdo->query("SELECT id, name, slug FROM tenants WHERE status IN ('active', 'trial') AND deleted_at IS NULL ORDER BY id ASC");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                return $rows;
            }
        } catch (\Throwable $e) {
            error_log('[CronTenantHelper] tenants table not available: ' . $e->getMessage());
        }

        // Fallback: single-tenant mode (APS Dream Home)
        return [['id' => 1, 'name' => 'APS Dream Home', 'slug' => 'aps']];
    }

    /**
     * Set tenant context for the current process.
     * Calls TenantContext::setById() and also sets a global constant
     * CRON_TENANT_ID for raw SQL queries that need it.
     *
     * @param int  $tenantId
     * @param \PDO $pdo
     * @return void
     */
    public static function setTenantContext(int $tenantId, \PDO $pdo): void
    {
        // Set for framework services that use TenantContext::getId()
        TenantContext::setById($tenantId, $pdo);

        // Define global constant for raw SQL fallback
        if (!defined('CRON_TENANT_ID')) {
            define('CRON_TENANT_ID', $tenantId);
        }
    }

    /**
     * Get the current tenant ID from context.
     * Falls back to CRON_TENANT_ID constant if TenantContext not available.
     */
    public static function getCurrentTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return defined('CRON_TENANT_ID') ? CRON_TENANT_ID : 1;
        }
    }

    /**
     * Build a tenant-scoped WHERE clause fragment.
     * Returns [' AND tenant_id = ?', [id]] or ['', []] for tenant 1 (default/superadmin).
     */
    public static function tenantWhere(): array
    {
        $tid = self::getCurrentTenantId();
        if ($tid <= 1) return ['', []];
        return [' AND tenant_id = ?', [$tid]];
    }

    /**
     * Append tenant_id to an INSERT column/value list.
     * Given ['col1', 'col2'] and ['val1', 'val2'],
     * returns columns ['col1', 'col2', 'tenant_id'] and params ['val1', 'val2', id].
     *
     * Does NOT add tenant_id for tenant 1 (default/superadmin).
     */
    public static function tenantInsertData(array $columns, array $values): array
    {
        $tid = self::getCurrentTenantId();
        if ($tid <= 1) return [$columns, $values];

        $columns[] = 'tenant_id';
        $values[]  = $tid;
        return [$columns, $values];
    }

    /**
     * Print a tenant header banner to stdout.
     */
    public static function printTenantBanner(string $name, int $id): void
    {
        $border = str_repeat('═', 55);
        echo "╔{$border}╗" . PHP_EOL;
        echo "║  Tenant: {$name} (ID: {$id})" . str_repeat(' ', max(0, 52 - strlen($name) - strlen((string)$id))) . "║" . PHP_EOL;
        echo "╚{$border}╝" . PHP_EOL;
    }
}
