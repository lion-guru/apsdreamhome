<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * MlmSettings
 * Lightweight helper for reading/writing MLM configuration values.
 */

class MlmSettings
{
    public static function get(string $key, $default = null)
    {
        $conn = self::connection();
        if (!$conn) {
            return $default;
        }

        $stmt = $conn->prepare('SELECT setting_value FROM mlm_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return $default;
        }

        return $result['setting_value'];
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key, null);
        if ($value === null || $value === '') {
            return $default;
        }
        return (int) $value;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = self::get($key, null);
        if ($value === null || $value === '') {
            return $default;
        }
        return (float) $value;
    }

    public static function set(string $key, $value): bool
    {
        $conn = self::connection();
        if (!$conn) {
            return false;
        }

        $valueString = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);

        $stmt = $conn->prepare(
            'INSERT INTO mlm_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );
        return $stmt->execute([$key, $valueString]);
    }

    private static function connection(): ?PDO
    {
        return Database::getInstance()->getConnection();
    }
}
