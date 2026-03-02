<?php

namespace App\Services;

/**
 * KYC Service
 * Handles PAN and Aadhaar verification via third-party APIs
 */
class KYCService
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;

    public function __construct()
    {
        // These should be in .env
        $this->apiKey = $_ENV['KYC_API_KEY'] ?? '';
        $this->apiSecret = $_ENV['KYC_API_SECRET'] ?? '';
        $this->baseUrl = $_ENV['KYC_API_BASE_URL'] ?? 'https://api.example-kyc.com/v1';
    }

    /**
     * Verify PAN Number
     * 
     * @param string $pan
     * @param string $name
     * @return array
     */
    public function verifyPAN($pan, $name = '')
    {
        // Basic format validation first
        if (!\preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
            return [
                'success' => false,
                'message' => 'Invalid PAN format'
            ];
        }

        // In a real implementation, you would make an API call here
        // Example with cURL:
        /*
        $ch = \curl_init($this->baseUrl . '/pan/verify');
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode([
            'pan' => $pan,
            'name' => $name
        ]));
        \curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ]);
        $response = \curl_exec($ch);
        return \json_decode($response, true);
        */

        // Mock response for now
        return [
            'success' => true,
            'message' => 'PAN verified successfully',
            'data' => [
                'pan' => $pan,
                'full_name' => $name ?: 'VALID USER',
                'status' => 'VALID'
            ]
        ];
    }

    /**
     * Verify Aadhaar Number (Basic format or via OTP if supported)
     * 
     * @param string $aadhaar
     * @return array
     */
    public function verifyAadhaar($aadhaar)
    {
        $aadhaar = \preg_replace('/\D/', '', $aadhaar);
        
        if (\strlen($aadhaar) !== 12) {
            return [
                'success' => false,
                'message' => 'Aadhaar must be 12 digits'
            ];
        }

        // In a real implementation, this might initiate an OTP or check validity
        // Mock response for now
        return [
            'success' => true,
            'message' => 'Aadhaar format verified successfully',
            'data' => [
                'aadhaar' => 'XXXXXXXX' . \substr($aadhaar, -4),
                'status' => 'VALID'
            ]
        ];
    }
}
