<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * Feature Flag Service
 * Centralized feature toggling system for gradual rollouts, A/B testing, and runtime config
 */
class FeatureFlagService
{
    use ServiceTenantTrait;

    private $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $key, ?array $context = null): bool
    {
        $flag = $this->getFlag($key);
        if (!$flag) return false;
        if (!$flag['enabled']) return false;

        // Check rollout percentage
        if ($flag['rollout_percentage'] < 100) {
            $hash = $this->getConsistentHash($key, $context);
            if ($hash >= $flag['rollout_percentage']) {
                return false;
            }
        }

        // Check targeting rules
        if (!empty($flag['targeting_rules'])) {
            if (!$this->evaluateTargetingRules($flag['targeting_rules'], $context)) {
                return false;
            }
        }

        // Check date range
        if ($flag['start_date'] && strtotime($flag['start_date']) > time()) return false;
        if ($flag['end_date'] && strtotime($flag['end_date']) < time()) return false;

        return true;
    }

    /**
     * Get flag configuration
     */
    public function getFlag(string $key): ?array
    {
        $cacheKey = "feature_flag_{$key}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$key];
            if ($tid > 1) $params[] = $tid;

            $row = $this->db->fetchOne(
                "SELECT * FROM feature_flags WHERE flag_key = ?" . $tenantSql . " LIMIT 1",
                $params
            );

            if (!$row) return null;

            $result = [
                'id' => (int)$row['id'],
                'key' => $row['flag_key'],
                'name' => $row['name'],
                'description' => $row['description'],
                'enabled' => (bool)$row['enabled'],
                'rollout_percentage' => (int)$row['rollout_percentage'],
                'targeting_rules' => $row['targeting_rules'] ? json_decode($row['targeting_rules'], true) : [],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];

            self::$cache["feature_flag_{$key}"] = $result;
            return $result;
        } catch (\Exception $e) {
            error_log("FeatureFlagService::getFlag error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all feature flags
     */
    public function getAllFlags(): array
    {
        try {
            $tid = $this->tenantId();
            $rows = $this->db->fetchAll(
                "SELECT * FROM feature_flags" . ($tid > 1 ? " WHERE tenant_id = ?" : "") . " ORDER BY flag_group, flag_key",
                $tid > 1 ? [$this->tenantId()] : []
            );

            return array_map(function($row) {
                return [
                    'id' => (int)$row['id'],
                    'key' => $row['flag_key'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'group' => $row['flag_group'],
                    'enabled' => (bool)$row['enabled'],
                    'rollout_percentage' => (int)$row['rollout_percentage'],
                    'targeting_rules' => $row['targeting_rules'] ? json_decode($row['targeting_rules'], true) : [],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ];
            }, $rows);
        } catch (\Exception $e) {
            error_log("FeatureFlagService::getAllFlags error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create or update a feature flag
     */
    public function setFlag(string $key, array $data): array
    {
        try {
            $tid = $this->tenantId();

            $flagData = [
                'flag_key' => $key,
                'name' => $data['name'] ?? $key,
                'description' => $data['description'] ?? '',
                'flag_group' => $data['group'] ?? 'general',
                'enabled' => !empty($data['enabled']) ? 1 : 0,
                'rollout_percentage' => (int)($data['rollout_percentage'] ?? 100),
                'targeting_rules' => isset($data['targeting_rules']) ? json_encode($data['targeting_rules']) : null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ];

            if ($tid > 1) {
                $flagData['tenant_id'] = $this->tenantId();
            }

            // Check if exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM feature_flags WHERE flag_key = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$key, $this->tenantId()] : [$key]
            );

            if ($existing) {
                $flagData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->update('feature_flags', $flagData, 'flag_key = ?' . ($tid > 1 ? ' AND tenant_id = ?' : ''), $tid > 1 ? [$key, $this->tenantId()] : [$key]);
            } else {
                $flagData['created_at'] = date('Y-m-d H:i:s');
                $flagData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->insert('feature_flags', $flagData);
            }

            unset(self::$cache["feature_flag_{$key}"]);
            return ['success' => true, 'message' => 'Feature flag updated'];
        } catch (\Exception $e) {
            error_log("FeatureFlagService::setFlag error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update feature flag'];
        }
    }

    /**
     * Delete a feature flag
     */
    public function deleteFlag(string $key): array
    {
        try {
            $tid = $this->tenantId();
            $deleted = $this->db->execute(
                "DELETE FROM feature_flags WHERE flag_key = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$key, $this->tenantId()] : [$key]
            )->rowCount() > 0;

            if ($deleted) {
                unset(self::$cache["feature_flag_{$key}"]);
                return ['success' => true, 'message' => 'Feature flag deleted'];
            }
            return ['success' => false, 'message' => 'Feature flag not found'];
        } catch (\Exception $e) {
            error_log("FeatureFlagService::deleteFlag error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete feature flag'];
        }
    }

    /**
     * Toggle feature flag
     */
    public function toggleFlag(string $key): array
    {
        $flag = $this->getFlag($key);
        if (!$flag) return ['success' => false, 'message' => 'Flag not found'];

        return $this->setFlag($key, ['enabled' => !$flag['enabled']]);
    }

    /**
     * Get consistent hash for rollout
     */
    private function getConsistentHash(string $key, ?array $context): int
    {
        $seed = $key . ($context['user_id'] ?? $context['session_id'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
        return abs(crc32($seed)) % 100;
    }

    /**
     * Evaluate targeting rules
     */
    private function evaluateTargetingRules(array $rules, ?array $context): bool
    {
        if (!$context) return false;

        foreach ($rules as $rule) {
            $field = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? 'equals';
            $value = $rule['value'] ?? '';
            $contextValue = $context[$field] ?? null;

            $match = false;
            switch ($operator) {
                case 'equals':
                    $match = $contextValue == $value;
                    break;
                case 'not_equals':
                    $match = $contextValue != $value;
                    break;
                case 'contains':
                    $match = is_string($contextValue) && strpos($contextValue, $value) !== false;
                    break;
                case 'in':
                    $match = in_array($contextValue, (array)$value);
                    break;
                case 'not_in':
                    $match = !in_array($contextValue, (array)$value);
                    break;
                case 'greater_than':
                    $match = is_numeric($contextValue) && $contextValue > $value;
                    break;
                case 'less_than':
                    $match = is_numeric($contextValue) && $contextValue < $value;
                    break;
            }

            if (!$match) return false;
        }

        return true;
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        self::$cache = [];
    }
}