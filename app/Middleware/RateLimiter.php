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
        // S264: param bypass is dev-only (production throttle hole otherwise).
        $__isProd = (defined('APP_ENV') && APP_ENV === 'production') || (getenv('APP_ENV') === 'production');
        if (((isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING'])) && !$__isProd) || (defined('APP_ENV') && APP_ENV === 'testing')) {
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
            $retryAfter = max(1, $windowSeconds - ($now - $window['window_start']));
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . $retryAfter);

            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (stripos($accept, 'text/html') !== false && stripos($accept, 'application/json') === false) {
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Rate Limit Exceeded - APS Dream Home</title>' .
                     '<meta name="viewport" content="width=device-width, initial-scale=1">' .
                     '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">' .
                     '</head><body class="bg-light d-flex align-items-center justify-content-center min-vh-100">' .
                     '<div class="card shadow-sm p-4 text-center" style="max-width: 440px; border-radius: 16px;">' .
                     '<div class="mb-3 text-warning"><i class="fas fa-hourglass-half fa-3x"></i></div>' .
                     '<h4 class="fw-bold mb-2">Too Many Requests</h4>' .
                     '<p class="text-muted small mb-3">Please wait ' . (int)$retryAfter . ' seconds before trying again.</p>' .
                     '<a href="javascript:history.back()" class="btn btn-outline-primary btn-sm px-4">Go Back</a>' .
                     '</div></body></html>';
                exit;
            }

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
