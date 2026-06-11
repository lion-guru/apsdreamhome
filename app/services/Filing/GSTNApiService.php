<?php
namespace App\Services\Filing;

use PDO;
use App\Services\ServiceConfigService;

/**
 * GSTNApiService — GSTN portal API client
 * Handles authentication, GSTR-1/3B submission, status checks
 * TEST_MODE=true by default — returns realistic mock responses
 *
 * Config resolution: constructor param → DB (service_configs) → env → hardcoded default.
 */
class GSTNApiService
{
    private $pdo;
    private $gstin;
    private $username;
    private $password;
    private $apiKey;
    private $testMode;
    private $baseUrl = 'https://api.gst.gov.in/gstn';
    private $authToken = null;
    private $tokenExpiry = 0;

    public function __construct(array $config = [])
    {
        // Fallback chain: constructor param → DB → env → hardcoded
        $dbConfig = self::getDbConfig();
        $this->testMode = $config['test_mode'] ?? $dbConfig['test_mode'] ?? ($_ENV['GSTN_TEST_MODE'] ?? 'true');
        $this->gstin = $config['gstin'] ?? $dbConfig['gstin'] ?? ($_ENV['GSTN_GSTIN'] ?? '');
        $this->username = $config['username'] ?? $dbConfig['username'] ?? ($_ENV['GSTN_USERNAME'] ?? '');
        $this->password = $config['password'] ?? $dbConfig['password'] ?? ($_ENV['GSTN_PASSWORD'] ?? '');
        $this->apiKey = $config['api_key'] ?? $dbConfig['api_key'] ?? ($_ENV['GSTN_API_KEY'] ?? '');

        try {
            $dbCfg = require 'C:/xampp/htdocs/apsdreamhome/config/database.php';
            $this->pdo = new PDO(
                "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
                $dbCfg['username'], $dbCfg['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Exception $e) {
            error_log("[GSTNApiService] DB connection failed: " . $e->getMessage());
        }
    }

    private static function getDbConfig(): array
    {
        try {
            return ServiceConfigService::getApiConfig('gstn');
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

        $result = $this->makeRequest('POST', '/gstn/authenticate', [
            'username' => $this->username,
            'password' => $this->password,
            'gstin' => $this->gstin,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);

        if ($result['success'] && !empty($result['data']['auth_token'])) {
            $this->authToken = $result['data']['auth_token'];
            $this->tokenExpiry = time() + ($result['data']['expiry'] ?? 21600);
            return ['success' => true, 'auth_token' => $this->authToken, 'expiry' => $this->tokenExpiry];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Authentication failed'];
    }

    // ========== GSTR-1 ==========

    public function submitGstr1(array $data): array
    {
        if ($this->testMode === 'true') {
            return $this->mockSubmitResponse('GSTR-1', $data);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('POST', '/gstr1/submit', [
            'gstin' => $data['gstin'] ?? $this->gstin,
            'fp' => $data['fp'] ?? '',
            'gt' => $data['gt'] ?? 0,
            'b2b' => $data['b2b'] ?? [],
            'b2c' => $data['b2c'] ?? [],
            'hsn' => $data['hsn'] ?? [],
        ]);

        $this->logApiCall('gstr1_submit', $data, $result, 'gstin_submit');
        return $result;
    }

    // ========== GSTR-3B ==========

    public function submitGstr3b(array $data): array
    {
        if ($this->testMode === 'true') {
            return $this->mockSubmitResponse('GSTR-3B', $data);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('POST', '/gstr3b/submit', [
            'gstin' => $data['gstin'] ?? $this->gstin,
            'fp' => $data['fp'] ?? $data['period'] ?? '',
            'output' => $data['output'] ?? [],
            'input' => $data['input'] ?? [],
            'net_payable' => $data['net_payable'] ?? 0,
        ]);

        $this->logApiCall('gstr3b_submit', $data, $result, 'gstin_submit');
        return $result;
    }

    // ========== Status & Queries ==========

    public function getStatus(string $gstin, string $returnPeriod): array
    {
        if ($this->testMode === 'true') {
            return $this->mockStatusResponse($gstin, $returnPeriod);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('GET', "/returns/status/{$gstin}/{$returnPeriod}");
        $this->logApiCall('status_check', compact('gstin', 'returnPeriod'), $result, 'gstin_status');
        return $result;
    }

    public function getLiability(string $gstin, string $returnPeriod): array
    {
        if ($this->testMode === 'true') {
            return $this->mockLiabilityResponse($gstin, $returnPeriod);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('GET', "/returns/liability/{$gstin}/{$returnPeriod}");
        $this->logApiCall('liability_check', compact('gstin', 'returnPeriod'), $result, 'gstin_liability');
        return $result;
    }

    public function getPaymentInfo(string $gstin, string $returnPeriod): array
    {
        if ($this->testMode === 'true') {
            return $this->mockPaymentResponse($gstin, $returnPeriod);
        }

        $token = $this->ensureToken();
        if (!$token) return ['success' => false, 'error' => 'Authentication required'];

        $result = $this->makeRequest('GET', "/returns/payment/{$gstin}/{$returnPeriod}");
        $this->logApiCall('payment_info', compact('gstin', 'returnPeriod'), $result, 'gstin_payment');
        return $result;
    }

    public function isTestMode(): bool
    {
        return $this->testMode === 'true';
    }

    public function getConnectionStatus(): array
    {
        if ($this->testMode === 'true') {
            return ['connected' => true, 'mode' => 'test', 'gstin' => $this->gstin ?: '27AADCB2230M1ZT', 'message' => 'TEST MODE — No real API calls'];
        }

        $connected = !empty($this->gstin) && !empty($this->username) && !empty($this->apiKey);
        return ['connected' => $connected, 'mode' => 'live', 'gstin' => $this->gstin, 'message' => $connected ? 'Live — Ready for production' : 'Credentials not configured'];
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
                'Gstin: ' . $this->gstin,
                $this->authToken ? 'Authorization: Bearer ' . $this->authToken : null,
                $this->apiKey ? 'x-api-key: ' . $this->apiKey : null,
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

        return ['success' => false, 'error' => $decoded['error'] ?? $decoded['message'] ?? "HTTP {$httpCode}", 'data' => $decoded, 'http_code' => $httpCode, 'duration_ms' => $duration];
    }

    private function ensureToken(): ?string
    {
        if ($this->authToken && time() < $this->tokenExpiry - 300) {
            return $this->authToken;
        }
        $result = $this->authenticate();
        return $result['success'] ? ($result['auth_token'] ?? null) : null;
    }

    // ========== Private: Gateway Logging ==========

    private function logApiCall(string $action, array $request, array $response, string $context = null): void
    {
        if (!$this->pdo) return;
        try {
            $status = ($response['success'] ?? false) ? 'success' : 'failed';
            $this->pdo->prepare(
                'INSERT INTO gateway_logs (gateway, action, recipient, request_payload, response_payload, status, http_code, duration_ms, error_message, context_json, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())'
            )->execute([
                'gstn-api',
                $action,
                $this->gstin,
                json_encode($request, JSON_UNESCAPED_UNICODE),
                json_encode($response, JSON_UNESCAPED_UNICODE),
                $status,
                $response['http_code'] ?? 0,
                $response['duration_ms'] ?? 0,
                ($response['error'] ?? null) ? substr($response['error'], 0, 1000) : null,
                $context ? json_encode(['context' => $context]) : null,
            ]);
        } catch (\Exception $e) {
            error_log("[GSTNApiService] logApiCall() failed: " . $e->getMessage());
        }
    }

    // ========== Private: Mock Responses ==========

    private function mockAuthenticate(): array
    {
        $token = 'TEST_TOKEN_' . strtoupper(bin2hex(random_bytes(16)));
        $this->authToken = $token;
        $this->tokenExpiry = time() + 21600;
        $this->logApiCall('authenticate', ['gstin' => $this->gstin], ['success' => true, 'auth_token' => $token]);
        return ['success' => true, 'auth_token' => $token, 'expiry' => $this->tokenExpiry];
    }

    private function mockSubmitResponse(string $returnType, array $data): array
    {
        $ack = 'ACK' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
        $result = [
            'success' => true,
            'reference_number' => $ack,
            'gstin' => $data['gstin'] ?? $this->gstin,
            'return_type' => $returnType,
            'period' => $data['fp'] ?? $data['period'] ?? date('dmY'),
            'status' => 'accepted',
            'message' => "{$returnType} submitted successfully (TEST MODE)",
            'acknowledgment_number' => $ack,
            'submitted_at' => date('Y-m-d H:i:s'),
        ];
        $this->logApiCall(strtolower(str_replace('-', '', $returnType)) . '_submit', $data, $result, 'mock');
        return $result;
    }

    private function mockStatusResponse(string $gstin, string $period): array
    {
        return [
            'success' => true,
            'data' => [
                'gstin' => $gstin,
                'return_period' => $period,
                'status' => 'filed',
                'filing_date' => date('Y-m-d', strtotime('-5 days')),
                'acknowledgment_no' => 'ACK' . date('Ymd', strtotime('-5 days')) . strtoupper(substr(md5($gstin . $period), 0, 8)),
                'mode' => 'test',
            ],
        ];
    }

    private function mockLiabilityResponse(string $gstin, string $period): array
    {
        $outTax = rand(50000, 500000);
        $itc = rand(10000, $outTax);
        return [
            'success' => true,
            'data' => [
                'gstin' => $gstin,
                'return_period' => $period,
                'output_tax' => $outTax,
                'input_tax_credit' => $itc,
                'net_liability' => $outTax - $itc,
                'interest' => 0,
                'late_fee' => 0,
                'total_payable' => $outTax - $itc,
                'mode' => 'test',
            ],
        ];
    }

    private function mockPaymentResponse(string $gstin, string $period): array
    {
        return [
            'success' => true,
            'data' => [
                'gstin' => $gstin,
                'return_period' => $period,
                'total_paid' => rand(10000, 200000),
                'pending_amount' => 0,
                'last_payment_date' => date('Y-m-d'),
                'challan_numbers' => ['CHL' . date('Ymd') . rand(1000, 9999)],
                'mode' => 'test',
            ],
        ];
    }
}
