<?php

namespace App\Services\KYC;

/**
 * UIDAI Aadhaar Verification Service
 * 
 * Supports:
 * 1. Verhoeff checksum validation (offline)
 * 2. Demographic verification (name + DOB + gender)
 * 3. OTP-based verification (requires registered mobile)
 * 
 * Production UIDAI API: https://uidai.gov.in/api/
 * Requires: Registered agency, licensed API key, OTP gateway
 * 
 * This service operates in MOCK mode by default (UIDAI_TEST_MODE=true).
 * Set UIDAI_TEST_MODE=false and provide real credentials for production.
 */
class UIDAIVerificationService
{
    private $apiKey;
    private $licenseKey;
    private $baseUrl;
    private $testMode;

    // Verhoeff algorithm tables
    private static $d = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
        [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
        [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
        [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
        [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
        [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
        [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
        [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
        [9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
    ];

    private static $p = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
        [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
        [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
        [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
        [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
        [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
        [7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
    ];

    // Known test Aadhaar numbers for mock mode
    private $testAadhaar = [
        '123456789012' => ['valid' => false, 'reason' => 'Invalid checksum'],
        '000000000000' => ['valid' => false, 'reason' => 'Invalid Aadhaar'],
        '999999999999' => ['valid' => false, 'reason' => 'Deactivated Aadhaar'],
    ];

    public function __construct()
    {
        $this->apiKey = $_ENV['UIDAI_API_KEY'] ?? '';
        $this->licenseKey = $_ENV['UIDAI_LICENSE_KEY'] ?? '';
        $this->baseUrl = $_ENV['UIDAI_API_BASE_URL'] ?? 'https://uidai.gov.in/api/v1';
        $this->testMode = ($_ENV['UIDAI_TEST_MODE'] ?? 'true') === 'true';
    }

    /**
     * Validate Aadhaar number (Verhoeff checksum)
     * 
     * @param string $aadhaar 12-digit Aadhaar number
     * @return array {success, message, data}
     */
    public function validateChecksum(string $aadhaar): array
    {
        $aadhaar = preg_replace('/\D/', '', $aadhaar);

        if (strlen($aadhaar) !== 12) {
            return [
                'success' => false,
                'message' => 'Aadhaar must be exactly 12 digits',
                'data' => ['aadhaar' => $aadhaar, 'valid' => false],
            ];
        }

        // Check for known invalid test numbers
        if (isset($this->testAadhaar[$aadhaar])) {
            return [
                'success' => false,
                'message' => $this->testAadhaar[$aadhaar]['reason'],
                'data' => ['aadhaar' => $aadhaar, 'valid' => false],
            ];
        }

        // Verhoeff checksum verification
        $isValid = $this->verhoeffCheck($aadhaar);

        if (!$isValid) {
            return [
                'success' => false,
                'message' => 'Invalid Aadhaar number (checksum mismatch)',
                'data' => ['aadhaar' => $aadhaar, 'valid' => false],
            ];
        }

        return [
            'success' => true,
            'message' => 'Aadhaar checksum valid',
            'data' => ['aadhaar' => $aadhaar, 'valid' => true],
        ];
    }

    /**
     * Full Aadhaar verification via UIDAI API
     * 
     * @param string $aadhaar 12-digit Aadhaar number
     * @param string $name Name as on Aadhaar
     * @param string|null $dob Date of birth (YYYY-MM-DD)
     * @param string|null $gender Male/Female/Other
     * @return array {success, message, data, provider_response}
     */
    public function verify(string $aadhaar, string $name = '', ?string $dob = null, ?string $gender = null): array
    {
        $aadhaar = preg_replace('/\D/', '', $aadhaar);

        // Step 1: Length check
        if (strlen($aadhaar) !== 12) {
            return [
                'success' => false,
                'message' => 'Aadhaar must be exactly 12 digits',
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
            ];
        }

        // Step 2: MOCK mode (skip checksum validation)
        if ($this->testMode) {
            return $this->mockVerify($aadhaar, $name, $dob, $gender);
        }

        // Step 3: Verhoeff checksum (production only)
        if (!$this->verhoeffCheck($aadhaar)) {
            return [
                'success' => false,
                'message' => 'Invalid Aadhaar number (checksum failed)',
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
            ];
        }

        // Step 4: Check known invalid test numbers
        if (isset($this->testAadhaar[$aadhaar])) {
            return [
                'success' => false,
                'message' => $this->testAadhaar[$aadhaar]['reason'],
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
            ];
        }

        // Step 5: Real UIDAI API call
        return $this->callUIDAIApi($aadhaar, $name, $dob, $gender);
    }

    /**
     * Mock UIDAI verification for testing
     */
    private function mockVerify(string $aadhaar, string $name, ?string $dob, ?string $gender): array
    {
        $masked = 'XXXXXXXX' . substr($aadhaar, -4);

        return [
            'success' => true,
            'message' => 'Aadhaar verified successfully via UIDAI',
            'data' => [
                'aadhaar' => $masked,
                'name_match' => !empty($name),
                'dob_match' => !empty($dob),
                'gender' => $gender ?: 'Male',
                'status' => 'ACTIVE',
                'age_band' => $this->estimateAgeBand($dob),
                'state' => 'Uttar Pradesh',
                'verified_at' => date('Y-m-d H:i:s'),
                'verification_type' => 'demographic',
            ],
            'provider_response' => ['mock' => true, 'test_mode' => true],
        ];
    }

    /**
     * Real UIDAI API call (production)
     */
    private function callUIDAIApi(string $aadhaar, string $name, ?string $dob, ?string $gender): array
    {
        $payload = [
            'aadhaar' => $aadhaar,
            'name' => $name,
            'dob' => $dob,
            'gender' => $gender,
        ];

        $ch = curl_init($this->baseUrl . '/aadhaar/verify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-UIDAI-API-Key: ' . $this->apiKey,
                'X-UIDAI-License-Key: ' . $this->licenseKey,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'UIDAI API connection error: ' . $error,
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
                'provider_response' => ['error' => $error],
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'UIDAI API returned HTTP ' . $httpCode,
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false, 'http_code' => $httpCode],
                'provider_response' => ['http_code' => $httpCode, 'body' => $response],
            ];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Invalid response from UIDAI API',
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
                'provider_response' => ['raw' => substr($response, 0, 500)],
            ];
        }

        $uidaiStatus = $result['status'] ?? 'UNKNOWN';
        $isSuccess = in_array($uidaiStatus, ['VALID', 'ACTIVE', 'verified', 'demographic_verified']);

        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Aadhaar verified successfully' : 'Aadhaar verification failed: ' . $uidaiStatus,
            'data' => [
                'aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4),
                'name_match' => $result['name_match'] ?? false,
                'dob_match' => $result['dob_match'] ?? false,
                'gender' => $result['gender'] ?? '',
                'status' => $uidaiStatus,
                'verification_type' => $result['verification_type'] ?? 'demographic',
                'verified_at' => date('Y-m-d H:i:s'),
            ],
            'provider_response' => $result,
        ];
    }

    /**
     * Verhoeff checksum validation
     */
    public function verhoeffCheck(string $num): bool
    {
        $len = strlen($num);
        $inv = 0;
        for ($i = 0; $i < $len; $i++) {
            $inv = self::$d[$inv][self::$p[$i % 8][(int)$num[$len - $i - 1]]];
        }
        return $inv === 0;
    }

    /**
     * Generate Verhoeff check digit for a given number
     */
    public function generateCheckDigit(string $num): int
    {
        $len = strlen($num);
        $inv = 0;
        for ($i = 0; $i < $len; $i++) {
            $inv = self::$d[$inv][self::$p[($i + 1) % 8][(int)$num[$len - $i - 1]]];
        }
        return self::$d[$inv][0];
    }

    /**
     * Estimate age band from DOB
     */
    private function estimateAgeBand(?string $dob): string
    {
        if (!$dob) return 'unknown';
        $age = (int)floor((time() - strtotime($dob)) / 31557600);
        if ($age < 18) return 'minor';
        if ($age < 30) return '18-29';
        if ($age < 40) return '30-39';
        if ($age < 50) return '40-49';
        if ($age < 60) return '50-59';
        return '60+';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->licenseKey);
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * Send OTP to registered Aadhaar mobile
     *
     * @param string $aadhaar 12-digit Aadhaar number
     * @return array {success, message, data: {transaction_id, masked_mobile}}
     */
    public function sendOtp(string $aadhaar): array
    {
        $aadhaar = preg_replace('/\D/', '', $aadhaar);
        if (strlen($aadhaar) !== 12) {
            return ['success' => false, 'message' => 'Aadhaar must be exactly 12 digits', 'data' => ['aadhaar' => $aadhaar]];
        }

        if ($this->testMode) {
            $txnId = 'MOCK-' . bin2hex(random_bytes(8));
            return [
                'success' => true,
                'message' => 'OTP sent successfully (mock)',
                'data' => [
                    'transaction_id' => $txnId,
                    'masked_mobile' => 'XXXXXX' . rand(1000, 9999),
                    'expires_in' => 300,
                ],
                'provider_response' => ['mock' => true],
            ];
        }

        if (!$this->verhoeffCheck($aadhaar)) {
            return ['success' => false, 'message' => 'Invalid Aadhaar number (checksum failed)', 'data' => ['aadhaar' => $aadhaar]];
        }

        return $this->callUidaiApiWithRetry('/aadhaar/send-otp', [
            'aadhaar' => $aadhaar,
        ]);
    }

    /**
     * Verify OTP sent to Aadhaar-linked mobile
     *
     * @param string $aadhaar 12-digit Aadhaar number
     * @param string $otp 6-digit OTP
     * @param string $txnId Transaction ID from sendOtp
     * @return array {success, message, data, provider_response}
     */
    public function verifyOtp(string $aadhaar, string $otp, string $txnId): array
    {
        $aadhaar = preg_replace('/\D/', '', $aadhaar);
        if (strlen($otp) !== 6 || !ctype_digit($otp)) {
            return ['success' => false, 'message' => 'OTP must be exactly 6 digits', 'data' => []];
        }

        if ($this->testMode) {
            return [
                'success' => true,
                'message' => 'Aadhaar OTP verified successfully (mock)',
                'data' => [
                    'aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4),
                    'status' => 'ACTIVE',
                    'verification_type' => 'otp',
                    'verified_at' => date('Y-m-d H:i:s'),
                ],
                'provider_response' => ['mock' => true, 'test_mode' => true],
            ];
        }

        return $this->callUidaiApiWithRetry('/aadhaar/verify-otp', [
            'aadhaar' => $aadhaar,
            'otp' => $otp,
            'transaction_id' => $txnId,
        ]);
    }

    /**
     * Call UIDAI API with retry (max 2 retries on 5xx/connection errors)
     */
    private function callUidaiApiWithRetry(string $endpoint, array $payload, int $maxRetries = 2): array
    {
        $lastError = null;
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            $result = $this->callUidaiApiRaw($endpoint, $payload);
            if ($result['success'] || ($result['data']['status'] ?? '') !== 'API_ERROR') {
                return $result;
            }
            $lastError = $result;
            if ($attempt < $maxRetries) {
                usleep(500000 * (1 << $attempt));
            }
        }
        return $lastError;
    }

    /**
     * Raw UIDAI API call (single attempt)
     */
    private function callUidaiApiRaw(string $endpoint, array $payload): array
    {
        $maskedAadhaar = 'XXXXXXXX' . substr($payload['aadhaar'] ?? '', -4);
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-UIDAI-API-Key: ' . $this->apiKey,
                'X-UIDAI-License-Key: ' . $this->licenseKey,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'UIDAI API connection error: ' . $error,
                'data' => ['aadhaar' => $maskedAadhaar, 'valid' => false, 'status' => 'API_ERROR'],
                'provider_response' => ['error' => $error],
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'UIDAI API returned HTTP ' . $httpCode,
                'data' => ['aadhaar' => $maskedAadhaar, 'valid' => false, 'status' => 'API_ERROR', 'http_code' => $httpCode],
                'provider_response' => ['http_code' => $httpCode, 'body' => $response],
            ];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Invalid response from UIDAI API',
                'data' => ['aadhaar' => $maskedAadhaar, 'valid' => false, 'status' => 'PARSE_ERROR'],
                'provider_response' => ['raw' => substr($response, 0, 500)],
            ];
        }

        $uidaiStatus = $result['status'] ?? 'UNKNOWN';
        $isSuccess = in_array($uidaiStatus, ['VALID', 'ACTIVE', 'verified', 'demographic_verified', 'otp_verified']);

        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'UIDAI verification successful' : 'UIDAI verification failed: ' . $uidaiStatus,
            'data' => [
                'aadhaar' => $maskedAadhaar,
                'name_match' => $result['name_match'] ?? false,
                'dob_match' => $result['dob_match'] ?? false,
                'gender' => $result['gender'] ?? '',
                'status' => $uidaiStatus,
                'verification_type' => $result['verification_type'] ?? 'demographic',
                'verified_at' => date('Y-m-d H:i:s'),
                'transaction_id' => $result['transaction_id'] ?? null,
                'masked_mobile' => $result['masked_mobile'] ?? null,
            ],
            'provider_response' => $result,
        ];
    }
}
