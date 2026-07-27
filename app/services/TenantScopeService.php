<?php
/**
 * TenantScopeService — Global helper for tenant-scoped database queries.
 *
 * Every query that touches tenantable data should use these helpers
 * to automatically add tenant_id filtering/insertion.
 *
 * Usage:
 *   // In SELECT WHERE clause:
 *   $where[] = TenantScopeService::whereTenant('leads');
 *   $params[] = TenantScopeService::tenantId();
 *
 *   // In INSERT data:
 *   $data['tenant_id'] = TenantScopeService::tenantId();
 *
 *   // Quick check:
 *   if (TenantScopeService::isolationEnabled()) { ... }
 */

namespace App\Services;

use App\Core\Middleware\TenantContext;

class TenantScopeService
{
    /**
     * Get current tenant ID from TenantContext.
     */
    public static function tenantId(): int
    {
        return TenantContext::getId();
    }

    /**
     * Check if multi-tenant isolation is active (>1 tenant in system).
     */
    public static function isolationEnabled(): bool
    {
        return TenantContext::isMultiTenant();
    }

    /**
     * Returns a WHERE clause fragment: "table.tenant_id = ?"
     * Use in SELECT queries.
     *
     * Example:
     *   $where[] = TenantScopeService::whereTenant('leads');
     *   $params[] = TenantScopeService::tenantId();
     */
    public static function whereTenant(string $table = 'leads'): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name: {$table}");
        }
        return "{$table}.tenant_id = ?";
    }

    /**
     * Returns "AND table.tenant_id = ?" for appending to existing WHERE.
     */
    public static function andTenant(string $table = 'leads'): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name: {$table}");
        }
        return "AND {$table}.tenant_id = ?";
    }

    /**
     * Adds tenant_id to an INSERT data array.
     *
     * Example:
     *   $data = TenantScopeService::scopeInsert($data, 'leads');
     */
    public static function scopeInsert(array $data, string $table = 'leads'): array
    {
        if (self::tableHasTenantId($table)) {
            $data['tenant_id'] = self::tenantId();
        }
        return $data;
    }

    /**
     * Adds tenant_id WHERE condition to an existing WHERE array.
     *
     * Example:
     *   $where = ["l.deleted_at IS NULL"];
     *   $params = [];
     *   [$where, $params] = TenantScopeService::scopeWhere($where, $params, 'leads', 'l');
     */
    public static function scopeWhere(array $where, array $params, string $table = 'leads', string $alias = ''): array
    {
        $t = $alias ?: $table;
        $where[] = self::whereTenant($t);
        $params[] = self::tenantId();
        return [$where, $params];
    }

    /**
     * Adds tenant_id to INSERT column list and values.
     *
     * Example:
     *   $columns = ['name', 'email', 'phone'];
     *   $values = ['?', '?', '?'];
     *   [$columns, $values, $params] = TenantScopeService::scopeInsertQuery(
     *       $columns, $values, $params, 'leads'
     *   );
     */
    public static function scopeInsertQuery(array $columns, array $values, array $params, string $table = 'leads'): array
    {
        if (self::tableHasTenantId($table)) {
            $columns[] = 'tenant_id';
            $values[] = '?';
            $params[] = self::tenantId();
        }
        return [$columns, $values, $params];
    }

    /**
     * Add tenant_id filter to a raw SQL query's WHERE clause.
     * Returns modified SQL + params.
     *
     * Example:
     *   [$sql, $params] = TenantScopeService::scopeSql(
     *       "SELECT * FROM leads WHERE status = ? AND deleted_at IS NULL",
     *       ['new'],
     *       'leads'
     *   );
     */
    public static function scopeSql(string $sql, array $params, string $table = 'leads'): array
    {
        if (self::tableHasTenantId($table)) {
            // Insert tenant_id condition at the first WHERE
            $sql = preg_replace(
                '/WHERE\s/i',
                'WHERE ' . self::whereTenant($table) . ' AND ',
                $sql,
                1
            );
            array_unshift($params, self::tenantId());
        }
        return [$sql, $params];
    }

    /**
     * Check if a table has a tenant_id column (cached per request).
     */
    public static function tableHasTenantId(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) return $cache[$table];

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            $cache[$table] = false;
            return false;
        }
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

    /**
     * Get all tables that have tenant_id column.
     */
    public static function getTenantableTables(): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->query(
                "SELECT TABLE_NAME FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'tenant_id'
                 ORDER BY TABLE_NAME"
            );
            return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'TABLE_NAME');
        } catch (\Throwable $e) {
            return [];
        }
    }
}
