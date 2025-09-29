# API Tests

This directory contains automated tests for the APS Dream Home API.

## Test Structure

- `functional/` - Functional tests for API endpoints
- `unit/` - Unit tests for individual components
- `integration/` - Integration tests
- `bootstrap.php` - Test bootstrap file
- `phpunit.xml` - PHPUnit configuration

## Running Tests

### Prerequisites

1. PHP 8.0 or higher
2. Composer
3. MySQL/MariaDB
4. PHP extensions: pdo_mysql, mbstring, xml, json, ctype

### Setup

1. Create a test database:
   ```sql
   CREATE DATABASE apsdreamhome_test;
   GRANT ALL PRIVILEGES ON apsdreamhome_test.* TO 'testuser'@'localhost' IDENTIFIED BY 'testpass';
   FLUSH PRIVILEGES;
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

### Running Tests

Run all tests:
```bash
./vendor/bin/phpunit
```

Run specific test suite:
```bash
./vendor/bin/phpunit --testsuite=Functional
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Integration
```

Run specific test file:
```bash
./vendor/bin/phpunit tests/functional/ApiEndpointsTest.php
```

Run with coverage report:
```bash
./vendor/bin/phpunit --coverage-html coverage-report
```

## Test Environment

The test environment uses the following configuration (can be overridden in `phpunit.xml`):

- Database: `apsdreamhome_test`
- Database User: `testuser`
- Database Password: `testpass`
- API Base URL: `http://localhost/apsdreamhomefinal/api`

## Writing Tests

### Test Naming Convention
- Test classes: `{ClassName}Test.php`
- Test methods: `test{MethodName}()` or `it_{should_do_something}()`

### Example Test

Here's a comprehensive example of testing the Property API endpoints:

```php
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
            'localhost',
            'testuser',
            'testpass',
            'apsdreamhome_test'
        );

        if (self::$testDb->connect_error) {
            throw new \RuntimeException("Connection failed: " . self::$testDb->connect_error);
        }

        // Create test data
        self::seedTestData();
    }

    /**
     * Clean up after all tests
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$testDb) {
            // Clean up test data
            self::cleanupTestData();
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
            'zip_code' => '12345'
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

    /**
     * Seed test data
     */
    private static function seedTestData(): void
    {
        $testProperties = [
            ['Test Property 1', 'Test Location 1', 'house', 250000, 3, 2.5, 1800],
            ['Test Property 2', 'Test Location 2', 'apartment', 150000, 2, 1, 900],
            ['Test Property 3', 'Test Location 3', 'house', 350000, 4, 3, 2200],
        ];
        
        $stmt = self::$testDb->prepare("
            INSERT INTO properties 
            (title, location, type, price, bedrooms, bathrooms, area, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        foreach ($testProperties as $property) {
            $stmt->bind_param('sssddid', ...$property);
            $stmt->execute();
            self::$testProperties[] = $stmt->insert_id;
        }
    }

    /**
     * Clean up test data
     */
    private static function cleanupTestData(): void
    {
        if (!empty(self::$testProperties)) {
            $ids = implode(',', array_map('intval', self::$testProperties));
            self::$testDb->query("DELETE FROM properties WHERE id IN ($ids)");
        }
    }
}
```

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
```

## Continuous Integration

GitHub Actions is configured to run tests on push and pull requests. The workflow file is located at `.github/workflows/php.yml`.

## Code Coverage

To generate a code coverage report:

```bash
./vendor/bin/phpunit --coverage-html coverage-report
```

Open `coverage-report/index.html` in your browser to view the report.

## Troubleshooting

### Database Connection Issues
- Verify the test database exists and is accessible
- Check database credentials in `phpunit.xml`
- Ensure the MySQL service is running

### Test Failures
- Run tests with `--debug` flag for more information
- Check test database state before and after tests
- Verify API endpoints are accessible

## License

This test suite is part of the APS Dream Home project.
