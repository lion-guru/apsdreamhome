<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * System Configuration Service
 * Centralized configuration management for all system settings
 * All settings stored in DB, configurable via Admin UI
 */
class SystemConfigService
{
    use ServiceTenantTrait;

    private $db;
    private static array $cache = [];
    private static array $configCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = "syscfg_{$key}";
        
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$key];
            if ($tid > 1) $params[] = $tid;

            $row = $this->db->fetchOne(
                "SELECT config_value, config_type FROM system_configs WHERE config_key = ?" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$row) {
                return $default;
            }

            $value = $this->castValue($row['config_value'], $row['config_type'] ?? 'string');
            self::$cache["syscfg_{$key}"] = $value;
            return $value;
        } catch (\Exception $e) {
            error_log("SystemConfigService::get error for {$key}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Get multiple configuration values
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Get all configurations (optionally filtered by group)
     */
    public function getAll(?string $group = null): array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [];
            if ($tid > 1) $params[] = $tid;

            $sql = "SELECT config_key, config_value, config_type, config_group, description, is_public, is_editable FROM system_configs WHERE 1=1" . $tenantSql;
            
            if ($group) {
                $sql .= " AND config_group = ?";
                $params[] = $group;
            }
            
            $sql .= " ORDER BY config_group, sort_order, config_key";
            
            if ($tid > 1) $params[] = $tid;

            $rows = $this->db->fetchAll($sql, $params);
            
            $result = [];
            foreach ($rows as $row) {
                $result[$row['config_key']] = [
                    'value' => $this->castValue($row['config_value'], $row['config_type']),
                    'type' => $row['config_type'],
                    'group' => $row['config_group'],
                    'description' => $row['description'],
                    'is_public' => (bool)$row['is_public'],
                    'is_editable' => (bool)$row['is_editable'],
                ];
            }
            return $result;
        } catch (\Exception $e) {
            error_log("SystemConfigService::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get configurations grouped by group
     */
    public function getGrouped(): array
    {
        $all = $this->getAll();
        $grouped = [];
        foreach ($all as $key => $config) {
            $group = $config['group'] ?? 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][$key] = $config;
        }
        return $grouped;
    }

    /**
     * Set configuration value
     */
    public function set(string $key, $value, string $type = 'string', ?string $group = null, ?string $description = null): array
    {
        try {
            $tid = $this->tenantId();
            
            // Validate value based on type
            $validatedValue = $this->validateValue($value, $type);
            if ($validatedValue === false) {
                return ['success' => false, 'message' => "Invalid value for type {$type}"];
            }

            $configValue = $this->serializeValue($validatedValue, $type);
            $configType = $type;

            // Check if exists
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $checkParams = [$key];
            if ($tid > 1) $checkParams[] = $this->tenantId();

            $existing = $this->db->fetchOne(
                "SELECT id FROM system_configs WHERE config_key = ?" . $tenantSql . " LIMIT 1",
                array_merge([$key], $tid > 1 ? [$this->tenantId()] : [])
            );

            if ($existing) {
                $params = [$configValue, $configType];
                if ($group !== null) $params[] = $group;
                if ($description !== null) $params[] = $description;
                $params[] = $key;
                if ($tid > 1) $params[] = $this->tenantId();

                $sql = "UPDATE system_configs SET config_value = ?, config_type = ?" .
                    ($group !== null ? ", config_group = ?" : "") .
                    ($description !== null ? ", description = ?" : "") .
                    ", updated_at = NOW() WHERE config_key = ?" . $tenantSql;
                
                $this->db->execute($sql, $params);
            } else {
                $params = [$key, $configValue, $configType];
                if ($group !== null) $params[] = $group;
                if ($description !== null) $params[] = $description;
                if ($tid > 1) $params[] = $this->tenantId();

                $sql = "INSERT INTO system_configs (config_key, config_value, config_type" . 
                    ($group !== null ? ", config_group" : "") .
                    ($description !== null ? ", description" : "") .
                    ($tid > 1 ? ", tenant_id" : "") .
                    ", created_at, updated_at) VALUES (?, ?, ?" .
                    ($group !== null ? ", ?" : "") .
                    ($description !== null ? ", ?" : "") .
                    ($tid > 1 ? ", ?" : "") .
                    ", NOW(), NOW())";
                
                $this->db->execute($sql, $params);
            }

            // Clear cache
            unset(self::$cache["syscfg_{$key}"]);

            // Log audit
            $this->logAudit($key, $value);

            return ['success' => true, 'message' => 'Configuration updated'];
        } catch (\Exception $e) {
            error_log("SystemConfigService::set error for {$key}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update configuration'];
        }
    }

    /**
     * Set multiple configurations at once
     */
    public function setMultiple(array $configs): array
    {
        $results = [];
        foreach ($configs as $key => $config) {
            $value = $config['value'] ?? null;
            $type = $config['type'] ?? 'string';
            $group = $config['group'] ?? null;
            $description = $config['description'] ?? null;
            
            $results[$key] = $this->set($key, $value, $type, $group, $description);
        }
        return $results;
    }

    /**
     * Delete configuration
     */
    public function delete(string $key): array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            
            $params = [$key];
            if ($tid > 1) $params[] = $this->tenantId();
            
            $deleted = $this->db->execute(
                "DELETE FROM system_configs WHERE config_key = ?" . $tenantSql,
                array_merge([$key], $tid > 1 ? [$this->tenantId()] : [])
            )->rowCount() > 0;

            if ($deleted) {
                unset(self::$cache["syscfg_{$key}"]);
                return ['success' => true, 'message' => 'Configuration deleted'];
            }
            
            return ['success' => false, 'message' => 'Configuration not found'];
        } catch (\Exception $e) {
            error_log("SystemConfigService::delete error for {$key}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete configuration'];
        }
    }

    /**
     * Get available configuration groups
     */
    public function getGroups(): array
    {
        try {
            $tid = $this->tenantId();
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT config_group FROM system_configs" . ($tid > 1 ? " WHERE tenant_id = ?" : "") . " ORDER BY config_group",
                $tid > 1 ? [$this->tenantId()] : []
            );
            return array_column($rows, 'config_group');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get configuration metadata (for UI forms)
     */
    public function getMetadata(string $key): ?array
    {
        try {
            $tid = $this->tenantId();
            $row = $this->db->fetchOne(
                "SELECT config_key, config_type, config_group, description, is_public, is_editable, validation_rules, options FROM system_configs WHERE config_key = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                array_merge([$key], $tid > 1 ? [$this->tenantId()] : [])
            );
            
            if (!$row) return null;
            
            return [
                'key' => $row['config_key'],
                'type' => $row['config_type'],
                'group' => $row['config_group'],
                'description' => $row['description'],
                'is_public' => (bool)$row['is_public'],
                'is_editable' => (bool)$row['is_editable'],
                'validation_rules' => $row['validation_rules'] ? json_decode($row['validation_rules'], true) : [],
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cast value based on type
     */
    private function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            case 'json':
            case 'array':
                return json_decode($value, true);
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Serialize value for storage
     */
    private function serializeValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return $value ? '1' : '0';
            case 'json':
            case 'array':
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            default:
                return (string)$value;
        }
    }

    /**
     * Validate value based on type
     */
    private function validateValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : false;
            case 'float':
            case 'double':
                return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : false;
            case 'json':
            case 'array':
                if (is_array($value)) return $value;
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : false;
                }
                return false;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : false;
            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * Log configuration change for audit
     */
    private function logAudit(string $key, $newValue): void
    {
        try {
            $tid = $this->tenantId();
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            
            $this->db->execute(
                "INSERT INTO system_config_audit (config_key, old_value, new_value, changed_by, changed_at, tenant_id) 
                 SELECT config_key, config_value, ?, ?, NOW(), ? FROM system_configs WHERE config_key = ?" . ($this->tenantId() > 1 ? " AND tenant_id = ?" : ""),
                array_merge([$this->serializeValue($newValue, 'string'), $userId, $this->tenantId(), $key], $tid > 1 ? [$this->tenantId()] : [])
            );
        } catch (\Exception $e) {
            // Silent fail for audit
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Get all configurations for export
     */
    public function export(): array
    {
        return $this->getAll();
    }

    /**
     * Import configurations
     */
    public function import(array $configs, bool $overwrite = true): array
    {
        $results = [];
        foreach ($configs as $key => $config) {
            if (!$overwrite && $this->get($key) !== null) {
                $results[$key] = ['success' => false, 'message' => 'Already exists'];
                continue;
            }
            
            $results[$key] = $this->set(
                $key,
                $config['value'] ?? null,
                $config['type'] ?? 'string',
                $config['group'] ?? null,
                $config['description'] ?? null
            );
        }
        return $results;
    }
}

