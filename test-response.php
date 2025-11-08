<?php

// Include the Response class directly
require_once __DIR__ . '/app/Core/Http/Response.php';

// Simple test runner
class SimpleTestRunner {
    private $testCount = 0;
    private $failures = [];
    
    public function run() {
        $this->testBasicResponse();
        $this->testJsonResponse();
        $this->testResponseWithCustomStatusCode();
        $this->testCallableContent();
        $this->testResponseContentType();
        
        $this->printResults();
    }
    
    private function assertEquals($expected, $actual, $message = '') {
        $this->testCount++;
        if ($expected !== $actual) {
            $this->failures[] = [
                'test' => $message ?: "Test #{$this->testCount}",
                'expected' => $expected,
                'actual' => $actual
            ];
            echo "F";
        } else {
            echo ".";
        }
    }
    
    private function assertArrayHasKey($key, $array, $message = '') {
        $this->testCount++;
        if (!array_key_exists($key, $array)) {
            $this->failures[] = [
                'test' => $message ?: "Test #{$this->testCount}",
                'expected' => "Array to have key: " . $this->export($key),
                'actual' => 'Array keys: ' . implode(', ', array_keys($array))
            ];
            echo "F";
        } else {
            echo ".";
        }
    }
    
    private function assertEmpty($value, $message = '') {
        $this->testCount++;
        if (!empty($value)) {
            $this->failures[] = [
                'test' => $message ?: "Test #{$this->testCount}",
                'expected' => 'Empty value',
                'actual' => $this->export($value)
            ];
            echo "F";
        } else {
            echo ".";
        }
    }
    
    private function assertLessThan($expected, $actual, $message = '') {
        $this->testCount++;
        if ($actual >= $expected) {
            $this->failures[] = [
                'test' => $message ?: "Test #{$this->testCount}",
                'expected' => "Value less than " . $this->export($expected),
                'actual' => $this->export($actual)
            ];
            echo "F";
        } else {
            echo ".";
        }
    }
    
    private function assertStringContainsString($needle, $haystack, $message = '') {
        $this->testCount++;
        if (strpos($haystack, $needle) === false) {
            $this->failures[] = [
                'test' => $message ?: "Test #{$this->testCount}",
                'expected' => "String containing: " . $this->export($needle),
                'actual' => $this->export($haystack)
            ];
            echo "F";
        } else {
            echo ".";
        }
    }
    
    private function assertTrue($condition, $message = '') {
        $this->assertEquals(true, $condition, $message);
    }
    
    private function assertFalse($condition, $message = '') {
        $this->assertEquals(false, $condition, $message);
    }
    
    private function export($value) {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }
        return var_export($value, true);
    }
    
    private function printResults() {
        echo "\n\n";
        echo "Tests run: " . $this->testCount . ", ";
        echo "Failures: " . count($this->failures) . "\n\n";
        
        foreach ($this->failures as $i => $failure) {
            echo ($i + 1) . ") " . $failure['test'] . "\n";
            echo "   Expected: " . $failure['expected'] . "\n";
            echo "   Actual:   " . $failure['actual'] . "\n\n";
        }
    }
    
    // Test methods
    public function testBasicResponse() {
        $response = new App\Core\Http\Response('Hello, World!');
        $this->assertEquals('Hello, World!', (string) $response, 'Basic response content');
        $this->assertEquals(200, $response->getStatusCode(), 'Default status code');
        $this->assertEquals('OK', $response->getStatusText(), 'Default status text');
        
        $contentType = $response->getHeader('Content-Type');
        $this->assertStringContainsString('text/html', $contentType[0], 'Default content type');
        $this->assertStringContainsString('charset=UTF-8', $contentType[0], 'Default charset');
    }
    
    public function testJsonResponse() {
        $data = ['status' => 'success', 'message' => 'Operation completed'];
        $response = App\Core\Http\Response::json($data);
        
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), (string) $response, 'JSON response content');
        $this->assertEquals(200, $response->getStatusCode(), 'JSON response status code');
        
        $contentType = $response->getHeader('Content-Type');
        $this->assertStringContainsString('application/json', $contentType[0], 'JSON content type');
        $this->assertStringContainsString('charset=utf-8', $contentType[0], 'JSON charset');
    }
    
    public function testResponseWithCustomStatusCode() {
        $response = new App\Core\Http\Response('Not Found', 404);
        
        $this->assertEquals(404, $response->getStatusCode(), 'Custom status code');
        $this->assertEquals('Not Found', $response->getStatusText(), 'Custom status text');
    }
    
    public function testCallableContent() {
        $response = new App\Core\Http\Response(function() {
            echo 'Hello from callable';
        });
        
        $this->assertEquals('Hello from callable', $response->getContent(), 'Callable content');
        $this->assertEquals('Hello from callable', (string) $response, 'String cast of callable content');
    }
    
    public function testResponseContentType() {
        $response = new App\Core\Http\Response();
        
        // Test setting content type with default charset
        $response->setContentType('text/plain');
        $contentType = $response->getHeader('Content-Type');
        $this->assertStringContainsString('text/plain', $contentType[0], 'Plain text content type');
        $this->assertStringContainsString('charset=UTF-8', $contentType[0], 'Default charset');
        
        // Test setting content type with custom charset
        $response->setContentType('text/html', 'ISO-8859-1');
        $contentType = $response->getHeader('Content-Type');
        $this->assertStringContainsString('text/html', $contentType[0], 'HTML content type');
        $this->assertStringContainsString('charset=ISO-8859-1', $contentType[0], 'Custom charset');
        
        // Test setting content type that shouldn't have charset
        $response->setContentType('image/png');
        $contentType = $response->getHeader('Content-Type');
        $this->assertEquals('image/png', $contentType[0], 'Image content type without charset');
    }
    
    /**
     * Test response cookies
     */
    public function testResponseCookies() {
        $response = new App\Core\Http\Response();
        $expires = time() + 3600;
        
        // Test setting a cookie
        $response->setCookie('test_cookie', 'test_value', [
            'expires' => $expires,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Verify cookie was set
        $this->assertTrue($response->hasCookie('test_cookie'), 'Cookie should be set');
        
        // Verify cookie values
        $cookies = $response->getCookies();
        $this->assertArrayHasKey('test_cookie', $cookies, 'Cookie should exist in cookies array');
        $this->assertEquals('test_value', $cookies['test_cookie']['value'], 'Cookie value should match');
        $this->assertEquals($expires, $cookies['test_cookie']['options']['expires'], 'Cookie expiry should match');
        $this->assertEquals('/test', $cookies['test_cookie']['options']['path'], 'Cookie path should match');
        $this->assertEquals('example.com', $cookies['test_cookie']['options']['domain'], 'Cookie domain should match');
        $this->assertTrue($cookies['test_cookie']['options']['secure'], 'Cookie secure flag should be true');
        $this->assertTrue($cookies['test_cookie']['options']['httponly'], 'Cookie httpOnly flag should be true');
        $this->assertEquals('Strict', $cookies['test_cookie']['options']['samesite'], 'Cookie samesite should be Strict');
        
        // Test removing a cookie
        $response->removeCookie('test_cookie');
        $this->assertFalse($response->hasCookie('test_cookie'), 'Cookie should be removed');
        
        // Verify cookie was marked for deletion
        $cookies = $response->getCookies();
        $this->assertArrayHasKey('test_cookie', $cookies, 'Cookie should still exist for deletion');
        $this->assertEmpty($cookies['test_cookie']['value'], 'Cookie value should be empty for deletion');
        $this->assertLessThan(time(), $cookies['test_cookie']['options']['expires'], 'Cookie should be expired');
    }
}

// Run the tests
$runner = new SimpleTestRunner();
$runner->run();
