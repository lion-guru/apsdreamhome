<?php

namespace App\Docs;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * API Testing Suite
 * Comprehensive testing framework for APS Dream Home API
 */
class ApiTestingSuite
{
    private $db;
    private $config;
    private $baseUrl;
    private $testResults = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
        $this->baseUrl = $this->config->get('app_url', 'http://localhost/apsdreamhome') . '/api';
    }

    /**
     * Run complete API test suite
     * @return array Test results
     */
    public function runTestSuite()
    {
        try {
            $this->logTest('Starting API Test Suite...');
            
            // Test authentication endpoints
            $authResults = $this->testAuthenticationEndpoints();
            
            // Test property endpoints
            $propertyResults = $this->testPropertyEndpoints();
            
            // Test banking endpoints
            $bankingResults = $this->testBankingEndpoints();
            
            // Test communication endpoints
            $communicationResults = $this->testCommunicationEndpoints();
            
            $allResults = array_merge($authResults, $propertyResults, $bankingResults, $communicationResults);
            
            $summary = [
                'total_tests' => count($allResults),
                'passed' => count(array_filter($allResults, function($result) {
                    return $result['status'] === 'passed';
                })),
                'failed' => count(array_filter($allResults, function($result) {
                    return $result['status'] === 'failed';
                })),
                'success_rate' => count($allResults) > 0 ? round((count(array_filter($allResults, function($result) {
                    return $result['status'] === 'passed';
                })) / count($allResults)) * 100, 2) : 0
            ];

            $this->logTest('Test Suite Completed. Results: ' . json_encode($summary));

            return [
                'success' => true,
                'test_suite' => [
                    'started_at' => date('Y-m-d H:i:s'),
                    'completed_at' => date('Y-m-d H:i:s'),
                    'summary' => $summary,
                    'results' => $allResults
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Test suite failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test authentication endpoints
     * @return array Test results
     */
    private function testAuthenticationEndpoints()
    {
        $results = [];
        
        // Test user registration
        $results[] = $this->makeTestRequest('POST', '/api/auth/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'TestPass123!',
            'phone' => '9876543210'
        ], 'User Registration Test');

        // Test user login
        $results[] = $this->makeTestRequest('POST', '/api/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'TestPass123!'
        ], 'User Login Test');

        // Test invalid login
        $results[] = $this->makeTestRequest('POST', '/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ], 'Invalid Login Test', true);

        return $results;
    }

    /**
     * Test property endpoints
     * @return array Test results
     */
    private function testPropertyEndpoints()
    {
        $results = [];
        
        // Test get all properties
        $results[] = $this->makeTestRequest('GET', '/api/properties', [], 'Get Properties Test');
        
        // Test get property by ID
        $results[] = $this->makeTestRequest('GET', '/api/properties/1', [], 'Get Property by ID Test');
        
        // Test create property (requires auth)
        $results[] = $this->makeTestRequest('POST', '/api/properties', [
            'title' => 'Test Property',
            'description' => 'Test property description',
            'price' => 150000,
            'type' => 'apartment',
            'location' => 'Test Location'
        ], 'Create Property Test');

        return $results;
    }

    /**
     * Test banking endpoints
     * @return array Test results
     */
    private function testBankingEndpoints()
    {
        $results = [];
        
        // Test save banking details
        $results[] = $this->makeTestRequest('POST', '/api/banking/save', [
            'account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'ifsc_code' => 'TEST0001234',
            'account_type' => 'savings'
        ], 'Save Banking Details Test');

        return $results;
    }

    /**
     * Test communication endpoints
     * @return array Test results
     */
    private function testCommunicationEndpoints()
    {
        $results = [];
        
        // Test send email
        $results[] = $this->makeTestRequest('POST', '/api/communication/send-email', [
            'to' => 'test@example.com',
            'subject' => 'Test Email',
            'message' => 'This is a test email from the API test suite.'
        ], 'Send Email Test');

        // Test send WhatsApp
        $results[] = $this->makeTestRequest('POST', '/api/communication/send-whatsapp', [
            'to' => '+919876543210',
            'message' => 'Test WhatsApp message',
            'type' => 'text'
        ], 'Send WhatsApp Test');

        return $results;
    }

    /**
     * Make test request to API endpoint
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $testName Test name
     * @param bool $expectError Whether to expect error response
     * @return array Test result
     */
    private function makeTestRequest($method, $endpoint, $data = [], $testName = '', $expectError = false)
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $headers = [
                'Content-Type: application/json',
                'X-API-Key: test-api-key-here'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);
            $success = !$expectError && ($httpCode >= 200 && $httpCode < 300);

            $result = [
                'test_name' => $testName,
                'endpoint' => $endpoint,
                'method' => $method,
                'request_data' => $data,
                'http_code' => $httpCode,
                'response' => $responseData,
                'status' => $success ? 'passed' : 'failed',
                'message' => $success ? 'Test passed' : 'Expected error occurred' . ($expectError ? ' but got success' : ''),
                'execution_time' => microtime(true)
            ];

            $this->logTest(sprintf(
                '%s: %s - %s (%d)',
                $testName,
                $endpoint,
                $result['status'],
                $httpCode
            ));

            return $result;

        } catch (Exception $e) {
            return [
                'test_name' => $testName,
                'endpoint' => $endpoint,
                'method' => $method,
                'request_data' => $data,
                'http_code' => 0,
                'response' => null,
                'status' => 'failed',
                'message' => 'Test failed: ' . $e->getMessage(),
                'execution_time' => microtime(true)
            ];
        }
    }

    /**
     * Log test message
     * @param string $message Log message
     */
    private function logTest($message)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'api_test',
            'message' => $message
        ];
        
        error_log(json_encode($logEntry));
        echo $message . "\n";
    }
}
