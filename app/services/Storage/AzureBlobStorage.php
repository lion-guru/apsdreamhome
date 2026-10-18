<?php
/**
 * Azure Blob Storage adapter using REST API (no SDK dependency).
 *
 * Auth: Shared Key authorization or SAS token.
 * REST API: https://docs.microsoft.com/en-us/rest/api/storageservices/
 *
 * Configuration (via service_configs / env):
 *   azure_blob.connection_string    required — DefaultEndpointsProtocol=https;AccountName=xxx;AccountKey=xxx;EndpointSuffix=core.windows.net
 *   azure_blob.container            required — Container name
 *   azure_blob.account_name         optional — Storage account name (extracted from connection string if not provided)
 *   azure_blob.account_key          optional — Storage account key (extracted from connection string if not provided)
 *   azure_blob.test_mode            optional bool (default true) — skip real API calls
 */
namespace App\Services\Storage;

use App\Core\Database\Database;
use App\Services\ServiceConfigService;
use Exception;

class AzureBlobStorage
{
    private string $accountName = '';
    private string $accountKey = '';
    private string $container = '';
    private bool $testMode;

    public function __construct(array $config = [])
    {
        $connStr = $config['connection_string'] ?? '';
        $this->container = $config['container'] ?? '';
        $this->testMode = (bool)($config['test_mode'] ?? true);

        if (!empty($connStr)) {
            $this->parseConnectionString($connStr);
        } else {
            $this->accountName = $config['account_name'] ?? '';
            $this->accountKey = $config['account_key'] ?? '';
        }
    }

    private function parseConnectionString(string $connStr): void
    {
        $parts = explode(';', $connStr);
        foreach ($parts as $part) {
            $part = trim($part);
            if (str_starts_with($part, 'AccountName=')) {
                $this->accountName = substr($part, 12);
            } elseif (str_starts_with($part, 'AccountKey=')) {
                $this->accountKey = substr($part, 11);
            } elseif (str_starts_with($part, 'EndpointSuffix=')) {
                // Could use for non-standard endpoints
            }
        }
    }

    public function getDriver(): string
    {
        return 'azure';
    }

    public function isConfigured(): bool
    {
        return !empty($this->accountName) && !empty($this->accountKey) && !empty($this->container);
    }

    /**
     * Generate Shared Key authorization header (Azure Blob Storage auth).
     * Reference: https://docs.microsoft.com/en-us/rest/api/storageservices/authorize-with-shared-key
     */
    private function getAuthorizationHeader(string $verb, string $canonicalizedResource, array $headers = []): string
    {
        $contentLength = isset($headers['Content-Length']) && $headers['Content-Length'] !== '' ? $headers['Content-Length'] : '';
        $contentType = $headers['Content-Type'] ?? '';
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $canonicalizedHeaders = '';
        
        // Standard headers to sign
        $headersToSign = [
            'Content-Encoding' => '',
            'Content-Language' => '',
            'Content-Length' => $contentLength,
            'Content-MD5' => '',
            'Content-Type' => $contentType,
            'Date' => '',
            'If-Modified-Since' => '',
            'If-Match' => '',
            'If-None-Match' => '',
            'If-Unmodified-Since' => '',
            'Range' => '',
        ];

        // x-ms-* headers
        $msHeaders = [];
        foreach ($headers as $k => $v) {
            if (str_starts_with(strtolower($k), 'x-ms-')) {
                $msHeaders[strtolower($k)] = $v;
            }
        }
        ksort($msHeaders);
        foreach ($msHeaders as $k => $v) {
            $canonicalizedHeaders .= "$k:$v\n";
        }

        // Canonicalized resource
        $canonicalizedResource = "/{$this->accountName}/{$this->container}" . ($this->resourcePath ?? '');

        // String to sign
        $stringToSign = implode("\n", [
            $this->verb ?? 'GET',
            $headersToSign['Content-Encoding'],
            $headersToSign['Content-Language'],
            $headersToSign['Content-Length'],
            $headersToSign['Content-MD5'],
            $headersToSign['Content-Type'],
            $headersToSign['Date'],
            $headersToSign['If-Modified-Since'],
            $headersToSign['If-Match'],
            $headersToSign['If-None-Match'],
            $headersToSign['If-Unmodified-Since'],
            $headersToSign['Range'],
            $canonicalizedHeaders,
            "/{$this->accountName}/{$this->container}/",
        ]);

        // Sign with HMAC-SHA256
        $key = base64_decode($this->accountKey);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $key, true));

        return "SharedKey {$this->accountName}:{$signature}";
    }

    public function listFiles(string $prefix = ''): array
    {
        if ($this->testMode) {
            return [
                ['name' => 'test-blob.txt', 'size' => 1234, 'path' => "{$this->container}/test-blob.txt", 'last_modified' => date('c')],
            ];
        }

        // List blobs: GET https://{account}.blob.core.windows.net/{container}?restype=container&comp=list
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}?restype=container&comp=list";
        if ($prefix) {
            $url .= '&prefix=' . rawurlencode($prefix);
        }

        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $auth = $this->getAuthorizationHeader('GET', "/{$this->accountName}/{$this->container}\nrestype:container\ncomp:list", [
            'x-ms-date' => gmdate('D, d M Y H:i:s \G\M\T'),
            'x-ms-version' => '2020-10-02',
        ]);

        $ch = curl_init("https://{$this->accountName}.blob.core.windows.net/{$this->container}?restype=container&comp=list&prefix=" . rawurlencode($prefix));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'x-ms-date: ' . gmdate('D, d M Y H:i:s \G\M\T'),
                'x-ms-version: 2020-10-02',
                'Authorization: ' . $this->getAuthorizationHeader('GET', "/{$this->accountName}/{$this->container}\nrestype:container\ncomp:list"),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($resp);
        if (!$xml || !isset($xml->Blobs)) return [];

        $files = [];
        foreach ($xml->Blobs->Blob as $blob) {
            $files[] = [
                'name' => (string)$blob->Name,
                'path' => (string)$blob->Name,
                'size' => (int)($blob->Properties->{'Content-Length'} ?? 0),
                'last_modified' => (string)$blob->Properties->{'Last-Modified'},
                'mime' => (string)($blob->Properties->{'Content-Type'} ?? 'application/octet-stream'),
            ];
        }
        return $files;
    }

    public function put(string $path, $contents, array $options = []): array
    {
        if ($this->testMode) {
            return ['success' => true, 'path' => $path];
        }

        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
        $headers = [
            'x-ms-date: ' . gmdate('D, d M Y H:i:s \G\M\T'),
            'x-ms-version: 2020-10-02',
            'x-ms-blob-type: BlockBlob',
            'Content-Type: ' . ($options['content_type'] ?? 'application/octet-stream'),
            'Content-Length: ' . strlen($contents),
        ];

        $auth = $this->getAuthorizationHeader('PUT', "/{$this->accountName}/{$this->container}/{$path}", [
            'x-ms-date' => gmdate('D, d M Y H:i:s \G\M\T'),
            'x-ms-version' => '2020-10-02',
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => $options['content_type'] ?? 'application/octet-stream',
            'Content-Length' => strlen($contents),
        ]);

        $ch = curl_init("https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path));
        curl_setopt_array($ch, [
            CURLOPT_PUT => true,
            CURLOPT_INFILESIZE => strlen($contents),
            CURLOPT_READFUNCTION => function ($ch, $fd, $length) use ($contents) {
                return $contents;
            },
            CURLOPT_INFILESIZE => strlen($contents),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($this->getCommonHeaders(), [
                'Authorization: ' . $this->getAuthorizationHeader('PUT', "/{$this->accountName}/{$this->container}/{$path}"),
                'Content-Type: ' . ($options['content_type'] ?? 'application/octet-stream'),
            ]),
            CURLOPT_TIMEOUT => 60,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return ['success' => true, 'path' => $path];
    }

    public function get(string $path): ?string
    {
        if ($this->testMode) return "test content for $path";

        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($this->getCommonHeaders(), [
                'Authorization: ' . $this->getAuthorizationHeader('GET', "/{$this->accountName}/{$this->container}/{$path}"),
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp !== false ? $resp : null;
    }

    public function delete(string $path): bool
    {
        if ($this->testMode) return true;

        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($this->getCommonHeaders(), [
                'Authorization: ' . $this->getAuthorizationHeader('DELETE', "/{$this->accountName}/{$this->container}/{$path}"),
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    public function url(string $path): ?string
    {
        return "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
    }

    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string
    {
        // Generate SAS token
        $start = gmdate('Y-m-d\TH:i:s\Z', time());
        $expiry = gmdate('Y-m-d\TH:i:s\Z', time() + $expiryMinutes * 60);
        $canonicalizedResource = "/blob/{$this->accountName}/{$this->container}/{$path}";
        $stringToSign = implode("\n", [
            'r',  // signed permissions (read)
            '',   // signed start
            gmdate('Y-m-d\TH:i:s\Z', time() + $expiryMinutes * 60), // signed expiry
            "/blob/{$this->accountName}/{$this->container}/{$path}", // canonicalized resource
            '',   // signed identifier
            '',   // signed IP
            'https', // signed protocol
            '2020-10-02', // signed version
            '',   // rscc
            '',   // rscd
            '',   // rsce
            '',   // rscl
            '',   // rsct
        ]);
        $key = base64_decode($this->accountKey);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $key, true));
        $sasToken = "sv=2020-10-02&sr=b&sig=" . rawurlencode(base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true))) . "&se=" . rawurlencode(gmdate('Y-m-d\TH:i:s\Z', time() + 3600)) . "&sp=r";
        return "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path) . "?{$sasToken}";
    }

    public function exists(string $path): bool
    {
        if ($this->testMode) return true;
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => $this->getCommonHeaders(),
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
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->container}/" . rawurlencode($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => $this->getCommonHeaders(),
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

    private function getCommonHeaders(): array
    {
        return [
            'x-ms-date: ' . gmdate('D, d M Y H:i:s \G\M\T'),
            'x-ms-version: 2020-10-02',
        ];
    }
}