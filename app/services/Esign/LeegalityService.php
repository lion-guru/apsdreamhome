<?php

namespace App\Services\Esign;

use App\Core\Database\Database;

/**
 * Leegality E-Signature Gateway
 *
 * Handles document creation, signing status, and signature verification
 * via the Leegality API (https://api.leegality.com/v2).
 *
 * Contract:
 *  - NEVER throws. All public methods return ['success' => bool, ...].
 *  - Every call logged to `gateway_logs`.
 *  - Honors LEEGALITY_TEST_MODE env var for mock responses.
 */
class LeegalityService
{
    private const API_BASE = 'https://api.leegality.com/v2';
    private const TIMEOUT = 30;

    /** @var string */
    private $apiKey;

    /** @var bool */
    private $testMode;

    /** @var \PDO|null */
    private $pdo;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey
            ?? ($_ENV['LEEGALITY_API_KEY'] ?? getenv('LEEGALITY_API_KEY') ?: '');
        $this->testMode = filter_var(
            $_ENV['LEEGALITY_TEST_MODE'] ?? getenv('LEEGALITY_TEST_MODE') ?: 'true',
            FILTER_VALIDATE_BOOLEAN
        );
        $this->pdo = $this->resolvePdo();
    }

    /**
     * Create a document for e-signing via Leegality.
     *
     * @param array $data  Keys: title, signers (array of {name,email,phone}), file_url
     * @return array       {success, document_id, signing_url, error}
     */
    public function createDocument(array $data): array
    {
        $title    = $data['title'] ?? 'Agreement';
        $signers  = $data['signers'] ?? [];
        $fileUrl  = $data['file_url'] ?? '';
        $callbackUrl = $data['callback_url'] ?? '';

        if (empty($signers)) {
            return ['success' => false, 'error' => 'At least one signer is required.'];
        }

        $body = [
            'title'       => $title,
            'signers'     => array_map(function ($s) {
                return [
                    'name'  => $s['name'] ?? '',
                    'email' => $s['email'] ?? '',
                    'phone' => $s['phone'] ?? '',
                ];
            }, $signers),
            'file_url'    => $fileUrl,
        ];

        if (!empty($callbackUrl)) {
            $body['callback_url'] = $callbackUrl;
        }

        // Test mode: return mock document
        if ($this->testMode) {
            return $this->mockCreateDocument($body);
        }

        $result = $this->makeRequest('POST', '/documents', $body);

        if (!$result['success']) {
            return $result;
        }

        $resp = $result['data'] ?? [];
        $documentId = $resp['id'] ?? $resp['document_id'] ?? null;

        return [
            'success'      => true,
            'document_id'  => $documentId,
            'signing_url'  => $resp['signing_url'] ?? $resp['redirect_url'] ?? '',
            'status'       => $resp['status'] ?? 'created',
            'error'        => null,
        ];
    }

    /**
     * Get the signing status of a document.
     *
     * @param string $documentId
     * @return array  {success, status, signed_at, error}
     */
    public function getStatus(string $documentId): array
    {
        if (empty($documentId)) {
            return ['success' => false, 'error' => 'Document ID is required.'];
        }

        if ($this->testMode) {
            return $this->mockGetStatus($documentId);
        }

        $result = $this->makeRequest('GET', "/documents/{$documentId}");

        if (!$result['success']) {
            return $result;
        }

        $resp = $result['data'] ?? [];

        return [
            'success'   => true,
            'status'    => $resp['status'] ?? 'pending',
            'signed_at' => $resp['signed_at'] ?? null,
            'error'     => null,
        ];
    }

    /**
     * Verify that a document has been signed by all required parties.
     *
     * @param string $documentId
     * @return array  {success, verified, signer_details, error}
     */
    public function verifySignature(string $documentId): array
    {
        if (empty($documentId)) {
            return ['success' => false, 'error' => 'Document ID is required.'];
        }

        if ($this->testMode) {
            return $this->mockVerify($documentId);
        }

        $result = $this->makeRequest('GET', "/documents/{$documentId}/verify");

        if (!$result['success']) {
            return $result;
        }

        $resp = $result['data'] ?? [];

        return [
            'success'        => true,
            'verified'       => $resp['verified'] ?? false,
            'signer_details' => $resp['signers'] ?? [],
            'error'          => null,
        ];
    }

    /**
     * Get the list of signers for a document.
     *
     * @param string $documentId
     * @return array  {success, signers, error}
     */
    public function getSigners(string $documentId): array
    {
        if (empty($documentId)) {
            return ['success' => false, 'error' => 'Document ID is required.'];
        }

        if ($this->testMode) {
            return $this->mockGetSigners($documentId);
        }

        $result = $this->makeRequest('GET', "/documents/{$documentId}/signers");

        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'signers' => $result['data'] ?? [],
            'error'   => null,
        ];
    }

    /**
     * Check if the service is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && strlen($this->apiKey) >= 10;
    }

    /* ------------------------------------------------------------------ */
    /*  Private: HTTP request + logging                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Make a cURL request to the Leegality API.
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        $start  = microtime(true);
        $url    = self::API_BASE . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $duration = (int)((microtime(true) - $start) * 1000);
        curl_close($ch);

        if ($response === false || $error) {
            $this->logRequest('leegality', $method, $endpoint, $data, null, $httpCode, 'error', $error, $duration);
            return ['success' => false, 'error' => $error ?: 'cURL request failed.'];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $errorMsg = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode}";
            $this->logRequest('leegality', $method, $endpoint, $data, $decoded, $httpCode, 'error', $errorMsg, $duration);
            return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
        }

        $this->logRequest('leegality', $method, $endpoint, $data, $decoded, $httpCode, 'success', null, $duration);

        return [
            'success'   => true,
            'data'      => $decoded,
            'http_code' => $httpCode,
        ];
    }

    /**
     * Log request to gateway_logs table. NEVER throws.
     */
    private function logRequest(
        string $gateway,
        string $method,
        string $endpoint,
        $request,
        $response,
        int $httpCode,
        string $status,
        ?string $error,
        int $duration
    ): void {
        if (!$this->pdo) {
            return;
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO gateway_logs
                    (gateway, action, method, endpoint, request_payload, response_payload,
                     http_code, status, duration_ms, error_message, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                $gateway,
                $endpoint,
                $method,
                $endpoint,
                $this->safeJson($request),
                $this->safeJson($response),
                $httpCode,
                $status,
                $duration,
                $error,
            ]);
        } catch (\Throwable $e) {
            error_log('[LeegalityService::logRequest] ' . $e->getMessage());
        }
    }

    private function safeJson($data): ?string
    {
        if ($data === null) {
            return null;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? null : $json;
    }

    private function resolvePdo(): ?\PDO
    {
        try {
            if (class_exists(Database::class)) {
                $db = Database::getInstance();
                return method_exists($db, 'getConnection') ? $db->getConnection() : null;
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return null;
    }

    private function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /* ------------------------------------------------------------------ */
    /*  Mock responses (TEST_MODE)                                         */
    /* ------------------------------------------------------------------ */

    private function mockCreateDocument(array $data): array
    {
        $documentId = 'DOC_TEST_' . strtoupper(bin2hex(random_bytes(8)));
        $signingUrl = "https://esign.leegality.com/sign/{$documentId}";

        return [
            'success'      => true,
            'document_id'  => $documentId,
            'signing_url'  => $signingUrl,
            'status'       => 'created',
            'error'        => null,
            'test_mode'    => true,
        ];
    }

    private function mockGetStatus(string $documentId): array
    {
        return [
            'success'   => true,
            'status'    => 'signed',
            'signed_at' => date('Y-m-d H:i:s'),
            'error'     => null,
            'test_mode' => true,
        ];
    }

    private function mockVerify(string $documentId): array
    {
        return [
            'success'  => true,
            'verified' => true,
            'signer_details' => [
                [
                    'name'       => 'Test Signer',
                    'email'      => 'test@example.com',
                    'status'     => 'signed',
                    'signed_at'  => date('Y-m-d H:i:s'),
                ],
            ],
            'error'     => null,
            'test_mode' => true,
        ];
    }

    private function mockGetSigners(string $documentId): array
    {
        return [
            'success' => true,
            'signers' => [
                [
                    'name'   => 'Test Signer',
                    'email'  => 'test@example.com',
                    'phone'  => '+919999900000',
                    'status' => 'signed',
                ],
            ],
            'error'     => null,
            'test_mode' => true,
        ];
    }
}
