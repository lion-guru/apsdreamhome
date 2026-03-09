<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Middleware\RequestMiddlewareService;
use App\Http\Controllers\RequestMiddlewareController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class RequestMiddlewareServiceTest extends TestCase
{
    use RefreshDatabase;

    private RequestMiddlewareService $middlewareService;
    private RequestMiddlewareController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middlewareService = app(RequestMiddlewareService::class);
        $this->controller = new RequestMiddlewareController($this->middlewareService);
    }

    /** @test */
    public function it_gets_request_metadata()
    {
        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Test Browser',
            'REMOTE_ADDR' => '127.0.0.1'
        ]);

        $metadata = $this->middlewareService->getRequestMetadata($request);

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('method', $metadata);
        $this->assertArrayHasKey('path', $metadata);
        $this->assertArrayHasKey('ip', $metadata);
        $this->assertEquals('GET', $metadata['method']);
        $this->assertEquals('test', $metadata['path']);
    }

    /** @test */
    public function it_gets_client_info()
    {
        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Test Browser',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'REMOTE_ADDR' => '127.0.0.1'
        ]);

        $clientInfo = $this->middlewareService->getClientInfo($request);

        $this->assertIsArray($clientInfo);
        $this->assertArrayHasKey('ip', $clientInfo);
        $this->assertArrayHasKey('user_agent', $clientInfo);
        $this->assertArrayHasKey('accept_language', $clientInfo);
        $this->assertEquals('127.0.0.1', $clientInfo['ip']);
        $this->assertEquals('Test Browser', $clientInfo['user_agent']);
    }

    /** @test */
    public function it_detects_suspicious_activity()
    {
        $request = Request::create('/test', 'POST', [
            'input' => '<script>alert("xss")</script>',
            'query' => 'SELECT * FROM users UNION SELECT password FROM admin'
        ]);

        $suspicious = $this->middlewareService->detectSuspiciousActivity($request);

        $this->assertIsArray($suspicious);
        $this->assertNotEmpty($suspicious);
        $this->assertContains('XSS attempt', $suspicious);
        $this->assertContains('SQL injection attempt', $suspicious);
    }

    /** @test */
    public function it_validates_json_request()
    {
        // Valid JSON request
        $validRequest = Request::create('/test', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], '{"test": "data"}');

        $this->assertTrue($this->middlewareService->validateJsonRequest($validRequest));

        // Invalid JSON request
        $invalidRequest = Request::create('/test', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], '{invalid json}');

        $this->assertFalse($this->middlewareService->validateJsonRequest($invalidRequest));

        // Non-JSON request
        $nonJsonRequest = Request::create('/test', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded'
        ]);

        $this->assertFalse($this->middlewareService->validateJsonRequest($nonJsonRequest));
    }

    /** @test */
    public function it_sanitizes_input()
    {
        $request = Request::create('/test', 'POST', [
            'clean' => 'safe data',
            'xss' => '<script>alert("xss")</script>',
            'nested' => [
                'safe' => 'data',
                'dangerous' => '<img src=x onerror=alert(1)>'
            ]
        ]);

        $sanitized = $this->middlewareService->sanitizeInput($request);

        $this->assertEquals('safe data', $sanitized->input('clean'));
        $this->assertStringNotContainsString('<script>', $sanitized->input('xss'));
        $this->assertStringNotContainsString('<img', $sanitized->input('nested.dangerous'));
    }

    /** @test */
    public function it_gets_middleware_stats()
    {
        $stats = $this->middlewareService->getMiddlewareStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('global_middleware_count', $stats);
        $this->assertArrayHasKey('route_middleware_count', $stats);
        $this->assertArrayHasKey('cors_enabled', $stats);
        $this->assertArrayHasKey('max_request_size', $stats);
        $this->assertArrayHasKey('allowed_origins', $stats);
        $this->assertIsInt($stats['global_middleware_count']);
        $this->assertIsInt($stats['route_middleware_count']);
        $this->assertIsBool($stats['cors_enabled']);
    }

    /** @test */
    public function it_registers_custom_middleware()
    {
        $customMiddleware = function($request, $next) {
            return $next($request);
        };

        $this->middlewareService->registerMiddleware('custom', $customMiddleware);
        
        $retrieved = $this->middlewareService->getMiddleware('custom');
        $this->assertNotNull($retrieved);
        $this->assertEquals($customMiddleware, $retrieved);
    }

    /** @test */
    public function it_handles_cors()
    {
        $request = Request::create('/test', 'OPTIONS', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000'
        ]);

        $next = function($req) {
            return response()->json(['test' => true]);
        };

        $response = $this->middlewareService->handleCors($request, $next);

        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Methods'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Headers'));
    }

    /** @test */
    public function it_validates_request_size()
    {
        // Normal size request
        $normalRequest = Request::create('/test', 'POST', [], [], [], [
            'CONTENT_LENGTH' => '1000'
        ]);

        $next = function($req) {
            return response()->json(['test' => true]);
        };

        $response = $this->middlewareService->validateRequestSize($normalRequest, $next);
        $this->assertEquals(200, $response->getStatusCode());

        // Oversized request
        $oversizedRequest = Request::create('/test', 'POST', [], [], [], [
            'CONTENT_LENGTH' => (string) (20 * 1024 * 1024) // 20MB
        ]);

        $response = $this->middlewareService->validateRequestSize($oversizedRequest, $next);
        $this->assertEquals(413, $response->getStatusCode());
    }

    /** @test */
    public function it_adds_security_headers()
    {
        $request = Request::create('/test', 'GET');

        $next = function($req) {
            return response()->json(['test' => true]);
        };

        $response = $this->middlewareService->addSecurityHeaders($request, $next);

        $this->assertTrue($response->headers->has('X-Content-Type-Options'));
        $this->assertTrue($response->headers->has('X-Frame-Options'));
        $this->assertTrue($response->headers->has('X-XSS-Protection'));
        $this->assertTrue($response->headers->has('Strict-Transport-Security'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
    }

    /** @test */
    public function request_middleware_api_endpoints_work()
    {
        // Test metadata endpoint
        $response = $this->getJson('/api/request-middleware/metadata');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test client info endpoint
        $response = $this->getJson('/api/request-middleware/client-info');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test suspicious detection endpoint
        $response = $this->getJson('/api/request-middleware/detect-suspicious');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test JSON validation endpoint
        $response = $this->postJson('/api/request-middleware/validate-json', [], [
            'Content-Type' => 'application/json'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test middleware stats endpoint
        $response = $this->getJson('/api/request-middleware/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test available middleware endpoint
        $response = $this->getJson('/api/request-middleware/available');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test endpoint
        $response = $this->getJson('/api/request-middleware/test');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_handles_suspicious_activity_detection_via_api()
    {
        $response = $this->postJson('/api/request-middleware/detect-suspicious', [
            'input' => '<script>alert("xss")</script>',
            'query' => 'UNION SELECT * FROM users'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_suspicious' => true
            ]
        ]);
    }

    /** @test */
    public function it_handles_input_sanitization_via_api()
    {
        $response = $this->postJson('/api/request-middleware/sanitize-input', [
            'clean' => 'safe data',
            'xss' => '<script>alert("xss")</script>'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->assertStringNotContainsString('<script>', $response->json('data.sanitized_input.xss'));
    }

    /** @test */
    public function it_handles_middleware_registration_via_api()
    {
        $response = $this->postJson('/api/request-middleware/register', [
            'name' => 'test_middleware_' . time(),
            'handler_type' => 'closure',
            'handler_code' => 'return $next($request);'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_validates_middleware_registration_data()
    {
        // Missing required fields
        $response = $this->postJson('/api/request-middleware/register', [
            'name' => 'test'
            // missing handler_type and handler_code
        ]);
        $response->assertStatus(422);

        // Invalid handler type
        $response = $this->postJson('/api/request-middleware/register', [
            'name' => 'test',
            'handler_type' => 'invalid',
            'handler_code' => 'return $next($request);'
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_middleware_application_via_api()
    {
        $response = $this->postJson('/api/request-middleware/apply', [
            'middleware' => ['cors', 'security.headers']
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_calculates_risk_levels_correctly()
    {
        // Test low risk (no suspicious activity)
        $request = Request::create('/test', 'POST', ['input' => 'safe data']);
        $suspicious = $this->middlewareService->detectSuspiciousActivity($request);
        
        // Use reflection to access private method for testing
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('calculateRiskLevel');
        $method->setAccessible(true);
        
        $riskLevel = $method->invoke($this->controller, $suspicious);
        $this->assertEquals('low', $riskLevel);

        // Test high risk (multiple suspicious activities)
        $request = Request::create('/test', 'POST', [
            'xss' => '<script>alert("xss")</script>',
            'sql' => 'UNION SELECT * FROM users',
            'js' => 'javascript:alert(1)',
            'handler' => 'onclick=alert(1)'
        ]);
        
        $suspicious = $this->middlewareService->detectSuspiciousActivity($request);
        $riskLevel = $method->invoke($this->controller, $suspicious);
        $this->assertEquals('high', $riskLevel);
    }

    /** @test */
    public function it_creates_middleware_responses()
    {
        $response = $this->middlewareService->createMiddlewareResponse('Test error', 400, ['extra' => 'data']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Test error', $content['error']);
        $this->assertEquals('data', $content['extra']);
        $this->assertArrayHasKey('timestamp', $content);
    }
}
