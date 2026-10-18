<?php
/**
 * Google Cloud Storage adapter using REST API (no SDK dependency).
 *
 * Auth: Service Account JSON key (file path or JSON string in config).
 * REST API v1: https://cloud.google.com/storage/docs/json_api/v1
 *
 * Configuration (via service_configs / env):
 *   gcs.project_id       required
 *   gcs.key_file         path to service account JSON file (optional if key_json provided)
 *   gcs.key_json         raw JSON string of service account (alternative to key_file)
 *   gcs.bucket           default bucket name
 *   gcs.region           optional (default: auto)
 *   gcs.test_mode        bool (default true) — skip real calls in test mode
 */
namespace App\Services\Storage;

use App\Core\Database\Database;
use App\Services\ServiceConfigService;
use Exception;

class GCSStorage
{
    private string $projectId;
    private string $bucket;
    private string $accessToken = '';
    private int $tokenExpires = 0;
    private string $keyJson = '';
    private bool $testMode;

    public function __construct(array $config = [])
    {
        $this->projectId = $config['project_id'] ?? '';
        $this->bucket = $config['bucket'] ?? '';
        $this->testMode = (bool)($config['test_mode'] ?? true);

        // Load service account credentials
        if (!empty($config['key_json'])) {
            $this->keyJson = $config['key_json'];
        } elseif (!empty($config['key_file']) && file_exists($config['key_file'])) {
            $this->keyJson = file_get_contents($config['key_file']);
        }

        if ($this->keyJson) {
            $key = json_decode($this->keyJson, true);
            if (isset($key['client_email'], $key['private_key'])) {
                $this->refreshToken();
            }
        }
    }

    public function getDriver(): string
    {
        return 'gcs';
    }

    public function isConfigured(): bool
    {
        return !empty($this->projectId) && !empty($this->bucket) && !empty($this->keyJson);
    }

    /**
     * Get OAuth2 access token using service account JWT (RFC 7523).
     */
    private function refreshToken(): void
    {
        if ($this->testMode) {
            $this->accessToken = 'test-token';
            $this->tokenExpires = time() + 3600;
            return;
        }

        $key = json_decode($this->keyJson, true);
        if (!isset($key['client_email'], $key['private_key'])) {
            throw new Exception('Invalid GCS service account JSON');
        }

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claim = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $headerB64 = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $claimB64 = rtrim(strtr(base64_encode(json_encode($claim)), '+/', '-_'), '=');
        $signingInput = $headerB64 . '.' . $claimB64;

        $privateKey = openssl_pkey_get_private($key['private_key']);
        if (!$privateKey) {
            throw new Exception('Invalid GCS private key');
        }
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        $jwt = $signingInput . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        if (empty($data['access_token'])) {
            throw new Exception('GCS token exchange failed: ' . json_encode($data));
        }
        $this->accessToken = $data['access_token'];
        $this->tokenExpires = time() + (int)($data['expires_in'] ?? 3600) - 60;
    }

    private function ensureToken(): void
    {
        if ($this->testMode) return;
        if (time() >= $this->tokenExpires || empty($this->accessToken)) {
            $this->refreshToken();
        }
    }

    public function listFiles(string $prefix = ''): array
    {
        if ($this->testMode) {
            return [
                ['name' => 'test-file.txt', 'size' => 1234, 'path' => 'test/test-file.txt', 'last_modified' => date('c')],
            ];
        }
        $this->ensureToken();

        $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucket}/o";
        $params = ['prefix' => $prefix, 'maxResults' => 1000];
        $url .= '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        if (empty($data['items'])) return [];

        $files = [];
        foreach ($data['items'] as $item) {
            $files[] = [
                'name' => basename($item['name']),
                'path' => $item['name'],
                'size' => (int)($item['size'] ?? 0),
                'last_modified' => $item['updated'] ?? date('c'),
                'mime' => $item['contentType'] ?? 'application/octet-stream',
            ];
        }
        return $files;
    }

    public function put(string $path, $contents, array $options = []): array
    {
        if ($this->testMode) {
            return ['success' => true, 'path' => $path];
        }
        $this->ensureToken();

        $url = "https://storage.googleapis.com/upload/storage/v1/b/{$this->bucket}/o?uploadType=media&name=" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_PUT => true,
            CURLOPT_INFILE => fopen('php://memory', 'r'),
            CURLOPT_INFILESIZE => strlen($contents),
            CURLOPT_READFUNCTION => function ($ch, $fd, $length) use ($contents) {
                return $contents;
            },
            CURLOPT_INFILESIZE => strlen($contents),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: ' . ($options['content_type'] ?? 'application/octet-stream'),
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        // Fix: need to use a proper upload approach
        curl_close($ch);
        // Use multipart/resumable for production; here simple implementation:
        $ch = curl_init("https://storage.googleapis.com/upload/storage/v1/b/{$this->bucket}/o?uploadType=media&name=" . rawurlencode($path));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contents,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: ' . ($options['content_type'] ?? 'application/octet-stream'),
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        return ['success' => !empty($data['name']), 'path' => $data['name'] ?? $path, 'raw' => $data];
    }

    public function get(string $path): ?string
    {
        if ($this->testMode) return "test content for $path";
        $this->ensureToken();

        $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucket}/o/" . rawurlencode($path) . "?alt=media";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp !== false ? $resp : null;
    }

    public function delete(string $path): bool
    {
        if ($this->testMode) return true;
        $this->ensureToken();

        $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucket}/o/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    public function url(string $path): ?string
    {
        return "https://storage.googleapis.com/{$this->bucket}/" . rawurlencode($path);
    }

    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string
    {
        // GCS signed URLs require more complex signing; return direct URL for now
        return $this->url($path);
    }

    public function exists(string $path): bool
    {
        if ($this->testMode) return true;
        $this->ensureToken();
        $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucket}/o/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }

    public function size(string $path): ?int
    {
        if ($this->testMode) return 1024;
        $this->ensureToken();
        $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucket}/o/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return $size > 0 ? (int)$size : null;
    }

    public function mimeType(string $path): ?string
    {
        return mime_content_type($path) ?? 'application/octet-stream';
    }
}