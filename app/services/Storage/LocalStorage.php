<?php

namespace App\Services\Storage;

/**
 * LocalStorage - filesystem-backed storage adapter.
 *
 * Base path:   public/uploads/   (configurable via the STORAGE_LOCAL_PATH env var)
 * Public URL:  BASE_URL + '/uploads/...'  (configurable via STORAGE_LOCAL_URL)
 *
 * This is the default driver. It's intentionally simple - it wraps the
 * standard PHP file functions and never throws. The S3 adapter mirrors
 * this contract exactly so callers can swap them with no code changes.
 */
class LocalStorage implements StorageInterface
{
    /** @var string absolute filesystem path to the root (no trailing slash) */
    private $root;

    /** @var string URL prefix that maps onto the root (no trailing slash) */
    private $urlPrefix;

    /**
     * @param string|null $root      absolute base dir; defaults to APP_ROOT/public/uploads
     * @param string|null $urlPrefix URL prefix; defaults to "/uploads"
     */
    public function __construct(?string $root = null, ?string $urlPrefix = null)
    {
        $root = $root ?: (getenv('STORAGE_LOCAL_PATH') ?: (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/public/uploads');
        $this->root = rtrim($root, '/\\');

        $urlPrefix = $urlPrefix ?: (getenv('STORAGE_LOCAL_URL') ?: '/uploads');
        $this->urlPrefix = rtrim($urlPrefix, '/');
    }

    public function getDriver(): string
    {
        return 'local';
    }

    public function put(string $path, $contents, array $options = []): array
    {
        $rel = $this->normalizePath($path);
        if ($rel === null) {
            return ['success' => false, 'error' => 'Invalid path'];
        }
        $abs = $this->root . '/' . $rel;

        $dir = dirname($abs);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['success' => false, 'error' => 'Failed to create directory: ' . $dir];
        }

        $bytes = @file_put_contents($abs, $contents, LOCK_EX);
        if ($bytes === false) {
            return ['success' => false, 'error' => 'Failed to write file: ' . $abs];
        }
        @chmod($abs, 0644);

        return ['success' => true, 'path' => $rel, 'size' => $bytes];
    }

    public function get(string $path): ?string
    {
        $abs = $this->absolutePath($path);
        if ($abs === null || !is_file($abs)) {
            return null;
        }
        $contents = @file_get_contents($abs);
        return $contents === false ? null : $contents;
    }

    public function exists(string $path): bool
    {
        $abs = $this->absolutePath($path);
        return $abs !== null && is_file($abs);
    }

    public function delete(string $path): bool
    {
        $abs = $this->absolutePath($path);
        if ($abs === null) {
            return false;
        }
        if (!is_file($abs)) {
            return true; // idempotent
        }
        return @unlink($abs);
    }

    public function size(string $path): ?int
    {
        $abs = $this->absolutePath($path);
        if ($abs === null || !is_file($abs)) {
            return null;
        }
        $sz = @filesize($abs);
        return $sz === false ? null : (int) $sz;
    }

    public function mimeType(string $path): ?string
    {
        $abs = $this->absolutePath($path);
        if ($abs === null || !is_file($abs)) {
            return null;
        }
        $mt = @mime_content_type($abs);
        return $mt === false ? null : $mt;
    }

    public function url(string $path): ?string
    {
        $rel = $this->normalizePath($path);
        if ($rel === null) {
            return null;
        }
        return $this->urlPrefix . '/' . $rel;
    }

    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string
    {
        // Local has no signing - return the public URL.
        return $this->url($path);
    }

    public function copy(string $from, string $to): array
    {
        $src = $this->absolutePath($from);
        $rel = $this->normalizePath($to);
        if ($src === null || $rel === null) {
            return ['success' => false, 'error' => 'Invalid path'];
        }
        if (!is_file($src)) {
            return ['success' => false, 'error' => 'Source not found: ' . $from];
        }
        $dst = $this->root . '/' . $rel;
        $dir = dirname($dst);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['success' => false, 'error' => 'Failed to create dir: ' . $dir];
        }
        if (!@copy($src, $dst)) {
            return ['success' => false, 'error' => 'copy() failed'];
        }
        @chmod($dst, 0644);
        return ['success' => true, 'path' => $rel];
    }

    public function move(string $from, string $to): array
    {
        $copied = $this->copy($from, $to);
        if (empty($copied['success'])) {
            return $copied;
        }
        $this->delete($from);
        return $copied;
    }

    public function listFiles(string $prefix = ''): array
    {
        $rel = $this->normalizePath($prefix);
        if ($rel === null) {
            return [];
        }
        $base = $this->root . '/' . $rel;
        if (!is_dir($base)) {
            return [];
        }
        $out = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $abs = $file->getPathname();
            $key = ltrim(str_replace('\\', '/', substr($abs, strlen($this->root))), '/');
            $out[] = [
                'key'      => $key,
                'size'     => (int) $file->getSize(),
                'modified' => date('c', $file->getMTime()),
            ];
        }
        return $out;
    }

    /**
     * Validate the path: no leading slash, no "..", no NUL, no backslash.
     * Returns the cleaned relative path, or null on any violation.
     */
    private function normalizePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || strpos($path, "\0") !== false) {
            return null;
        }
        if ($path[0] === '/' || $path[0] === '\\') {
            return null;
        }
        // On Windows, also reject drive letters like "C:".
        if (preg_match('#^[A-Za-z]:#', $path)) {
            return null;
        }
        $path = str_replace('\\', '/', $path);
        // Reject ".." anywhere (after normalisation a "../" at any level is traversal).
        if (strpos($path, '..') !== false) {
            // Be precise: only reject ".." as a path segment.
            $segments = explode('/', $path);
            foreach ($segments as $seg) {
                if ($seg === '..') {
                    return null;
                }
            }
        }
        return ltrim($path, '/');
    }

    private function absolutePath(string $path): ?string
    {
        $rel = $this->normalizePath($path);
        if ($rel === null) {
            return null;
        }
        return $this->root . '/' . $rel;
    }
}
