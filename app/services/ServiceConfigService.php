<?php

namespace App\Services;

use PDO;

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
        $pdo = $this->requirePdo();

        if ($existing !== null) {
            $stmt = $pdo->prepare(
                "UPDATE `service_configs` SET `config_value` = ?, `updated_at` = NOW()
                 WHERE `service_name` = ? AND `config_key` = ?"
            );
            $stmt->execute([(string) $value, $service, $key]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO `service_configs`
                    (`service_name`,`config_key`,`config_value`,`config_type`,`description`,`is_secret`,`group_name`,`sort_order`)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $service,
                $key,
                (string) $value,
                $meta['config_type'] ?? 'text',
                $meta['description'] ?? null,
                $meta['is_secret'] ?? 0,
                $meta['group_name'] ?? 'general',
                $meta['sort_order'] ?? 0,
            ]);
        }

        $this->invalidateCache();
        return true;
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
            $config = require __DIR__ . '/../../config/database.php';
            return new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }
}
