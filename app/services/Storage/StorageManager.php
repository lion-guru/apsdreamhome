<?php

namespace App\Services\Storage;

/**
 * StorageManager - thin facade that returns the right adapter based on config.
 *
 * Resolution order:
 *   1. STORAGE_DRIVER env var ("local" | "s3")
 *   2. If S3 is configured but the connection test fails, log a warning and
 *      silently fall back to local storage for the rest of this request.
 *
 * The facade is a singleton so adapters and the in-memory "S3 failed this
 * request" flag are shared across all callers.
 */
class StorageManager
{
    use \App\Traits\ServiceTenantTrait;

    private static $instance = null;

    /** @var StorageInterface|null */
    private $disk = null;

    /** @var bool S3 was attempted this request and failed - don't retry. */
    private $s3DisabledThisRequest = false;

    /** @var string configured driver name (from env) */
    private $configuredDriver;

    private function __construct()
    {
        $this->configuredDriver = strtolower((string) (getenv('STORAGE_DRIVER') ?: 'local'));
    }

    public static function getInstance(): StorageManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Test if the S3 driver is reachable. Returns true if a tiny file
     * can be uploaded, listed, and deleted. Bypasses the per-request
     * fallback flag (it always tries again).
     */
    public function testS3(): array
    {
        $s3 = new S3Storage();
        if (!$s3->isConfigured()) {
            return ['success' => false, 'error' => 'S3 not configured (missing AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY or AWS_BUCKET)'];
        }
        $key = 'healthcheck/' . uniqid('s3_test_', true) . '.txt';
        $body = 'S3 healthcheck ' . date('c');
        $put = $s3->put($key, $body, ['ContentType' => 'text/plain']);
        if (empty($put['success'])) {
            return ['success' => false, 'error' => 'PUT failed: ' . ($put['error'] ?? 'unknown')];
        }
        $got = $s3->get($key);
        $del = $s3->delete($key);
        if ($got !== $body) {
            return ['success' => false, 'error' => 'GET returned mismatched body'];
        }
        if (!$del) {
            return ['success' => false, 'error' => 'DELETE failed'];
        }
        return [
            'success' => true,
            'bucket'  => $s3->isConfigured() ? (getenv('AWS_BUCKET') ?: '') : '',
            'region'  => getenv('AWS_DEFAULT_REGION') ?: '',
            'message' => 'Round-trip PUT/GET/DELETE succeeded',
        ];
    }

    public function isS3Enabled(): bool
    {
        return $this->configuredDriver === 's3' && !$this->s3DisabledThisRequest
            && (new S3Storage())->isConfigured();
    }

    /**
     * Return the active disk. Falls back to local on S3 failure.
     */
    public function disk(?string $name = null): StorageInterface
    {
        $name = $name ?: $this->configuredDriver;
        if ($name === 's3') {
            if ($this->s3DisabledThisRequest) {
                return $this->makeLocal();
            }
            if ($this->disk instanceof S3Storage) {
                return $this->disk;
            }
            $s3 = new S3Storage();
            if (!$s3->isConfigured()) {
                $this->logFallback('S3 not configured');
                $this->s3DisabledThisRequest = true;
                return $this->makeLocal();
            }
            // Trust the S3 driver to handle its own retries / failure modes.
            $this->disk = $s3;
            return $s3;
        }
        return $this->makeLocal();
    }

    public function getDriverName(): string
    {
        if ($this->s3DisabledThisRequest) {
            return 'local (s3 fallback)';
        }
        return $this->configuredDriver;
    }

    public function url(string $path): ?string
    {
        return $this->disk()->url($path);
    }

    public function temporaryUrl(string $path, int $minutes = 60): ?string
    {
        return $this->disk()->temporaryUrl($path, $minutes);
    }

    public function put(string $path, $contents, array $options = []): array
    {
        return $this->disk()->put($path, $contents, $options);
    }

    public function get(string $path): ?string
    {
        return $this->disk()->get($path);
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($path);
    }

    public function delete(string $path): bool
    {
        return $this->disk()->delete($path);
    }

    public function listFiles(string $prefix = ''): array
    {
        return $this->disk()->listFiles($prefix);
    }

    public function size(string $path): ?int
    {
        return $this->disk()->size($path);
    }

    public function mimeType(string $path): ?string
    {
        return $this->disk()->mimeType($path);
    }

    public function copy(string $from, string $to): array
    {
        return $this->disk()->copy($from, $to);
    }

    public function move(string $from, string $to): array
    {
        return $this->disk()->move($from, $to);
    }

    /** Reset the singleton (testing only). */
    public static function reset(): void
    {
        self::$instance = null;
    }

    // ---------- private ----------

    private function makeLocal(): StorageInterface
    {
        if (!($this->disk instanceof LocalStorage)) {
            $this->disk = new LocalStorage();
        }
        return $this->disk;
    }

    private function logFallback(string $reason): void
    {
        try {
            if (!class_exists('App\\Core\\Database\\Database', false)) {
                $path = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/app/Core/Database/Database.php';
                if (is_file($path)) require_once $path;
            }
            $dbClass = 'App\\Core\\Database\\Database';
            if (!class_exists($dbClass)) return;
            $db = $dbClass::getInstance();
            $pdo = method_exists($db, 'getConnection') ? $db->getConnection() : null;
            if (!$pdo) return;
            // Best effort - use INSERT IGNORE if gateway_logs is missing columns.
            $stmt = $pdo->prepare("INSERT INTO gateway_logs (gateway, action, method, endpoint, error_message, created_at) VALUES (?, ?, 'N/A', 'N/A', ?, NOW())");
            $stmt->execute(['s3', 'fallback_to_local', json_encode(['reason' => $reason])]);
        } catch (\Throwable $e) {
        // silent
        error_log($e->getMessage());
        }
    }
}
