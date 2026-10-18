<?php
/**
 * Local Filesystem Storage Provider.
 *
 * Stores files on the local filesystem (office PC, NAS, mounted volume).
 * Supports all standard storage operations with symlink/presigned URL generation.
 *
 * Configuration (via service_configs / env):
 *   local_fs.root_path    required — absolute path to storage root (outside webroot recommended)
 *   local_fs.base_url     optional — public base URL for generating public URLs (e.g. https://files.example.com)
 *   local_fs.public_path  optional — web-accessible sub-path if root_path has a public subdirectory
 */
namespace App\Services\Storage;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Core\Database\Database;
use App\Services\ServiceConfigService;
use Exception;

class LocalStorage
{
    private string $rootPath;
    private ?string $baseUrl;
    private ?string $publicPath;

    public function __construct(array $config = [])
    {
        $this->rootPath = rtrim($config['root_path'] ?? '', '/');
        $this->baseUrl = $config['base_url'] ?? null;
        $this->publicPath = $config['public_path'] ?? null;

        if (empty($this->rootPath) || !is_dir($this->rootPath)) {
            @mkdir($this->rootPath, 0755, true);
        }
    }

    public function getDriver(): string
    {
        return 'local';
    }

    public function isConfigured(): bool
    {
        return is_dir($this->rootPath) && is_writable($this->rootPath);
    }

    public function listFiles(string $prefix = ''): array
    {
        $dir = $this->resolvePath($prefix);
        if (!is_dir($dir)) return [];

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relPath = $prefix ? ltrim(str_replace($this->rootPath, '', $file->getPathname()), '/') : ltrim(str_replace($this->rootPath, '', $file->getPathname()), '/');
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $relPath,
                    'size' => $file->getSize(),
                    'last_modified' => date('c', $file->getMTime()),
                    'mime' => mime_content_type($file->getPathname()) ?? 'application/octet-stream',
                ];
            }
        }
        return $files;
    }

    public function put(string $path, $contents, array $options = []): array
    {
        $fullPath = $this->resolvePath($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $written = file_put_contents($fullPath, $contents);
        if ($written === false) {
            return ['success' => false, 'error' => 'Failed to write file'];
        }

        return ['success' => true, 'path' => $path];
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->resolvePath($path);
        if (!is_file($fullPath)) return null;
        return file_get_contents($fullPath);
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->resolvePath($path);
        if (!is_file($fullPath)) return false;
        return unlink($fullPath);
    }

    public function url(string $path): ?string
    {
        if ($this->baseUrl) {
            return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        }
        // Fallback: serve via a controller route
        return BASE_URL . '/admin/cloud-storage/download?path=' . rawurlencode($path);
    }

    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string
    {
        // Generate a signed token for temporary access
        // For now, return direct URL with a short-lived token in query
        // In production, implement signed tokens with HMAC
        return $this->url($path) . '?token=' . hash_hmac('sha256', $path . '|' . (time() + 3600), $_ENV['APP_KEY'] ?? 'fallback') . '&expires=' . (time() + 3600);
    }

    public function exists(string $path): bool
    {
        return is_file($this->resolvePath($path));
    }

    public function size(string $path): ?int
    {
        $fullPath = $this->resolvePath($path);
        if (!is_file($fullPath)) return null;
        return filesize($fullPath);
    }

    public function mimeType(string $path): ?string
    {
        $fullPath = $this->resolvePath($path);
        return mime_content_type($fullPath) ?? 'application/octet-stream';
    }

    private function resolvePath(string $path): string
    {
        $path = ltrim($path, '/');
        return rtrim($this->rootPath, '/') . '/' . $path;
    }
}