<?php

namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

/**
 * Centralized service configuration store.
 *
 * Reads/writes the `service_configs` table. All configs are loaded once
 * per request into an in-memory cache so repeated reads are free.
 *
 * Usage:
 *   ServiceConfigService::get('razorpay', 'key_id', 'fallback');
 *   ServiceConfigService::isTestMode('twilio');
 *   ServiceConfigService::getApiConfig('razorpay');
 *   ServiceConfigService::getAll();
 *   ServiceConfigService::getAllGroups();
 *
 * Contract:
 *   - NEVER throws. All methods return safe defaults on failure.
 *   - Fallback chain: DB value → $default parameter.
 *   - Table may not exist yet — gracefully returns defaults.
 */
class ServiceConfigService
{
    use ServiceTenantTrait;

    private static ?self $instance = null;

    /** @var PDO|null */
    private $pdo;

    /** @var array<string, array<string, array>> service_name => [key => row] */
    private array $cache = [];

    /** @var bool */
    private bool $loaded = false;

    /** @var bool|null whether table exists */
    private static ?bool $tableExists = null;

    private function __construct()
    {
        $this->pdo = $this->resolvePdo();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Public API ──

    /**
     * Get a single config value.
     */
    public function get(string $service, string $key, mixed $default = null): mixed
    {
        $row = $this->find($service, $key);
        if ($row === null) {
            return $default;
        }
        return $this->castValue($row['config_value'], $row['config_type'] ?? 'text');
    }

    /**
     * Static alias for get() — backward compatible.
     */
    public static function getVal(string $service, string $key, mixed $default = null): mixed
    {
        return self::getInstance()->get($service, $key, $default);
    }

    /**
     * Get all configs, optionally filtered by service name.
     *
     * @return array<string, array<string, array>>
     */
    public function getAll(?string $service = null): array
    {
        $this->loadAll();
        if ($service !== null) {
            return $this->cache[$service] ?? [];
        }
        return $this->cache;
    }

    /**
     * Set (upsert) a single config value.
     */
    public function set(string $service, string $key, mixed $value, array $meta = []): bool
    {
        $this->loadAll();
        $existing = $this->find($service, $key);
        $oldValue = $existing['config_value'] ?? null;
        $pdo = $this->requirePdo();

        if ($existing !== null) {
            $stmt = $pdo->prepare(
                "UPDATE `service_configs` SET `config_value` = ?, `updated_at` = NOW()
                 WHERE `service_name` = ? AND `config_key` = ?" . $this->tenantSql()
            );
            $stmt->execute([(string) $value, $service, $key]);
        } else {
            $insertCols = ['service_name','config_key','config_value','config_type','description','is_secret','group_name','sort_order'];
            $insertVals = str_repeat('?,', count($insertCols) - 1) . '?';
            $insertCols = array_merge($insertCols, array_keys($this->tenantInsertData()));
            $insertVals .= $this->tenantInsertData() ? ', ?' : '';
            $stmt = $pdo->prepare(
                "INSERT INTO `service_configs`
                    (`" . implode('`,`', $insertCols) . "`)
                  VALUES ($insertVals)"
            );
            $stmt->execute(array_merge([
                $service,
                $key,
                (string) $value,
                $meta['config_type'] ?? 'text',
                $meta['description'] ?? null,
                $meta['is_secret'] ?? 0,
                $meta['group_name'] ?? 'general',
                $meta['sort_order'] ?? 0,
            ], array_values($this->tenantInsertData())));
        }

        $this->invalidateCache();
        $this->auditChange($service, $key, $oldValue, (string) $value);
        return true;
    }

    /**
     * Best-effort audit trail for config changes (who/when/old/new).
     * Never throws — audit must not break the write it records.
     */
    private function auditChange(string $service, string $key, ?string $oldValue, string $newValue): void
    {
        try {
            if ($oldValue !== null && $oldValue === $newValue) {
                return; // no-op writes are not history
            }
            if ($this->pdo === null) {
                return;
            }
            $chk = $this->pdo->query("SHOW TABLES LIKE 'service_config_audit'");
            if ($chk->fetch() === false) {
                return; // audit table not installed yet
            }
            $changedBy = 0;
            $changedByName = null;
            if (isset($_SESSION) && is_array($_SESSION)) {
                $changedBy = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
                $changedByName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? null;
            }
            $tid = $this->tenantId();
            $cols = ['tenant_id', 'service_name', 'config_key', 'old_value', 'new_value', 'changed_by', 'changed_by_name', 'ip_address'];
            $stmt = $this->pdo->prepare(
                "INSERT INTO `service_config_audit`
                 (`tenant_id`, `service_name`, `config_key`, `old_value`, `new_value`, `changed_by`, `changed_by_name`, `ip_address`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $tid,
                $service,
                $key,
                $oldValue,
                $newValue,
                $changedBy > 0 ? $changedBy : null,
                $changedByName,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[ServiceConfigService] auditChange failed: ' . $e->getMessage());
        }
    }

    /**
     * Log a manual/out-of-band change (e.g. bulk package updates that mirror
     * a config knob)._feat: same audit trail as set(). Never throws.
     */
    public function auditManual(string $service, string $key, ?string $oldValue, string $newValue, string $notes = ''): void
    {
        try {
            if ($this->pdo === null) {
                return;
            }
            $chk = $this->pdo->query("SHOW TABLES LIKE 'service_config_audit'");
            if ($chk->fetch() === false) {
                return;
            }
            $changedBy = 0;
            $changedByName = null;
            if (isset($_SESSION) && is_array($_SESSION)) {
                $changedBy = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
                $changedByName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? null;
            }
            $tid = $this->tenantId();
            $stmt = $this->pdo->prepare(
                "INSERT INTO `service_config_audit`
                 (`tenant_id`, `service_name`, `config_key`, `old_value`, `new_value`, `changed_by`, `changed_by_name`, `ip_address`, `notes`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            // notes column may not exist on older installs — retry without it
            try {
                $stmt->execute([$tid, $service, $key, $oldValue, $newValue, $changedBy > 0 ? $changedBy : null, $changedByName, $_SERVER['REMOTE_ADDR'] ?? null, $notes !== '' ? $notes : null]);
            } catch (\Throwable $e) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO `service_config_audit`
                     (`tenant_id`, `service_name`, `config_key`, `old_value`, `new_value`, `changed_by`, `changed_by_name`, `ip_address`)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$tid, $service, $key, $oldValue, $newValue, $changedBy > 0 ? $changedBy : null, $changedByName, $_SERVER['REMOTE_ADDR'] ?? null]);
            }
        } catch (\Throwable $e) {
            error_log('[ServiceConfigService] auditManual failed: ' . $e->getMessage());
        }
    }

    /**
     * Read recent audit rows (newest first). Never throws.
     */
    public function getAuditHistory(?string $service = null, int $limit = 50): array
    {
        try {
            if ($this->pdo === null) {
                return [];
            }
            $sql = "SELECT * FROM `service_config_audit`";
            $params = [];
            if ($service !== null && $service !== '') {
                $sql .= " WHERE `service_name` = ?";
                $params[] = $service;
            }
            $sql .= " ORDER BY `id` DESC LIMIT " . max(1, min($limit, 200));
            // tenant scoping intentionally omitted: audit is cross-tenant admin record
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Set multiple configs for a service at once.
     *
     * @param array<string, mixed> $configs  key => value pairs
     */
    public function setBulk(string $service, array $configs): bool
    {
        foreach ($configs as $key => $value) {
            if (is_array($value)) {
                $this->set($service, $key, $value['value'] ?? '', $value);
            } else {
                $this->set($service, $key, $value);
            }
        }
        return true;
    }

    /**
     * Delete a config entry.
     */
    public function delete(string $service, string $key): bool
    {
        $pdo = $this->requirePdo();
        $stmt = $pdo->prepare("DELETE FROM `service_configs` WHERE `service_name` = ? AND `config_key` = ?");
        $stmt->execute([$service, $key]);
        $this->invalidateCache();
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all configs in a given group.
     *
     * @return array<int, array>
     */
    public function getGroup(string $group): array
    {
        $this->loadAll();
        $result = [];
        foreach ($this->cache as $service => $keys) {
            foreach ($keys as $row) {
                if (($row['group_name'] ?? 'general') === $group) {
                    $result[] = $row;
                }
            }
        }
        return $result;
    }

    /**
     * Get all distinct group names with their config rows.
     *
     * @return array<string, array<int, array>>
     */
    public function getAllGroups(): array
    {
        $this->loadAll();
        $groups = [];
        foreach ($this->cache as $service => $keys) {
            foreach ($keys as $row) {
                $g = $row['group_name'] ?? 'general';
                $groups[$g][] = $row;
            }
        }
        return $groups;
    }

    /**
     * Convenience: is the given service in test mode?
     */
    public function isTestMode(string $service): bool
    {
        $val = $this->get($service, 'test_mode', '1');
        if (is_bool($val)) {
            return $val;
        }
        return in_array(strtolower(trim((string) $val)), ['1', 'true', 'yes'], true);
    }

    /**
     * Return all non-empty configs for a service as a flat key=>value array.
     *
     * @return array<string, mixed>
     */
    public function getApiConfig(string $service): array
    {
        $this->loadAll();
        $rows = $this->cache[$service] ?? [];
        $out = [];
        foreach ($rows as $row) {
            $val = $this->castValue($row['config_value'], $row['config_type'] ?? 'text');
            if ($val !== null && $val !== '') {
                $out[$row['config_key']] = $val;
            }
        }
        return $out;
    }

    /**
     * Encrypt a value for storage (simple base64; production should use sodium).
     */
    public static function encryptValue(string $value): string
    {
        return base64_encode($value);
    }

    /**
     * Decrypt a stored value.
     */
    public static function decryptValue(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);
        return $decoded !== false ? $decoded : $encoded;
    }

    // ── Private helpers ──

    private function find(string $service, string $key): ?array
    {
        $this->loadAll();
        return $this->cache[$service][$key] ?? null;
    }

    private function loadAll(): void
    {
        if ($this->loaded) {
            return;
        }
        if (!$this->tableExists()) {
            $this->loaded = true;
            return;
        }

        try {
            $stmt = $this->pdo()->query(
                "SELECT * FROM `service_configs` ORDER BY `group_name`, `service_name`, `sort_order`"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->cache = [];
            foreach ($rows as $row) {
                $s = $row['service_name'];
                $k = $row['config_key'];
                $this->cache[$s][$k] = $row;
            }
        } catch (\Throwable $e) {
            error_log('[ServiceConfigService] loadAll failed: ' . $e->getMessage());
            $this->cache = [];
        }

        $this->loaded = true;
    }

    private function invalidateCache(): void
    {
        $this->loaded = false;
        $this->cache = [];
    }

    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        return match ($type) {
            'boolean' => $value === '1' || strtolower($value) === 'true',
            'number'  => is_numeric($value) ? (float) $value : $value,
            'json'    => json_decode($value, true) ?? $value,
            default   => $value,
        };
    }

    private function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }
        if (!$this->pdo) {
            self::$tableExists = false;
            return false;
        }
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'service_configs'");
            self::$tableExists = $stmt->fetch() !== false;
        } catch (\Throwable $e) {
            self::$tableExists = false;
        }
        return self::$tableExists;
    }

    private function pdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->resolvePdo();
        }
        return $this->requirePdo();
    }

    private function requirePdo(): PDO
    {
        if ($this->pdo === null) {
            throw new \RuntimeException('ServiceConfigService: database unavailable');
        }
        return $this->pdo;
    }

    private function resolvePdo(): ?PDO
    {
        try {
            if (class_exists(\App\Core\Database\Database::class)) {
                $db = \App\Core\Database\Database::getInstance();
                if (method_exists($db, 'getConnection')) {
                    return $db->getConnection();
                }
                if (method_exists($db, 'getPdo')) {
                    return $db->getPdo();
                }
            }
        } catch (\Throwable $e) {
        // fallback below
        error_log($e->getMessage());
        }

        try {
            return \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
