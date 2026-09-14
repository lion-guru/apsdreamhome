<?php
namespace App\Http\Controllers\Traits;

use App\Core\Middleware\TenantContext;

/**
 * TenantAwareTrait â€” Reusable tenant scoping for controllers.
 *
 * Usage:
 *   use TenantAwareTrait;
 *   // Then call $this->tenantId() or $this->tenantScope('leads', $query)
 */
trait TenantAwareTrait
{
    /**
     * Get current tenant ID.
     */
    protected function tenantId(): int
    {
        return TenantContext::getId();
    }

    /**
     * Get current tenant data.
     */
    protected function tenant(): ?array
    {
        return TenantContext::get();
    }

    /**
     * Get tenant branding for views.
     */
    protected function tenantBranding(): array
    {
        return [
            'tenant_name'       => TenantContext::getName(),
            'tenant_logo'       => TenantContext::getLogo(),
            'tenant_primary'    => TenantContext::getColors()['primary'],
            'tenant_secondary'  => TenantContext::getColors()['secondary'],
            'tenant_id'         => TenantContext::getId(),
        ];
    }

    /**
     * Add tenant_id WHERE clause to a query.
     * Only adds if the table has a tenant_id column.
     */
    protected function tenantScope(string $table, string $query, array $params = []): array
    {
        if ($this->tableHasTenantId($table)) {
            $query = str_replace('WHERE', "WHERE {$table}.tenant_id = ? AND", $query);
            array_unshift($params, $this->tenantId());
        }
        return [$query, $params];
    }

    /**
     * Check if a table has a tenant_id column.
     */
    protected function tableHasTenantId(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) return $cache[$table];

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE 'tenant_id'");
            $stmt->execute();
            $cache[$table] = $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            $cache[$table] = false;
        }

        return $cache[$table];
    }
}?>