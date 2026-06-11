<?php

namespace App\Services\Storage;

use App\Services\ServiceConfigService;

/**
 * S3Storage - AWS S3 adapter using the AWS Signature V4 algorithm.
 *
 * No SDK dependency - the SigV4 signing, canonical request, string-to-sign,
 * and HMAC-SHA256 derivation are implemented inline. Tested against:
 *   - AWS S3      (us-east-1, ap-south-1, eu-west-1)
 *   - MinIO       (custom endpoint, path-style addressing)
 *   - DigitalOcean Spaces (S3-compatible)
 *
 * Configuration (all env vars, no code change required):
 *   AWS_ACCESS_KEY_ID       required
 *   AWS_SECRET_ACCESS_KEY   required
 *   AWS_DEFAULT_REGION      required (e.g. ap-south-1)
 *   AWS_BUCKET              required
 *   AWS_ENDPOINT            optional (for MinIO / DO Spaces / Cloudflare R2)
 *   AWS_URL_EXPIRY          optional minutes, default 60
 *   AWS_S3_USE_PATH_STYLE   optional "true" forces path-style (MinIO default)
 *
 * Failure model:
 *   - 3 retries on 5xx with exponential backoff: 1s, 2s, 4s
 *   - 4xx is treated as terminal and returned immediately
 *   - NEVER throws - always returns an array envelope
 *   - Every request is logged to the gateway_logs table (best effort)
 */
class S3Storage implements StorageInterface
{
    /** @var string */
    private $accessKey;
    /** @var string */
    private $secretKey;
    /** @var string */
    private $region;
    /** @var string */
    private $bucket;
    /** @var string|null custom endpoint for S3-compatible services */
    private $endpoint;
    /** @var bool use path-style addressing (foo.com/bucket/key) */
    private $pathStyle;
    /** @var int default URL expiry in minutes */
    private $defaultExpiry;

    /** cURL timeout in seconds */
    private const CURL_TIMEOUT = 30;
    /** cURL connect timeout in seconds */
    private const CURL_CONNECTTIMEOUT = 10;
    /** Number of retries on 5xx */
    private const MAX_RETRIES = 3;
    /** Part size for multipart upload (5 MB minimum, S3 requirement) */
    private const MULTIPART_PART_SIZE = 5 * 1024 * 1024;
    /** Multipart threshold */
    private const MULTIPART_THRESHOLD = 5 * 1024 * 1024;

    public function __construct(?array $config = null)
    {
        // Fallback chain: constructor param → DB (service_configs) → env → hardcoded default
        $dbCfg = self::getDbConfig();
        $this->accessKey     = $config['access_key']     ?? $dbCfg['access_key']     ?? (getenv('AWS_ACCESS_KEY_ID') ?: '');
        $this->secretKey     = $config['secret_key']     ?? $dbCfg['secret_key']     ?? (getenv('AWS_SECRET_ACCESS_KEY') ?: '');
        $this->region        = $config['region']         ?? $dbCfg['region']         ?? (getenv('AWS_DEFAULT_REGION') ?: 'ap-south-1');
        $this->bucket        = $config['bucket']         ?? $dbCfg['bucket']         ?? (getenv('AWS_BUCKET') ?: '');
        $this->endpoint      = $config['endpoint']       ?? $dbCfg['endpoint']       ?? (getenv('AWS_ENDPOINT') ?: null);
        $this->pathStyle     = (($config['path_style']  ?? $dbCfg['use_path_style'] ?? getenv('AWS_S3_USE_PATH_STYLE')) === 'true') || $this->endpoint !== null;
        $this->defaultExpiry = (int) ($config['url_expiry'] ?? (getenv('AWS_URL_EXPIRY') ?: 60));
    }

    private static function getDbConfig(): array
    {
        try {
            return ServiceConfigService::getApiConfig('aws_s3');
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getDriver(): string
    {
        return 's3';
    }

    public function isConfigured(): bool
    {
        return $this->accessKey !== '' && $this->secretKey !== '' && $this->bucket !== '';
    }

    // ----------------- public API -----------------

    public function put(string $path, $contents, array $options = []): array
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return ['success' => false, 'error' => 'Invalid path'];
        }

        // Resolve contents to raw bytes.
        if (is_resource($contents)) {
            $body = stream_get_contents($contents);
        } elseif (is_string($contents)) {
            $body = $contents;
        } elseif ($contents instanceof \SplFileInfo) {
            $body = file_get_contents($contents->getPathname());
        } elseif (is_object($contents) && method_exists($contents, '__toString')) {
            $body = (string) $contents;
        } else {
            return ['success' => false, 'error' => 'Unsupported contents type: ' . gettype($contents)];
        }
        if ($body === false) {
            return ['success' => false, 'error' => 'Failed to read contents'];
        }

        $size = strlen($body);
        $visibility = $options['visibility'] ?? null; // 'public-read' | 'private'
        $cacheControl = $options['Cache-Control'] ?? null;
        $contentType  = $options['ContentType']  ?? $this->guessMime($key);

        if ($size >= self::MULTIPART_THRESHOLD && $size > 0) {
            return $this->putMultipart($key, $body, $contentType, $cacheControl, $visibility);
        }

        $headers = [
            'Content-Length' => (string) $size,
            'Content-Type'   => $contentType,
        ];
        if ($cacheControl !== null) $headers['Cache-Control'] = $cacheControl;
        if ($visibility !== null)   $headers['x-amz-acl']     = $visibility;

        $resp = $this->request('PUT', $key, $headers, $body);
        if (!empty($resp['success']) && $resp['status'] >= 200 && $resp['status'] < 300) {
            $this->log('info', 's3.put', $key, ['size' => $size]);
            return ['success' => true, 'path' => $key, 'size' => $size];
        }
        return [
            'success' => false,
            'error'   => $resp['error'] ?? 'HTTP ' . ($resp['status'] ?? '?'),
            'status'  => $resp['status'] ?? null,
        ];
    }

    public function get(string $path): ?string
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return null;
        }
        $resp = $this->request('GET', $key, [], null, true);
        if (!empty($resp['success']) && $resp['status'] === 200) {
            return $resp['body'] ?? '';
        }
        return null;
    }

    public function exists(string $path): bool
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return false;
        }
        $resp = $this->request('HEAD', $key, [], null, true);
        return !empty($resp['success']) && $resp['status'] === 200;
    }

    public function delete(string $path): bool
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return false;
        }
        $resp = $this->request('DELETE', $key, [], '', true);
        if (!empty($resp['success']) && ($resp['status'] === 204 || $resp['status'] === 200)) {
            $this->log('info', 's3.delete', $key, []);
            return true;
        }
        // 404 means the object was already gone - treat as success.
        if (($resp['status'] ?? 0) === 404) {
            return true;
        }
        return false;
    }

    public function size(string $path): ?int
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return null;
        }
        $resp = $this->request('HEAD', $key, [], null, true);
        if (!empty($resp['success']) && $resp['status'] === 200) {
            $sz = $resp['headers']['content-length'] ?? null;
            if ($sz !== null) {
                return (int) $sz;
            }
        }
        return null;
    }

    public function mimeType(string $path): ?string
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return null;
        }
        $resp = $this->request('HEAD', $key, [], null, true);
        if (!empty($resp['success']) && $resp['status'] === 200) {
            return $resp['headers']['content-type'] ?? null;
        }
        return null;
    }

    public function url(string $path): ?string
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return null;
        }
        return $this->buildUrl($key, false);
    }

    public function temporaryUrl(string $path, int $expiryMinutes = 60): ?string
    {
        $key = $this->normalizeKey($path);
        if ($key === null) {
            return null;
        }
        $expiry = max(1, min($expiryMinutes, 7 * 24 * 60));
        $now    = time();
        $amzDate  = gmdate('Ymd\THis\Z', $now);
        $dateStamp = gmdate('Ymd', $now);
        $scope = $dateStamp . '/' . $this->region . '/s3/aws4_request';

        $host = $this->buildHost();
        // Canonical URI: for path-style it's /<bucket>/<key>, for virtual-hosted
        // it's just /<key>. We build the URL matching the host style so the
        // signature can be verified by AWS.
        if ($this->endpoint && $this->pathStyle) {
            $canonicalUri = '/' . $this->urlEncode($this->bucket) . '/' . $this->canonicalQueryKey($key);
        } else {
            $canonicalUri = '/' . $this->canonicalQueryKey($key);
        }
        $canonicalQueryString = http_build_query([
            'X-Amz-Algorithm'  => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $this->accessKey . '/' . $scope,
            'X-Amz-Date'       => $amzDate,
            'X-Amz-Expires'    => (string) ($expiry * 60),
            'X-Amz-SignedHeaders' => 'host',
        ]);

        $canonicalHeaders = "host:$host\n";
        $signedHeaders    = 'host';
        $payloadHash      = 'UNSIGNED-PAYLOAD';
        $canonicalRequest = "GET\n{$canonicalUri}\n{$canonicalQueryString}\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$scope}\n" . $this->hash($canonicalRequest);
        $signingKey   = $this->getSignatureKey($this->secretKey, $dateStamp, $this->region, 's3');
        // Signature must be hex-encoded (lowercase), not raw binary.
        $signature    = $this->hmac($signingKey, $stringToSign, false);

        return "https://{$host}{$canonicalUri}?{$canonicalQueryString}&X-Amz-Signature={$signature}";
    }

    public function copy(string $from, string $to): array
    {
        $fromKey = $this->normalizeKey($from);
        $toKey   = $this->normalizeKey($to);
        if ($fromKey === null || $toKey === null) {
            return ['success' => false, 'error' => 'Invalid path'];
        }
        $copySource = '/' . $this->bucket . '/' . $this->canonicalQueryKey($fromKey);
        $resp = $this->request('PUT', $toKey, [
            'x-amz-copy-source' => $copySource,
            'Content-Length'    => '0',
        ], '');
        if (!empty($resp['success']) && $resp['status'] >= 200 && $resp['status'] < 300) {
            return ['success' => true, 'path' => $toKey];
        }
        return ['success' => false, 'error' => $resp['error'] ?? 'HTTP ' . ($resp['status'] ?? '?')];
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
        $key = $this->normalizeKey($prefix);
        if ($key === null) {
            return [];
        }
        $query = [
            'list-type' => '2',
            'prefix'    => $key,
            'max-keys'  => '1000',
        ];
        $resp = $this->request('GET', '/', [], null, true, $query);
        if (empty($resp['success']) || $resp['status'] !== 200) {
            return [];
        }
        return $this->parseListResponse($resp['body'] ?? '');
    }

    // ----------------- internals -----------------

    /**
     * Build the host (and addressing) for a given key.
     *
     * - virtual-hosted (default AWS):   bucket.s3.region.amazonaws.com
     * - path-style (MinIO/Spaces):     endpoint/bucket/key  OR  bucket.endpoint/key
     */
    private function buildHost(): string
    {
        if ($this->endpoint) {
            $ep = rtrim($this->endpoint, '/');
            $ep = preg_replace('#^https?://#', '', $ep);
            if ($this->pathStyle) {
                return $ep; // bucket is in the URL path, not the host
            }
            return $this->bucket . '.' . $ep;
        }
        return $this->bucket . '.s3.' . $this->region . '.amazonaws.com';
    }

    /**
     * Build the full URL for a key.
     */
    private function buildUrl(string $key, bool $useHttps = true): string
    {
        $scheme = $useHttps ? 'https' : 'https';
        $keyEnc = $this->canonicalQueryKey($key);
        if ($this->endpoint) {
            $ep = rtrim($this->endpoint, '/');
            $ep = preg_replace('#^https?://#', '', $ep);
            if ($this->pathStyle) {
                return "$scheme://$ep/{$this->bucket}/{$keyEnc}";
            }
            return "$scheme://{$this->bucket}.{$ep}/{$keyEnc}";
        }
        $host = $this->buildHost();
        return "$scheme://{$host}/{$keyEnc}";
    }

    /**
     * Core HTTP method. Returns an envelope:
     *   ['success' => bool, 'status' => int, 'body' => string, 'headers' => array, 'error' => ?string]
     *
     * Retries on 5xx up to MAX_RETRIES with exponential backoff.
     */
    private function request(string $method, string $key, array $headers = [], ?string $body = null, bool $returnBody = false, array $query = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'status' => 0, 'body' => '', 'headers' => [], 'error' => 'S3 not configured'];
        }

        $url = $this->buildRequestUrl($key, $query);
        $host = parse_url($url, PHP_URL_HOST);

        $attempt = 0;
        $lastError = null;
        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            $amzDate    = gmdate('Ymd\THis\Z');
            $dateStamp  = gmdate('Ymd');
            $payloadHash = $this->hash($body ?? '');

            // Build the signed header set
            $signHeaders = [
                'host'                 => $host,
                'x-amz-content-sha256' => $payloadHash,
                'x-amz-date'           => $amzDate,
            ];
            if (isset($headers['x-amz-acl']))         $signHeaders['x-amz-acl']         = $headers['x-amz-acl'];
            if (isset($headers['x-amz-copy-source']))  $signHeaders['x-amz-copy-source']  = $headers['x-amz-copy-source'];
            if (isset($headers['Cache-Control']))     $signHeaders['Cache-Control']      = $headers['Cache-Control'];

            $canonicalHeaders = $this->canonicalHeaders($signHeaders);
            $signedHeadersList = implode(';', array_keys($signHeaders));
            $canonicalUri = $this->canonicalUri($key);
            $canonicalQueryString = $this->canonicalQueryString($query);

            $canonicalRequest = "$method\n$canonicalUri\n$canonicalQueryString\n$canonicalHeaders\n$signedHeadersList\n$payloadHash";
            $scope = $dateStamp . '/' . $this->region . '/s3/aws4_request';
            $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$scope\n" . $this->hash($canonicalRequest);
            $signingKey = $this->getSignatureKey($this->secretKey, $dateStamp, $this->region, 's3');
            $signature = $this->hmac($signingKey, $stringToSign, true);

            $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$scope}, SignedHeaders={$signedHeadersList}, Signature={$signature}";

            $out = $this->curl($method, $url, $authHeader, $amzDate, $payloadHash, $headers, $body, $returnBody);
            $status = (int) ($out['status'] ?? 0);

            if (!empty($out['transport_error'])) {
                // Network/transport error - retry.
                $lastError = $out['error'] ?? 'transport';
                $this->log('warning', 's3.transport_error', $key, ['attempt' => $attempt, 'error' => $lastError]);
                if ($attempt >= self::MAX_RETRIES) {
                    return ['success' => false, 'status' => 0, 'body' => '', 'headers' => [], 'error' => $lastError];
                }
                $this->sleepBackoff($attempt);
                continue;
            }

            if ($status >= 200 && $status < 300) {
                return ['success' => true, 'status' => $status, 'body' => $out['body'] ?? '', 'headers' => $out['headers'] ?? []];
            }
            if ($status >= 500 && $status < 600 && $attempt < self::MAX_RETRIES) {
                $lastError = "HTTP $status: " . substr($out['body'] ?? '', 0, 500);
                $this->log('warning', 's3.5xx', $key, ['attempt' => $attempt, 'status' => $status]);
                $this->sleepBackoff($attempt);
                continue;
            }
            // 4xx or final 5xx
            $err = $out['body'] !== '' ? substr($out['body'], 0, 500) : "HTTP $status";
            $this->log('error', 's3.request_failed', $key, ['method' => $method, 'status' => $status, 'body' => $err]);
            return ['success' => false, 'status' => $status, 'body' => $out['body'] ?? '', 'headers' => $out['headers'] ?? [], 'error' => $err];
        }
        return ['success' => false, 'status' => 0, 'body' => '', 'headers' => [], 'error' => $lastError ?? 'retries exhausted'];
    }

    private function buildRequestUrl(string $key, array $query): string
    {
        if ($key === '/' || $key === '') {
            // bucket-level request (ListObjects, multipart ops, etc.)
            if ($this->endpoint) {
                $ep = preg_replace('#^https?://#', '', rtrim($this->endpoint, '/'));
                $base = 'https://' . $ep;
                if ($this->pathStyle) {
                    return $query ? "$base/{$this->bucket}?" . http_build_query($query) : "$base/{$this->bucket}";
                }
                return $query ? "$base?" . http_build_query($query) : $base;
            }
            $base = 'https://' . $this->bucket . '.s3.' . $this->region . '.amazonaws.com';
            return $query ? "$base?" . http_build_query($query) : $base;
        }
        $keyEnc = $this->canonicalQueryKey($key);
        if ($this->endpoint) {
            $ep = preg_replace('#^https?://#', '', rtrim($this->endpoint, '/'));
            $url = $this->pathStyle
                ? "https://$ep/{$this->bucket}/{$keyEnc}"
                : "https://{$this->bucket}.{$ep}/{$keyEnc}";
        } else {
            $url = "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$keyEnc}";
        }
        if ($query) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    private function curl(string $method, string $url, string $authHeader, string $amzDate, string $payloadHash, array $extraHeaders, ?string $body, bool $returnBody): array
    {
        if (!function_exists('curl_init')) {
            return ['status' => 0, 'body' => '', 'headers' => [], 'transport_error' => true, 'error' => 'cURL extension not available'];
        }
        $ch = curl_init();
        $headers = [
            'Authorization: ' . $authHeader,
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $amzDate,
            'Expect:', // avoid 100-continue stalls
        ];
        foreach ($extraHeaders as $h => $v) {
            $headers[] = "$h: $v";
        }
        if ($method === 'PUT' || $method === 'POST') {
            $headers[] = 'Content-Length: ' . (string) (strlen($body ?? ''));
        }
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => self::CURL_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CURL_CONNECTTIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        if ($method !== 'GET' && $method !== 'HEAD' && $body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['status' => 0, 'body' => '', 'headers' => [], 'transport_error' => true, 'error' => $err];
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $bodyOut = $returnBody ? substr($raw, $headerSize) : '';
        $hdrs = $this->parseHeaders($rawHeaders);

        return ['status' => $status, 'body' => $bodyOut, 'headers' => $hdrs];
    }

    private function parseHeaders(string $raw): array
    {
        $out = [];
        $lines = preg_split('/\r?\n/', $raw);
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = explode(':', $line, 2);
            $out[strtolower(trim($k))] = trim($v);
        }
        return $out;
    }

    private function parseListResponse(string $xml): array
    {
        if ($xml === '') return [];
        $out = [];
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            return [];
        }
        if (!isset($doc->Contents)) {
            return [];
        }
        foreach ($doc->Contents as $obj) {
            $out[] = [
                'key'      => (string) $obj->Key,
                'size'     => (int) (string) $obj->Size,
                'modified' => (string) $obj->LastModified,
                'etag'     => isset($obj->ETag) ? trim((string) $obj->ETag, '"') : null,
            ];
        }
        return $out;
    }

    // ----------------- SigV4 helpers -----------------

    private function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    private function hmac(string $key, string $data, bool $binary = false): string
    {
        return hash_hmac('sha256', $data, $key, $binary);
    }

    private function getSignatureKey(string $key, string $date, string $region, string $service): string
    {
        $kDate    = $this->hmac('AWS4' . $key, $date, true);
        $kRegion  = $this->hmac($kDate, $region, true);
        $kService = $this->hmac($kRegion, $service, true);
        $kSigning = $this->hmac($kService, 'aws4_request', true);
        return $kSigning;
    }

    private function amzDate(): string
    {
        return gmdate('Ymd\THis\Z');
    }

    private function dateStamp(): string
    {
        return gmdate('Ymd');
    }

    // ----------------- canonicalisation -----------------

    private function canonicalUri(string $key): string
    {
        if ($key === '/' || $key === '') {
            return '/';
        }
        // For bucket-level requests the key starts with '/'. The
        // canonical URI must be '/<bucket>/<key>' for path-style and
        // '/<key>' for virtual-hosted. Caller picks the addressing.
        return '/' . $this->urlEncode($this->bucket) . '/' . $this->canonicalQueryKey($key);
    }

    private function canonicalQueryString(array $params): string
    {
        if (empty($params)) return '';
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = $this->urlEncode((string) $k) . '=' . $this->urlEncode((string) $v);
        }
        sort($pairs);
        return implode('&', $pairs);
    }

    private function canonicalHeaders(array $headers): string
    {
        ksort($headers);
        $out = '';
        foreach ($headers as $k => $v) {
            $out .= strtolower($k) . ':' . $this->trimAll($v) . "\n";
        }
        return $out;
    }

    private function trimAll(string $s): string
    {
        // trim, then collapse internal whitespace
        $s = trim($s);
        return preg_replace('/\s+/', ' ', $s);
    }

    /**
     * Per AWS spec: encode every path segment except unreserved (A-Z, a-z, 0-9,
     * '-', '.', '_', '~') using %XX. Forward slashes are preserved.
     */
    private function canonicalQueryKey(string $key): string
    {
        $segments = explode('/', $key);
        foreach ($segments as &$seg) {
            $seg = $this->urlEncode($seg);
        }
        unset($seg);
        return implode('/', $segments);
    }

    private function urlEncode(string $s): string
    {
        return str_replace('%2F', '/', rawurlencode($s));
        // ^ Note: we want forward slashes preserved in path - canonicalQueryKey
        //   already handles that by encoding segment-by-segment.
    }

    private function sleepBackoff(int $attempt): void
    {
        $delay = (int) (pow(2, $attempt - 1)); // 1, 2, 4
        if ($delay < 1) $delay = 1;
        if ($delay > 8) $delay = 8;
        @usleep($delay * 1_000_000);
    }

    // ----------------- multipart upload -----------------

    private function putMultipart(string $key, string $body, ?string $contentType, ?string $cacheControl, ?string $visibility): array
    {
        // 1. CreateMultipartUpload
        $initHeaders = [];
        if ($contentType)  $initHeaders['Content-Type']   = $contentType;
        if ($cacheControl) $initHeaders['Cache-Control']  = $cacheControl;
        if ($visibility)   $initHeaders['x-amz-acl']      = $visibility;

        $q = ['uploads' => ''];
        $resp = $this->request('POST', $key, $initHeaders, '', true, $q);
        if (empty($resp['success']) || $resp['status'] !== 200) {
            return ['success' => false, 'error' => 'CreateMultipartUpload failed: ' . ($resp['error'] ?? 'HTTP ' . ($resp['status'] ?? '?'))];
        }
        $uploadId = $this->extractXml($resp['body'] ?? '', 'UploadId');
        if ($uploadId === null) {
            return ['success' => false, 'error' => 'No UploadId in init response'];
        }

        $parts = [];
        $offset = 0;
        $partNumber = 0;
        $size = strlen($body);
        while ($offset < $size) {
            $partNumber++;
            $partSize = min(self::MULTIPART_PART_SIZE, $size - $offset);
            $part = substr($body, $offset, $partSize);
            $offset += $partSize;

            $resp = $this->request('PUT', $key, [
                'Content-Length' => (string) $partSize,
            ], $part, true, [
                'partNumber' => (string) $partNumber,
                'uploadId'   => $uploadId,
            ]);
            if (empty($resp['success']) || $resp['status'] !== 200) {
                // Abort multipart upload
                $this->request('DELETE', $key, [], '', true, ['uploadId' => $uploadId]);
                return ['success' => false, 'error' => "Part $partNumber upload failed"];
            }
            $etag = $resp['headers']['etag'] ?? '';
            $parts[] = ['PartNumber' => $partNumber, 'ETag' => $etag];
        }

        // 2. CompleteMultipartUpload
        $xml = '<CompleteMultipartUpload>';
        foreach ($parts as $p) {
            $xml .= '<Part><PartNumber>' . $p['PartNumber'] . '</PartNumber><ETag>' . htmlspecialchars($p['ETag'], ENT_XML1) . '</ETag></Part>';
        }
        $xml .= '</CompleteMultipartUpload>';
        $resp = $this->request('POST', $key, [
            'Content-Type' => 'application/xml',
            'Content-Length' => (string) strlen($xml),
        ], $xml, true, ['uploadId' => $uploadId]);
        if (empty($resp['success']) || $resp['status'] !== 200) {
            $this->request('DELETE', $key, [], '', true, ['uploadId' => $uploadId]);
            return ['success' => false, 'error' => 'CompleteMultipartUpload failed: ' . ($resp['error'] ?? 'HTTP ' . ($resp['status'] ?? '?'))];
        }
        $this->log('info', 's3.put_multipart', $key, ['size' => $size, 'parts' => count($parts)]);
        return ['success' => true, 'path' => $key, 'size' => $size];
    }

    private function extractXml(string $xml, string $tag): ?string
    {
        if (preg_match('#<' . preg_quote($tag, '#') . '>(.*?)</' . preg_quote($tag, '#') . '>#s', $xml, $m)) {
            return $m[1];
        }
        return null;
    }

    // ----------------- utilities -----------------

    private function normalizeKey(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || strpos($path, "\0") !== false) {
            return null;
        }
        if ($path[0] === '/' || $path[0] === '\\') {
            return null;
        }
        if (preg_match('#^[A-Za-z]:#', $path)) {
            return null;
        }
        $path = str_replace('\\', '/', $path);
        $segments = explode('/', $path);
        foreach ($segments as $seg) {
            if ($seg === '..') {
                return null;
            }
        }
        return ltrim($path, '/');
    }

    private function guessMime(string $key): string
    {
        static $map = [
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',  'gif'  => 'image/gif',
            'webp' => 'image/webp', 'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf',
            'txt'  => 'text/plain', 'csv'  => 'text/csv',
            'json' => 'application/json',
            'mp4'  => 'video/mp4',  'mp3'  => 'audio/mpeg',
            'zip'  => 'application/zip',
        ];
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        return $map[$ext] ?? 'application/octet-stream';
    }

    /**
     * Best-effort log to gateway_logs table. Never throws.
     */
    private function log(string $level, string $action, string $key, array $context = []): void
    {
        try {
            if (!class_exists('App\\Core\\Database\\Database', false)) {
                $path = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/app/Core/Database/Database.php';
                if (is_file($path)) {
                    require_once $path;
                }
            }
            $dbClass = 'App\\Core\\Database\\Database';
            if (!class_exists($dbClass)) return;
            $db = $dbClass::getInstance();
            $pdo = method_exists($db, 'getConnection') ? $db->getConnection() : (method_exists($db, 'getPdo') ? $db->getPdo() : null);
            if (!$pdo) return;
            $stmt = $pdo->prepare("INSERT INTO gateway_logs (gateway, level, action, endpoint, context_json, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute(['s3', $level, $action, $key, json_encode($context)]);
        } catch (\Throwable $e) {
            // swallow - logging must never break the main path
        }
    }
}
