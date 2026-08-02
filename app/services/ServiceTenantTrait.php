<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class ServiceTenantTrait
{
    protected static function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    protected static function tenantWhere(string &$sql, array &$params): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tid;
        }
    }

    protected static function tenantInsertData(array &$columns, array &$values): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $columns[] = 'tenant_id';
            $values[] = $tid;
        }
    }
}
