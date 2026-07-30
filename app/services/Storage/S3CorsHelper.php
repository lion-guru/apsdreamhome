<?php

namespace App\Services\Storage;

/**
 * S3CorsHelper - generate, apply, fetch and validate S3 bucket CORS rules.
 *
 * Why this exists:
 *   S3 CORS rules must be applied to the bucket via a signed PUT /?cors
 *   request. The S3 web console does this for you, but in headless / scripted
 *   environments (CI, container images, IaC) you need a programmatic way.
 *   This class produces the XML, signs the request, and applies it.
 *
 * The app's own uploads go through the PHP backend, so CORS is normally
 * not required. It IS required if you ever serve static media directly from
 * S3 (e.g. via a CloudFront / public-read bucket) and a browser on a
 * different origin needs to GET them. This helper is the safety net for
 * that case.
 *
 * Methods (all return ['success' => bool, 'data' => ?, 'error' => ?] envelope):
 *   - generateConfig(array $origins, array $methods, array $headers, int $maxAge)
 *       Build a CORSConfiguration XML string. Defaults cover our own admin UI.
 *   - applyConfig(string $xml)          PUT /?cors on the configured bucket
 *   - getCurrentConfig()                GET /?cors  (returns raw XML)
 *   - deleteConfig()                    DELETE /?cors
 *   - validateConfig(string $xml)       Quick well-formedness + structure check
 *   - diffConfig(string $existingXml, string $desiredXml)
 *                                       True if the rules are functionally identical
 *
 * Configuration: all env vars, no code change required.
 *   AWS_ACCESS_KEY_ID
 *   AWS_SECRET_ACCESS_KEY
 *   AWS_DEFAULT_REGION
 *   AWS_BUCKET
 *   AWS_ENDPOINT          (optional, for MinIO/Spaces/R2)
 *   AWS_S3_USE_PATH_STYLE (optional, "true" for MinIO)
 *
 * Safety:
 *   - Never throws. Errors are returned as envelope.
 *   - All requests are signed with SigV4 (no SDK).
 *   - 3 retries on 5xx, no retries on 4xx (the AWS-recommended behaviour).
 *   - Logs each call to gateway_logs (best effort, failure is silent).
 */
class S3CorsHelper
{
    /** cURL timeout in seconds */
    private const CURL_TIMEOUT = 30;
    /** cURL connect timeout in seconds */
    private const CURL_CONNECTTIMEOUT = 10;
    /** Max retries on 5xx */
    private const MAX_RETRIES = 3;

    private string $accessKey;
    private string $secretKey;
    private string $region;
    private string $bucket;
    private ?string $endpoint;
    private bool $pathStyle;

    public function __construct(?array $config = null)
    {
        $this->accessKey = $config['access_key'] ?? (getenv('AWS_ACCESS_KEY_ID') ?: '');
        $this->secretKey = $config['secret_key'] ?? (getenv('AWS_SECRET_ACCESS_KEY') ?: '');
        $this->region    = $config['region']     ?? (getenv('AWS_DEFAULT_REGION') ?: 'ap-south-1');
        $this->bucket    = $config['bucket']     ?? (getenv('AWS_BUCKET') ?: '');
        $this->endpoint  = $config['endpoint']   ?? (getenv('AWS_ENDPOINT') ?: null);
        $this->pathStyle = ($config['path_style'] ?? getenv('AWS_S3_USE_PATH_STYLE')) === 'true' || $this->endpoint !== null;
    }

    public function isConfigured(): bool
    {
        return $this->accessKey !== '' && $this->secretKey !== '' && $this->bucket !== '';
    }

    /**
     * Build a CORS XML document from the given inputs.
     *
     * @param string[] $origins   List of allowed origins, e.g. ['https://apsdreamhome.com', 'https://www.apsdreamhome.com']
     *                            Special value '*' = allow any origin (insecure for credentialed requests).
     * @param string[] $methods   List of allowed HTTP methods. Default: GET, HEAD (safe for media serving).
     * @param string[] $headers   List of allowed request headers. Default: ['*'] (any header).
     * @param int      $maxAge    MaxAgeSeconds for the preflight cache. Default: 3000 (50 min).
     * @param bool     $exposeHeaders Whether to add an ExposeHeaders section (useful for ETag / Content-Disposition).
     * @param string[] $exposedHeaders Headers to expose to the browser. Default: ETag, Content-Length, Content-Type.
     */
    public function generateConfig(
        array $origins = ['*'],
        array $methods = ['GET', 'HEAD'],
        array $headers = ['*'],
        int $maxAge = 3000,
        bool $exposeHeaders = true,
        array $exposedHeaders = ['ETag', 'Content-Length', 'Content-Type']
    ): string {
        $origins  = $origins  ?: ['*'];
        $methods  = $methods  ?: ['GET', 'HEAD'];
        $headers  = $headers  ?: ['*'];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<CORSConfiguration>\n";
        $xml .= "  <CORSRule>\n";
        foreach ($origins as $o) {
            $xml .= "    <AllowedOrigin>" . htmlspecialchars($o, ENT_XML1) . "</AllowedOrigin>\n";
        }
        foreach ($methods as $m) {
            $m = strtoupper(trim($m));
            if (!in_array($m, ['GET', 'PUT', 'POST', 'DELETE', 'HEAD'], true)) continue;
            $xml .= "    <AllowedMethod>{$m}</AllowedMethod>\n";
        }
        foreach ($headers as $h) {
            $xml .= "    <AllowedHeader>" . htmlspecialchars($h, ENT_XML1) . "</AllowedHeader>\n";
        }
        $xml .= "    <MaxAgeSeconds>" . max(0, min(86400, $maxAge)) . "</MaxAgeSeconds>\n";
        if ($exposeHeaders && !empty($exposedHeaders)) {
            foreach ($exposedHeaders as $h) {
                $xml .= "    <ExposeHeader>" . htmlspecialchars($h, ENT_XML1) . "</ExposeHeader>\n";
            }
        }
        $xml .= "  </CORSRule>\n";
        $xml .= "</CORSConfiguration>\n";
        return $xml;
    }

    /**
     * Validate a CORS XML for well-formedness + the minimum required structure.
     */
    public function validateConfig(string $xml): array
    {
        if (trim($xml) === '') {
            return ['success' => false, 'data' => null, 'error' => 'Empty XML'];
        }
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            $errs = array_map(fn($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();
            return ['success' => false, 'data' => null, 'error' => 'XML parse error: ' . implode('; ', $errs)];
        }
        if (!isset($doc->CORSRule)) {
            return ['success' => false, 'data' => null, 'error' => 'Missing <CORSRule> element'];
        }
        $rules = $doc->CORSRule;
        $count = 0;
        foreach ($rules as $rule) {
            $count++;
            if (!isset($rule->AllowedOrigin))  { return ['success' => false, 'data' => null, 'error' => "Rule {$count}: missing <AllowedOrigin>"]; }
            if (!isset($rule->AllowedMethod))  { return ['success' => false, 'data' => null, 'error' => "Rule {$count}: missing <AllowedMethod>"]; }
        }
        return ['success' => true, 'data' => ['rule_count' => $count], 'error' => null];
    }

    /**
     * PUT the CORS XML to the bucket. Replaces any existing rules.
     */
    public function applyConfig(string $xml): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => null, 'error' => 'S3 not configured (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET required)'];
        }
        $v = $this->validateConfig($xml);
        if (empty($v['success'])) {
            return ['success' => false, 'data' => null, 'error' => 'Refused to send invalid XML: ' . $v['error']];
        }
        return $this->signedRequest('PUT', '?cors', $xml, 'application/xml');
    }

    /**
     * GET the current CORS XML from the bucket. Returns null body if not set.
     */
    public function getCurrentConfig(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => null, 'error' => 'S3 not configured (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET required)'];
        }
        $resp = $this->signedRequest('GET', '?cors', null, null);
        if (empty($resp['success'])) {
            // 404 (NoSuchCORSConfiguration) means no rules are set - treat as empty, not error
            if (($resp['status'] ?? 0) === 404) {
                return ['success' => true, 'data' => ['configured' => false, 'xml' => ''], 'error' => null];
            }
            return $resp;
        }
        return ['success' => true, 'data' => ['configured' => true, 'xml' => $resp['data'] ?? ''], 'error' => null];
    }

    /**
     * DELETE the CORS config on the bucket.
     */
    public function deleteConfig(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => null, 'error' => 'S3 not configured (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET required)'];
        }
        return $this->signedRequest('DELETE', '?cors', null, null);
    }

    /**
     * Return true if the existing and desired CORS configs are functionally
     * identical (after re-serialising to canonical form). Useful for "should
     * I apply?" gates in deploy scripts.
     */
    public function diffConfig(string $existingXml, string $desiredXml): bool
    {
        $canon = function (string $xml): ?\SimpleXMLElement {
            if (trim($xml) === '') return null;
            $doc = simplexml_load_string($xml);
            return $doc === false ? null : $doc;
        };
        $a = $canon($existingXml);
        $b = $canon($desiredXml);
        if ($a === null && $b === null) return true;
        if ($a === null || $b === null) return false;
        return $a->asXML() === $b->asXML();
    }

    /* ------------------------------------------------------------------ *
     *  SigV4 bucket-level request (PUT/GET/DELETE /?cors)
     * ------------------------------------------------------------------ */

    private function signedRequest(string $method, string $query, ?string $body, ?string $contentType): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => null, 'error' => 'S3 not configured (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET required)'];
        }

        $method = strtoupper($method);
        $url    = $this->buildUrl($query);
        $host  = parse_url($url, PHP_URL_HOST);

        $amzDate    = gmdate('Ymd\THis\Z');
        $dateStamp  = gmdate('Ymd');
        $payloadHash = $this->hash($body ?? '');

        $signHeaders = [
            'host'                 => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date'           => $amzDate,
        ];
        if ($contentType !== null) $signHeaders['content-type'] = $contentType;

        $canonicalHeaders = $this->canonicalHeaders($signHeaders);
        $signedHeadersList = implode(';', array_keys($signHeaders));
        $canonicalUri    = '/';
        $canonicalQuery  = $query; // raw ?cors
        $canonicalRequest = "$method\n$canonicalUri\n$canonicalQuery\n$canonicalHeaders\n$signedHeadersList\n$payloadHash";

        $scope = $dateStamp . '/' . $this->region . '/s3/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$scope\n" . $this->hash($canonicalRequest);
        $signingKey   = $this->getSignatureKey($this->secretKey, $dateStamp, $this->region, 's3');
        $signature    = $this->hmac($signingKey, $stringToSign, true);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$scope}, SignedHeaders={$signedHeadersList}, Signature={$signature}";

        $extraHeaders = [];
        if ($contentType !== null) $extraHeaders['Content-Type'] = $contentType;

        $attempt = 0;
        $lastError = null;
        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            $out = $this->curl($method, $url, $authHeader, $amzDate, $payloadHash, $extraHeaders, $body, true);
            if (!empty($out['transport_error'])) {
                $lastError = $out['error'] ?? 'transport';
                if ($attempt >= self::MAX_RETRIES) break;
                sleep((int)pow(2, $attempt - 1));
                continue;
            }
            $status = (int)($out['status'] ?? 0);
            if ($status >= 200 && $status < 300) {
                $this->log('info', 'cors.' . strtolower($method), $this->bucket, ['status' => $status]);
                return ['success' => true, 'data' => $out['body'] ?? '', 'error' => null, 'status' => $status];
            }
            if ($status >= 500 && $status < 600 && $attempt < self::MAX_RETRIES) {
                $lastError = "HTTP $status";
                sleep((int)pow(2, $attempt - 1));
                continue;
            }
            $errBody = $out['body'] ?? '';
            $err = $errBody !== '' ? substr($errBody, 0, 500) : "HTTP $status";
            $this->log('error', 'cors.failed', $this->bucket, ['method' => $method, 'status' => $status, 'body' => $err]);
            return ['success' => false, 'data' => $errBody, 'error' => $err, 'status' => $status];
        }
        return ['success' => false, 'data' => null, 'error' => $lastError ?? 'retries exhausted', 'status' => 0];
    }

    private function buildUrl(string $query): string
    {
        $scheme = 'https';
        if ($this->endpoint) {
            $ep = rtrim($this->endpoint, '/');
            $ep = preg_replace('#^https?://#', '', $ep);
            if ($this->pathStyle) {
                return "$scheme://$ep/{$this->bucket}/?$query";
            }
            return "$scheme://{$this->bucket}.{$ep}/?$query";
        }
        return "$scheme://{$this->bucket}.s3.{$this->region}.amazonaws.com/?$query";
    }

    private function curl(string $method, string $url, string $authHeader, string $amzDate, string $payloadHash, array $extraHeaders, ?string $body, bool $returnBody): array
    {
        $ch = curl_init();
        $headers = [
            'Authorization: ' . $authHeader,
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $amzDate,
            'Expect:',
        ];
        foreach ($extraHeaders as $h => $v) {
            $headers[] = "$h: $v";
        }
        if ($method === 'PUT' || $method === 'POST') {
            $headers[] = 'Content-Length: ' . (string)strlen((string)$body);
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
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $rawHeaders = substr($raw, 0, $headerSize);
        $bodyOut = $returnBody ? substr($raw, $headerSize) : '';
        return ['status' => $status, 'body' => $bodyOut, 'headers' => $this->parseHeaders($rawHeaders)];
    }

    private function parseHeaders(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = explode(':', $line, 2);
            $out[strtolower(trim($k))] = trim($v);
        }
        return $out;
    }

    private function canonicalHeaders(array $headers): string
    {
        ksort($headers);
        $out = '';
        foreach ($headers as $k => $v) {
            $out .= strtolower($k) . ':' . trim(preg_replace('/\s+/', ' ', $v)) . "\n";
        }
        return $out;
    }

    private function hash(string $data): string { return hash('sha256', $data); }
    private function hmac(string $key, string $data, bool $binary = false): string {
        return hash_hmac('sha256', $data, $key, $binary);
    }
    private function getSignatureKey(string $key, string $date, string $region, string $service): string {
        $kDate    = $this->hmac('AWS4' . $key, $date, true);
        $kRegion  = $this->hmac($kDate, $region, true);
        $kService = $this->hmac($kRegion, $service, true);
        $kSigning = $this->hmac($kService, 'aws4_request', true);
        return $kSigning;
    }

    /**
     * Best-effort log to gateway_logs. Never throws.
     */
    private function log(string $level, string $action, string $key, array $context): void
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("INSERT INTO gateway_logs
                (gateway, action, method, endpoint, status, response_code, request_payload)
                VALUES ('aws_s3', ?, 'OTHER', ?, ?, 0, ?)");
            $status = $level === 'error' ? 'failed' : 'success';
            $stmt->execute([
                $action,
                $key,
                $status,
                json_encode($context, JSON_UNESCAPED_SLASHES),
            ]);
        } catch (\Throwable $e) {
        // swallow - logging must never break a request
        error_log($e->getMessage());
        }
    }
}
