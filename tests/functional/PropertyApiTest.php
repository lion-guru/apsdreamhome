<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test class for Property API endpoints
 */
class PropertyApiTest extends TestCase
{
    private static $testDb;
    private static $testProperties = [];
    private static $apiBaseUrl = 'http://localhost/apsdreamhomefinal/api';
    private static $apiKey = 'test_api_key';

    /**
     * Set up before any tests run
     */
    public static function setUpBeforeClass(): void
    {
        // Set up test database connection
        self::$testDb = new mysqli(
            TEST_DB_HOST,
            TEST_DB_USER,
            TEST_DB_PASS,
            TEST_DB_NAME
        );

        if (self::$testDb->connect_error) {
            throw new \RuntimeException("Connection failed: " . self::$testDb->connect_error);
        }
    }

    /**
     * Clean up after all tests
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$testDb) {
            self::$testDb->close();
        }
    }

    /**
     * Test GET /api/properties endpoint
     */
    public function testGetProperties(): void
    {
        $response = $this->makeRequest('GET', '/properties');
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertIsArray($response['body']['data']);
        $this->assertGreaterThan(0, count($response['body']['data']));
    }

    /**
     * Test GET /api/properties with filters
     */
    public function testGetPropertiesWithFilters(): void
    {
        $query = http_build_query([
            'min_price' => 200000,
            'max_price' => 300000,
            'bedrooms' => 3
        ]);
        
        $response = $this->makeRequest('GET', "/properties?$query");
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
        
        // Verify filters were applied
        foreach ($response['body']['data'] as $property) {
            $this->assertGreaterThanOrEqual(200000, $property['price']);
            $this->assertLessThanOrEqual(300000, $property['price']);
            $this->assertGreaterThanOrEqual(3, $property['bedrooms']);
        }
    }

    /**
     * Test POST /api/properties (create new property)
     */
    public function testCreateProperty(): void
    {
        $testData = [
            'title' => 'Test Property',
            'description' => 'This is a test property',
            'price' => 275000.50,
            'bedrooms' => 3,
            'bathrooms' => 2.5,
            'area' => 1800,
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'type' => 'house'
        ];
        
        $response = $this->makeRequest('POST', '/properties', $testData);
        
        $this->assertEquals(201, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertEquals($testData['title'], $response['body']['data']['title']);
        $this->assertEquals($testData['price'], $response['body']['data']['price']);
        
        // Store for cleanup
        self::$testProperties[] = $response['body']['data']['id'];
    }

    /**
     * Test validation for POST /api/properties
     */
    public function testCreatePropertyValidation(): void
    {
        $invalidData = [
            'title' => '',  // Empty title should fail validation
            'price' => -100  // Negative price should fail validation
        ];
        
        $response = $this->makeRequest('POST', '/properties', $invalidData);
        
        $this->assertEquals(400, $response['code']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertArrayHasKey('title', $response['body']['errors']);
        $this->assertArrayHasKey('price', $response['body']['errors']);
    }

    /**
     * Helper method to make HTTP requests
     */
    private function makeRequest(string $method, string $endpoint, array $data = null): array
    {
        $url = rtrim(self::$apiBaseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $ch = curl_init($url);
        
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::$apiKey
        ];
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ];
        
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        return [
            'code' => $httpCode,
            'headers' => $this->parseHeaders($headers),
            'body' => json_decode($body, true) ?: $body
        ];
    }
    
    /**
     * Parse response headers
     */
    private function parseHeaders(string $headers): array
    {
        $headersArray = [];
        $headers = explode("\r\n", $headers);
        
        foreach ($headers as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $headersArray[trim($key)] = trim($value);
            }
        }
        
        return $headersArray;
    }
}
