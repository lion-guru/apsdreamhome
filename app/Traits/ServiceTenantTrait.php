<?php
namespace App\Traits;

/**
 * ServiceTenantTrait — Tenant scoping for Service classes using raw PDO.
 *
 * Services bypass Model layer entirely (raw PDO), so Model::$tenantScoped doesn't apply.
 * This trait provides lightweight helpers to add tenant_id to every SQL operation.
 *
 * Usage in any service:
 *   use \App\Traits\ServiceTenantTrait;
 *
 *   // In SELECT/UPDATE/DELETE:
 *   $sql = "SELECT * FROM leads WHERE status = ?" . $this->tenantSql();
 *   $params = [$status];
 *   if ($this->tenantId() > 1) $params[] = $this->tenantId();
 *
 *   // In INSERT:
 *   $columns = array_merge(['name', 'status'], array_keys($this->tenantInsertData()));
 *   $values  = array_merge([$name, $status], array_values($this->tenantInsertData()));
 *
 *   // Quick check:
 *   if ($this->tenantId() > 1) { // apply scoping }
 */
trait ServiceTenantTrait
{
    private ?int $_serviceTenantId = null;

    /**
     * Get current tenant ID (cached per request).
     */
    protected function tenantId(): int
    {
        if ($this->_serviceTenantId !== null) {
            return $this->_serviceTenantId;
        }

        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                $this->_serviceTenantId = (int) \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                $this->_serviceTenantId = 1;
            }
        } else {
            $this->_serviceTenantId = 1;
        }

        return $this->_serviceTenantId;
    }

    /**
     * WHERE clause fragment for tenant scoping.
     * Returns " AND tenant_id = ?" for tenants > 1, empty string for superadmin.
     */
    protected function tenantSql(): string
    {
        return $this->tenantId() > 1 ? " AND tenant_id = {$this->tenantId()}" : "";
    }

    /**
     * Named parameter version for prepared statements.
     * Returns [' AND tenant_id = :stid', ':stid' => $tid] or ['', []]
     */
    protected function tenantNamedSql(): array
    {
        $tid = $this->tenantId();
        if ($tid <= 1) return ['', []];
        return [' AND tenant_id = :stid', [':stid' => $tid]];
    }

    /**
     * INSERT data — returns ['tenant_id' => $tid] or [] for superadmin.
     * Use: array_merge($existingColumns, array_keys($this->tenantInsertData()))
     */
    protected function tenantInsertData(): array
    {
        return $this->tenantId() > 1 ? ['tenant_id' => $this->tenantId()] : [];
    }

    /**
     * Bind tenant_id to a prepared statement (for parameterized queries).
     * Call after $stmt = $pdo->prepare(...).
     */
    protected function tenantBind($stmt): void
    {
        if ($this->tenantId() > 1) {
            $stmt->bindParam(':stid', $this->tenantId(), \PDO::PARAM_INT);
        }
    }

    /**
     * Get tenant_id column name and value pair for dynamic INSERT.
     * Returns 'tenant_id' => $tid for building column/value arrays.
     */
    protected function tenantColumn(): array
    {
        return $this->tenantId() > 1 ? ['tenant_id' => $this->tenantId()] : [];
    }

    /**
     * Check if tenant scoping is active (tenant > 1).
     */
    protected function isTenantScoped(): bool
    {
        return $this->tenantId() > 1;
    }
}
