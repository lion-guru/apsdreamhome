<?php
namespace App\Services\Filing;

use PDO;
use App\Services\ServiceConfigService;
use \App\Traits\ServiceTenantTrait;

/**
 * TINApiService — Tax Information Network API client
 * Handles TDS e-filing: Form 26Q/27Q submission, status, Form16A download
 * TEST_MODE=true by default — returns realistic mock responses
 *
 * Config resolution: constructor param → DB (service_configs) → env → hardcoded default.
 */
class TINApiService
{
    use ServiceTenantTrait;

    private $pdo;
    private $tan;
    private $username;
    private $password;
    private $testMode;
    private $baseUrl = 'https://tin-nsdl.com/api/v1';
    private $authToken = null;
    private $tokenExpiry = 0;

    public function __construct(array $config = [])
    {
        // Fallback chain: constructor param → DB → env → hardcoded
        $dbCfg = self::getDbConfig();
        $this->testMode = $config['test_mode'] ?? $dbCfg['test_mode'] ?? ($_ENV['TIN_TEST_MODE'] ?? 'true');
        $this->tan = $config['tan'] ?? ($dbCfg['tan'] ?? '') ?: ($_ENV['TIN_TAN'] ?? '');
        $this->username = $config['username'] ?? $dbCfg['username'] ?? ($_ENV['TIN_USERNAME'] ?? '');
        $this->password = $config['password'] ?? $dbCfg['password'] ?? ($_ENV['TIN_PASSWORD'] ?? '');

        try {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            error_log("[TINApiService] DB connection failed: " . $e->getMessage());
        }
    }

    private static function getDbConfig(): array
    {
        try {
            return ServiceConfigService::getApiConfig('tin');
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========== Authentication ==========

    public function authenticate(): array
    {
        if ($this->testMode === 'true') {
            return $this->mockAuthenticate();
        }

        $result = $this->makeRequest('POST', '/auth/login', [
            'username' => $this->username,
            'password' => $this->password,
            'tan' => $this->tan,
        ]);

        if ($result['success'] && !empty($result['data']['token'])) {
            $this->authToken = $result['data']['token'];
            $this->tokenExpiry = time() + ($result['data']['expires_in'] ?? 1800);
            return ['success' => true, 'token' => $this->authToken];
        }
        return ['success' => false, 'error' => $result['error'] ?? 'TIN authentication failed'];
    }

    // ========== Form Submissions ==========

    public function submitForm26Q(array $data): array
    {
        if ($this->testMode === 'true') {
            return $this->mockSubmitResponse('26Q', $data);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('POST', '/tds/submit', [
            'form_type' => '26Q',
            'tan' => $data['tan'] ?? $this->tan,
            'assessment_year' => $data['assessment_year'] ?? '',
            'financial_year' => $data['financial_year'] ?? '',
            'quarter' => $data['quarter'] ?? '',
            'deductee_count' => $data['total_deductees'] ?? 0,
            'total_tds' => $data['total_tds'] ?? 0,
            'sections' => $data['sections'] ?? [],
        ]);

        $this->logApiCall('form26q_submit', $data, $result, 'tds_submit');
        return $result;
    }

    public function submitForm27Q(array $data): array
    {
        if ($this->testMode === 'true') {
            return $this->mockSubmitResponse('27Q', $data);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('POST', '/tds/submit', [
            'form_type' => '27Q',
            'tan' => $data['tan'] ?? $this->tan,
            'assessment_year' => $data['assessment_year'] ?? '',
            'financial_year' => $data['financial_year'] ?? '',
            'quarter' => $data['quarter'] ?? '',
            'deductee_count' => $data['total_deductees'] ?? 0,
            'total_tds' => $data['total_tds'] ?? 0,
            'sections' => $data['sections'] ?? [],
        ]);

        $this->logApiCall('form27q_submit', $data, $result, 'tds_submit');
        return $result;
    }

    // ========== Status & Downloads ==========

    public function getStatus(string $tokenNumber): array
    {
        if ($this->testMode === 'true') {
            return $this->mockStatusResponse($tokenNumber);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('GET', "/tds/status/{$tokenNumber}");
        $this->logApiCall('tds_status', compact('tokenNumber'), $result, 'tds_status');
        return $result;
    }

    public function downloadForm16A(string $tokenNumber): array
    {
        if ($this->testMode === 'true') {
            return $this->mockDownloadResponse($tokenNumber);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('GET', "/tds/form16a/{$tokenNumber}");
        $this->logApiCall('form16a_download', compact('tokenNumber'), $result, 'tds_download');
        return $result;
    }

    public function isTestMode(): bool
    {
        return $this->testMode === 'true';
    }

    public function getConnectionStatus(): array
    {
        if ($this->testMode === 'true') {
            return ['connected' => true, 'mode' => 'test', 'tan' => $this->tan ?: 'RTDS0012345', 'message' => 'TEST MODE — No real API calls'];
        }
        $connected = !empty($this->tan) && !empty($this->username) && !empty($this->password);
        return ['connected' => $connected, 'mode' => 'live', 'tan' => $this->tan, 'message' => $connected ? 'Live — Ready for production' : 'Credentials not configured'];
    }

    // ========== Private: HTTP Client ==========

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $start = microtime(true);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array_filter([
                'Content-Type: application/json',
                'Accept: application/json',
                $this->authToken ? 'Authorization: Bearer ' . $this->authToken : null,
            ]),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $duration = (int)((microtime(true) - $start) * 1000);

        if ($error) {
            return ['success' => false, 'error' => "cURL error: {$error}", 'http_code' => 0, 'duration_ms' => $duration];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $decoded ?? [], 'http_code' => $httpCode, 'duration_ms' => $duration];
        }
        return ['success' => false, 'error' => $decoded['error'] ?? $decoded['message'] ?? "HTTP {$httpCode}", 'http_code' => $httpCode, 'duration_ms' => $duration];
    }

    private function ensureToken(): ?string
    {
        if ($this->authToken && time() < $this->tokenExpiry - 300) {
            return $this->authToken;
        }
        $result = $this->authenticate();
        return $result['success'] ? ($result['token'] ?? null) : null;
    }

    // ========== Private: Gateway Logging ==========

    private function logApiCall(string $action, array $request, array $response, string $context = null): void
    {
        if (!$this->pdo) return;
        try {
            $status = ($response['success'] ?? false) ? 'success' : 'failed';
            $this->pdo->prepare(
                'INSERT INTO gateway_logs (gateway, action, recipient, request_payload, response_payload, status, http_code, duration_ms, error_message, context_json, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())'
            )->execute([
                'tin-api',
                $action,
                $this->tan,
                json_encode($request, JSON_UNESCAPED_UNICODE),
                json_encode($response, JSON_UNESCAPED_UNICODE),
                $status,
                $response['http_code'] ?? 0,
                $response['duration_ms'] ?? 0,
                ($response['error'] ?? null) ? substr($response['error'], 0, 1000) : null,
                $context ? json_encode(['context' => $context]) : null,
                $this->tenantId(),
            ]);
        } catch (\Exception $e) {
            error_log("[TINApiService] logApiCall() failed: " . $e->getMessage());
        }
    }

    // ========== Private: Mock Responses ==========

    private function mockAuthenticate(): array
    {
        $token = 'TIN_MOCK_' . strtoupper(bin2hex(random_bytes(12)));
        $this->authToken = $token;
        $this->tokenExpiry = time() + 1800;
        $this->logApiCall('authenticate', ['tan' => $this->tan], ['success' => true, 'token' => $token]);
        return ['success' => true, 'token' => $token];
    }

    private function mockSubmitResponse(string $formType, array $data): array
    {
        $tokenNo = 'TDS' . date('Ymd') . strtoupper(substr(md5($formType . json_encode($data)), 0, 6));
        return [
            'success' => true,
            'data' => [
                'token_number' => $tokenNo,
                'form_type' => $formType,
                'status' => 'accepted',
                'acknowledgment_number' => 'ACK' . date('Ymd') . strtoupper(bin2hex(random_bytes(4))),
                'tan' => $data['tan'] ?? $this->tan,
                'financial_year' => $data['financial_year'] ?? '',
                'quarter' => $data['quarter'] ?? '',
                'submitted_at' => date('Y-m-d H:i:s'),
                'message' => "Form {$formType} submitted successfully (TEST MODE)",
                'mode' => 'test',
            ],
        ];
    }

    private function mockStatusResponse(string $tokenNumber): array
    {
        return [
            'success' => true,
            'data' => [
                'token_number' => $tokenNumber,
                'status' => 'processed',
                'form_type' => '26Q',
                'filing_date' => date('Y-m-d', strtotime('-3 days')),
                'processed_date' => date('Y-m-d', strtotime('-1 day')),
                'acknowledgment_number' => 'ACK' . date('Ymd', strtotime('-3 days')) . strtoupper(substr(md5($tokenNumber), 0, 8)),
                'mode' => 'test',
            ],
        ];
    }

    private function mockDownloadResponse(string $tokenNumber): array
    {
        return [
            'success' => true,
            'data' => [
                'token_number' => $tokenNumber,
                'form_type' => '16A',
                'file_name' => "Form16A_{$tokenNumber}.pdf",
                'download_url' => '#mock-download-url',
                'file_size' => rand(50000, 200000),
                'mode' => 'test',
                'message' => 'Form 16A certificate ready for download (TEST MODE)',
            ],
        ];
    }
}
