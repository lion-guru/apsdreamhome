<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

/**
 * MlmSettings - Centralized MLM configuration reader/writer
 * 
 * Eliminates duplicated inline queries across 7+ services
 * that all do: SELECT setting_value FROM mlm_settings WHERE setting_key = ?
 */
class MlmSettings
{
    use ServiceTenantTrait;

    private static ?array $cache = null;
    private static int $cacheTTL = 300;
    private static int $cacheTime = 0;

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null || (time() - self::$cacheTime) > self::$cacheTTL) {
            self::loadCache();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function getFloat(string $key, float $default = 0): float
    {
        $value = self::get($key);
        return $value !== null ? (float) $value : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);
        return $value !== null ? (int) $value : $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) return $default;
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on']);
    }

    public static function set(string $key, $value): bool
    {
        try {
            $db = Database::getInstance();
            $conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
            $stmt = $conn->prepare("INSERT INTO mlm_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $result = $stmt->execute([$key, (string)$value, (string)$value]);
            if ($result) {
                self::$cache[$key] = (string)$value;
            }
            return $result;
        } catch (\Throwable $e) {
            error_log("MlmSettings::set error: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll(): array
    {
        if (self::$cache === null || (time() - self::$cacheTime) > self::$cacheTTL) {
            self::loadCache();
        }
        return self::$cache ?? [];
    }

    public static function clearCache(): void
    {
        self::$cache = null;
        self::$cacheTime = 0;
    }

    private static function loadCache(): void
    {
        self::$cache = [];
        try {
            $db = Database::getInstance();
            $conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
            $stmt = $conn->query("SELECT setting_key, setting_value FROM mlm_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
            self::$cacheTime = time();
        } catch (\Throwable $e) {
            error_log("MlmSettings::loadCache error: " . $e->getMessage());
        }
    }
}
