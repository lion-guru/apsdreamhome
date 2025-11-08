<?php

namespace Tests\Unit\Http;

use App\Core\Http\Response;
use RuntimeException;
use InvalidArgumentException;

// Custom TestCase class with necessary assertion methods
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    // Alias for assertSame for better readability
    protected function assertSame($expected, $actual, string $message = ''): void
    {
        parent::assertSame($expected, $actual, $message);
    }
    
    // Alias for assertEquals for better readability
    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        parent::assertEquals($expected, $actual, $message);
    }
    
    // Alias for assertTrue for better readability
    protected function assertTrue($condition, string $message = ''): void
    {
        parent::assertTrue($condition, $message);
    }
    
    // Alias for assertFalse for better readability
    protected function assertFalse($condition, string $message = ''): void
    {
        parent::assertFalse($condition, $message);
    }
    
    // Alias for assertEmpty for better readability
    protected function assertEmpty($actual, string $message = ''): void
    {
        parent::assertEmpty($actual, $message);
    }
    
    // Alias for assertCount for better readability
    protected function assertCount(int $expectedCount, $haystack, string $message = ''): void
    {
        parent::assertCount($expectedCount, $haystack, $message);
    }
    
    // Alias for assertArrayHasKey for better readability
    protected function assertArrayHasKey($key, $array, string $message = ''): void
    {
        parent::assertArrayHasKey($key, $array, $message);
    }
    
    // Alias for assertStringContainsString for better readability
    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        parent::assertStringContainsString($needle, $haystack, $message);
    }
    
    // Alias for assertNotFalse for better readability
    protected function assertNotFalse($condition, string $message = ''): void
    {
        parent::assertNotFalse($condition, $message);
    }
    
    // Alias for expectException for better readability
    protected function expectException(string $exception): void
    {
        parent::expectException($exception);
    }
    
    /**
     * Assert that an array has all the specified keys.
     *
     * @param array $keys
     * @param array $array
     * @param string $message
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "The array does not have the key: {$key}");
        }
    }
    
    /**
     * Assert that a string contains a given substring.
     *
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function assertStringContains($needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }
    
    /**
     * Assert that a variable is an array.
     *
     * @param mixed $actual
     * @param string $message
     */
    protected function assertIsArray($actual, string $message = ''): void
    {
        $this->assertTrue(is_array($actual), $message ?: 'Expected value to be an array');
    }
}

/**
 * @covers \App\Core\Http\Response
 */
class ResponseTest extends TestCase
{
    /**
     * Test creating a basic response
     */
    public function testBasicResponse()
    {
        $response = new Response('Hello, World!');
        
        $this->assertSame('Hello, World!', (string) $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getStatusText());
        
        $contentType = $response->getHeader('Content-Type');
        $this->assertIsArray($contentType);
        $this->assertArrayHasKey(0, $contentType);
        $this->assertSame('text/html; charset=UTF-8', $contentType[0]);
    }
    
    /**
     * Test JSON response
     */
    public function testJsonResponse()
    {
        $data = ['status' => 'success', 'message' => 'Operation completed'];
        $response = Response::json($data);
        
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), (string) $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->getHeader('Content-Type')[0]);
    }
    
    /**
     * Test JSON response with custom options
     */
    public function testJsonResponseWithCustomOptions()
    {
        $data = ['key' => 'value', 'number' => 42];
        
        // Test with pretty print
        $response = Response::json($data, 200, [], JSON_PRETTY_PRINT);
        $this->assertStringContainsString("\n", (string) $response);
        
        // Test with custom headers
        $response = Response::json($data, 200, ['X-Custom-Header' => 'Custom Value']);
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertEquals('Custom Value', $response->getHeader('X-Custom-Header')[0]);
    }
    
    /**
     * Test response with custom status code
     */
    public function testResponseWithCustomStatusCode()
    {
        $response = new Response('Not Found', 404);
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getStatusText());
    }
    
    /**
     * Test response with callable content
     */
    public function testCallableContent()
    {
        $response = new Response(function() {
            echo 'Hello from callable';
        });
        
        $this->assertEquals('Hello from callable', $response->getContent());
        $this->assertEquals('Hello from callable', (string) $response);
    }
    
    /**
     * Test response with cookies
     */
    public function testResponseCookies()
    {
        $response = new Response('With Cookie');
        $expires = time() + 3600;
        
        $response->setCookie('test_cookie', 'cookie_value', [
            'expires' => $expires,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        $this->assertTrue($response->hasHeader('Set-Cookie'));
        $cookies = $response->getHeader('Set-Cookie');
        
        $this->assertStringContainsString('test_cookie=cookie_value', $cookies[0]);
        $this->assertStringContainsString('path=/test', $cookies[0]);
        $this->assertStringContainsString('domain=example.com', $cookies[0]);
        $this->assertStringContainsString('expires=' . gmdate('D, d M Y H:i:s', $expires) . ' GMT', $cookies[0]);
        $this->assertStringContainsString('secure', $cookies[0]);
        $this->assertStringContainsString('httponly', $cookies[0]);
        $this->assertStringContainsString('samesite=Strict', $cookies[0]);
        
        // Test cookie removal
        $response->removeCookie('test_cookie');
        $cookies = $response->getHeader('Set-Cookie');
        $this->assertStringContainsString('test_cookie=deleted', $cookies[1]);
        $this->assertStringContainsString('expires=Thu, 01 Jan 1970 00:00:00 GMT', $cookies[1]);
    }
    
    /**
     * Test response content type
     */
    public function testResponseContentType()
    {
        $response = new Response();
        
        // Test setting content type with default charset
        $response->setContentType('text/plain');
        $this->assertEquals('text/plain; charset=UTF-8', $response->getHeader('Content-Type')[0]);
        
        // Test setting content type with custom charset
        $response->setContentType('text/html', 'ISO-8859-1');
        $this->assertEquals('text/html; charset=ISO-8859-1', $response->getHeader('Content-Type')[0]);
        
        // Test setting content type that shouldn't have charset
        $response->setContentType('image/png');
        $this->assertEquals('image/png', $response->getHeader('Content-Type')[0]);
    }
    
    /**
     * Test response with invalid content type
     */
    public function testInvalidContentType()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $response = new Response();
        $response->setContentType('invalid/content-type');
    }
    
    /**
     * Test response headers
     */
    public function testResponseHeaders()
    {
        $response = new Response('Test');
        
        // Test setting and getting a single header
        $response->setHeader('X-Custom-Header', 'Test Value');
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertEquals('Test Value', $response->getHeader('X-Custom-Header')[0]);
        
        // Test setting multiple values for a header
        $response->setHeader('X-Multi-Header', ['Value1', 'Value2']);
        $this->assertTrue($response->hasHeader('X-Multi-Header'));
        $this->assertCount(2, $response->getHeader('X-Multi-Header'));
        $this->assertEquals('Value1', $response->getHeader('X-Multi-Header')[0]);
        $this->assertEquals('Value2', $response->getHeader('X-Multi-Header')[1]);
        
        // Test header removal
        $response->removeHeader('X-Custom-Header');
        $this->assertFalse($response->hasHeader('X-Custom-Header'));
        
        // Test getting non-existent header
        $this->assertEmpty($response->getHeader('Non-Existent-Header'));
    }
    
    /**
     * Test streamed response
     */
    public function testStreamedResponse()
    {
        $output = [];
        $response = new Response(function() use (&$output) {
            $output[] = 'First chunk';
            echo 'First chunk';
            flush();
            
            $output[] = 'Second chunk';
            echo 'Second chunk';
            flush();
        });
        
        ob_start();
        $response->sendContent();
        $result = ob_get_clean();
        
        $this->assertEquals('First chunkSecond chunk', $result);
        $this->assertEquals(['First chunk', 'Second chunk'], $output);
    }
    
    /**
     * Test file response
     */
    public function testFileResponse()
    {
        $filePath = __DIR__ . '/test_file.txt';
        file_put_contents($filePath, 'Test file content');
        
        try {
            $response = new Response();
            $response->file($filePath);
            
            $this->assertEquals('Test file content', $response->getContent());
            $this->assertStringContainsString('text/plain', $response->getHeader('Content-Type')[0]);
            $this->assertStringContainsString('attachment', $response->getHeader('Content-Disposition')[0]);
            $this->assertStringContainsString('test_file.txt', $response->getHeader('Content-Disposition')[0]);
            
            // Test with custom filename and disposition
            $response->file($filePath, 'custom.txt', 'inline');
            $this->assertStringContainsString('inline', $response->getHeader('Content-Disposition')[0]);
            $this->assertStringContainsString('custom.txt', $response->getHeader('Content-Disposition')[0]);
            
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    /**
     * Test response caching
     */
    /**
     * Test JSON serialization of the response
     */
    public function testJsonSerialization()
    {
        $response = new Response('Test content', 201);
        $response->setContentType('application/json')
                ->setEtag('test-etag')
                ->setLastModified(new \DateTime('2023-01-01 12:00:00'));
        
        $data = $response->jsonSerialize();
        
        // Check if data is an array
        $this->assertIsArray($data, 'jsonSerialize() should return an array');
        
        // Check if required keys exist using the base class method
        $requiredKeys = ['status', 'statusText', 'content', 'headers', 'version', 'charset'];
        $this->assertArrayHasKeys($requiredKeys, $data, 'Response array is missing required keys');
        
        // Check values
        $this->assertSame(201, $data['status'], 'Status code should be 201');
        $this->assertSame('Created', $data['statusText'], 'Status text should be "Created"');
        $this->assertSame('Test content', $data['content'], 'Content should match');
        $this->assertSame('1.1', $data['version'], 'Protocol version should be 1.1');
        $this->assertSame('UTF-8', $data['charset'], 'Charset should be UTF-8');
        
        // Check headers
        $this->assertIsArray($data['headers'], 'Headers should be an array');
        $this->assertArrayHasKey('Content-Type', $data['headers'], 'Content-Type header should be set');
        
        // Test JSON encoding of the response
        $json = json_encode($response);
        $this->assertNotFalse($json, 'json_encode should succeed');
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded, 'Decoded JSON should be an array');
        $this->assertArrayHasKey('content', $decoded, 'Decoded response should have content key');
        $this->assertSame('Test content', $decoded['content'], 'Decoded content should match');
    }
    
    /**
     * Test response caching
     */
    public function testResponseCaching()
    {
        $response = new Response('Cached content');
        
        // Test ETag
        $response->setEtag('test-etag');
        $this->assertEquals('"test-etag"', $response->getEtag());
        $this->assertStringContainsString('"test-etag"', $response->getHeader('ETag')[0]);
        
        // Test Last-Modified
        $date = new \DateTime('2023-01-01 12:00:00');
        $response->setLastModified($date);
        $this->assertEquals($date, $response->getLastModified());
        $this->assertStringContainsString('Sun, 01 Jan 2023 12:00:00 GMT', $response->getHeader('Last-Modified')[0]);
        
        // Test Cache-Control
        $response->setPublic();
        $this->assertStringContainsString('public', $response->getHeader('Cache-Control')[0]);
        
        $response->setPrivate();
        $this->assertStringContainsString('private', $response->getHeader('Cache-Control')[0]);
        
        $response->setMaxAge(3600);
        $this->assertStringContainsString('max-age=3600', $response->getHeader('Cache-Control')[0]);
        
        $response->setSharedMaxAge(1800);
        $this->assertStringContainsString('s-maxage=1800', $response->getHeader('Cache-Control')[0]);
        
        // Test isNotModified
        $request = new \App\Core\Http\Request();
        $request->headers['If-None-Match'] = '"test-etag"';
        $this->assertTrue($response->isNotModified($request));
        
        // Test setNotModified
        $response->setNotModified();
        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEmpty($response->getHeader('Content-Type'));
        $this->assertEmpty($response->getHeader('Content-Length'));
    }
}
