<?php
/**
 * Secure Environment Variable Loader
 * Prevents direct access to sensitive configuration
 */

class EnvLoader {
    private static $envCache = [];

    public static function load($path = null) {
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            error_log("Environment file not found: {$path}");
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) continue;

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            // Prevent overwriting existing environment variables
            if (!getenv($key)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                self::$envCache[$key] = $value;
            }
        }

        return true;
    }

    public static function get($key, $default = null) {
        return self::$envCache[$key] ?? getenv($key) ?? $default;
    }
}

// Auto-load environment configuration
EnvLoader::load();
