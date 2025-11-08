<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test class for API endpoints
 */
class ApiEndpointsTest extends TestCase
{
    private $baseUrl = 'http://localhost/apsdreamhome/api';
    private $testDb;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize test database connection
        $this->testDb = $this->createTestDatabaseConnection();
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestData();
        if ($this->testDb) {
            $this->testDb->close();
        }
    }

    /**
     * Test search properties endpoint
     */
    public function testSearchPropertiesEndpoint()
    {
        // Test with no parameters (should return all properties)
        $response = $this->makeRequest('search_properties.php');
        $this->assertEquals(200, $response['code'], 'Status code should be 200');
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('properties', $response['body']['data']);

        // Test with location filter
        $response = $this->makeRequest('search_properties.php?location=test');
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertIsArray($response['body']['data']['properties']);

        // Test with invalid parameters
        $response = $this->makeRequest('search_properties.php?min_price=invalid');
        $this->assertEquals(200, $response['code']);
    }

    /**
     * Test search endpoint
     */
    public function testSearchEndpoint()
    {
        $response = $this->makeRequest('search.php');
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting()
    {
        // Make multiple requests to trigger rate limiting
        $endpoint = 'search.php';
        $responses = [];
        
        // Make more requests than the rate limit allows
        for ($i = 0; $i < 20; $i++) {
            $responses[] = $this->makeRequest($endpoint, [], false);
        }
        
        // Check that we got rate limited at some point
        $rateLimited = false;
        foreach ($responses as $response) {
            if ($response['code'] === 429) {
                $rateLimited = true;
                break;
            }
        }
        
        $this->assertTrue($rateLimited, 'Expected to be rate limited after multiple requests');
    }

    /**
     * Helper method to make HTTP requests
     */
    private function makeRequest(string $endpoint, array $data = [], bool $decodeJson = true): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $ch = curl_init($url);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];

        if (!empty($data)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        if ($error) {
            throw new \RuntimeException("cURL Error: " . $error);
        }
        
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        return [
            'code' => $httpCode,
            'headers' => $this->parseHeaders($headers),
            'body' => $decodeJson ? json_decode($body, true) : $body
        ];
    }
    
    /**
     * Parse response headers into an associative array
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
    
    /**
     * Create a test database connection
     */
    private function createTestDatabaseConnection()
    {
        // Use a test database configuration
        $host = 'localhost';
        $username = 'root'; // Replace with your test DB username
        $password = '';     // Replace with your test DB password
        $database = 'apsdreamhome_test';
        
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            $this->markTestSkipped('Could not connect to the test database: ' . $conn->connect_error);
        }
        
        return $conn;
    }
    
    /**
     * Seed test data into the database
     */
    private function seedTestData(): void
    {
        // Create test properties table if it doesn't exist
        $this->testDb->query("
            CREATE TABLE IF NOT EXISTS properties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                location VARCHAR(255) NOT NULL,
                address VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                bedrooms INT NOT NULL,
                owner_contact VARCHAR(255),
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Insert test data
        $this->testDb->query("TRUNCATE TABLE properties");
        
        $stmt = $this->testDb->prepare("
            INSERT INTO properties (title, location, address, type, price, bedrooms) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $testProperties = [
            ['Test Property 1', 'Test Location', '123 Test St', 'house', 250000, 3],
            ['Test Property 2', 'Another Location', '456 Test Ave', 'apartment', 150000, 2],
            ['Test Property 3', 'Test Location', '789 Test Blvd', 'house', 350000, 4],
        ];
        
        foreach ($testProperties as $property) {
            $stmt->bind_param('ssssdi', ...$property);
            $stmt->execute();
        }
    }
    
    /**
     * Clean up test data
     */
    private function cleanupTestData(): void
    {
        if ($this->testDb) {
            $this->testDb->query("DROP TABLE IF EXISTS properties");
        }
    }
}
