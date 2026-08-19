<?php
namespace App\Services;

use App\Core\Database\Database;

class MlmSettings
{
    private static $cache;

    public static function getFloat(string $key, float $default = 0): float
    {
        $value = self::get($key);
        return $value !== null ? (float) $value : $default;
    }

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, $value): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO mlm_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2");
            $stmt->execute(['key' => $key, 'value' => (string)$value, 'value2' => (string)$value]);
            self::$cache[$key] = (string)$value;
            return true;
        } catch (\Throwable $e) {
            error_log("MlmSettings::set error: " . $e->getMessage());
            return false;
        }
    }

    private static function loadCache(): void
    {
        self::$cache = [];
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT setting_key, setting_value FROM mlm_settings");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            error_log("MlmSettings::loadCache error: " . $e->getMessage());
        }
    }
}
