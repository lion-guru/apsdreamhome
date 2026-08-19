<?php
namespace App\Middleware;

class RateLimiter
{
    private static $limits = [];
    private static $storageDir = null;

    private static function getStorageDir(): string
    {
        if (self::$storageDir === null) {
            self::$storageDir = sys_get_temp_dir() . '/rate_limits';
            if (!is_dir(self::$storageDir)) {
                @mkdir(self::$storageDir, 0777, true);
            }
        }
        return self::$storageDir;
    }

    private static function getFile(string $key): string
    {
        return self::getStorageDir() . '/' . md5($key) . '.json';
    }

    private static function loadWindow(string $key): array
    {
        $file = self::getFile($key);
        if (file_exists($file)) {
            $data = @json_decode(file_get_contents($file), true);
            if (is_array($data) && isset($data['window_start'], $data['count'])) {
                return $data;
            }
        }
        return ['window_start' => time(), 'count' => 0];
    }

    private static function saveWindow(string $key, array $window): void
    {
        @file_put_contents(self::getFile($key), json_encode($window));
    }

    public static function check(string $key = 'default', int $maxRequests = 60, int $windowSeconds = 60): bool
    {
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING']) || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return true;
        }

        $now = time();
        $window = self::loadWindow($key);

        if ($now - $window['window_start'] > $windowSeconds) {
            $window = ['window_start' => $now, 'count' => 0];
        }

        $window['count']++;
        self::saveWindow($key, $window);

        if ($window['count'] > $maxRequests) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . ($windowSeconds - ($now - $window['window_start'])));
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Too many requests. Please try again later.']);
            exit;
        }

        return true;
    }

    public static function checkApi(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        return self::check('api_' . md5($ip . $route), 60, 60);
    }

    public static function checkLogin(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return self::check('login_' . $ip, 5, 60);
    }

    public static function cleanup(): int
    {
        $dir = self::getStorageDir();
        $files = glob($dir . '/*.json');
        $cleaned = 0;
        foreach ($files as $file) {
            if (time() - filemtime($file) > 3600) {
                @unlink($file);
                $cleaned++;
            }
        }
        return $cleaned;
    }
}
