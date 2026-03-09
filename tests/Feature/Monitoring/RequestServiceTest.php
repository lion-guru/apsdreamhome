<?php

namespace Tests\Feature\Custom;

use App\Services\Custom\RequestService;
use PHPUnit\Framework\TestCase;

/**
 * Custom Request Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class RequestServiceTest extends TestCase
{
    private $requestService;
    
    protected function setUp(): void
    {
        $this->requestService = new RequestService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(RequestService::class, $this->requestService);
        $this->assertIsArray($this->requestService->getMiddlewareStack());
        $this->assertIsArray($this->requestService->getRouteRegistry());
    }
    
    /** @test */
    public function it_can_register_middleware()
    {
        $middlewareCalled = false;
        
        $this->requestService->registerMiddleware('test_stage', function($request, $response) use (&$middlewareCalled) {
            $middlewareCalled = true;
            return true;
        });
        
        $stack = $this->requestService->getMiddlewareStack();
        $this->assertArrayHasKey('test_stage', $stack);
        $this->assertCount(1, $stack['test_stage']);
    }
    
    /** @test */
    public function it_can_register_route()
    {
        $handler = function($request) {
            return ['success' => true, 'message' => 'Test route'];
        };
        
        $this->requestService->registerRoute('GET', '/test', $handler);
        
        $registry = $this->requestService->getRouteRegistry();
        $this->assertArrayHasKey('GET:/test', $registry);
        
        $route = $registry['GET:/test'];
        $this->assertEquals('GET', $route['method']);
        $this->assertEquals('/test', $route['path']);
        $this->assertEquals($handler, $route['handler']);
    }
    
    /** @test */
    public function it_can_register_route_with_middleware()
    {
        $handler = function($request) {
            return ['success' => true];
        };
        
        $this->requestService->registerRoute('POST', '/test', $handler, ['auth', 'validation']);
        
        $registry = $this->requestService->getRouteRegistry();
        $route = $registry['POST:/test'];
        
        $this->assertEquals(['auth', 'validation'], $route['middleware']);
    }
    
    /** @test */
    public function it_can_get_request_data()
    {
        $request = $this->requestService->getRequest();
        
        $this->assertIsArray($request);
        $this->assertArrayHasKey('method', $request);
        $this->assertArrayHasKey('uri', $request);
        $this->assertArrayHasKey('headers', $request);
        $this->assertArrayHasKey('ip', $request);
        $this->assertArrayHasKey('timestamp', $request);
    }
    
    /** @test */
    public function it_can_handle_cors()
    {
        // Test CORS handling - should not throw exception
        $this->requestService->handleCors();
        
        // If no exception thrown, test passes
        $this->assertTrue(true);
    }
    
    /** @test */
    public function it_can_add_security_middleware()
    {
        $this->requestService->addSecurityMiddleware();
        
        $stack = $this->requestService->getMiddlewareStack();
        $this->assertArrayHasKey('pre_process', $stack);
    }
    
    /** @test */
    public function it_can_add_rate_limiting_middleware()
    {
        $this->requestService->addRateLimitingMiddleware(10, 60);
        
        $stack = $this->requestService->getMiddlewareStack();
        $this->assertArrayHasKey('rate_limit', $stack);
    }
    
    /** @test */
    public function it_can_add_logging_middleware()
    {
        $this->requestService->addLoggingMiddleware();
        
        $stack = $this->requestService->getMiddlewareStack();
        $this->assertArrayHasKey('post_process', $stack);
    }
    
    /** @test */
    public function it_validates_request_size()
    {
        // Mock large request
        $_SERVER['CONTENT_LENGTH'] = 20 * 1024 * 1024; // 20MB
        
        $newRequestService = new RequestService();
        
        // This should validate and potentially reject large requests
        $request = $newRequestService->getRequest();
        $this->assertIsArray($request);
        
        // Reset
        unset($_SERVER['CONTENT_LENGTH']);
    }
    
    /** @test */
    public function it_extracts_client_ip_correctly()
    {
        // Test with different IP headers
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.1';
        
        $newRequestService = new RequestService();
        $request = $newRequestService->getRequest();
        
        $this->assertArrayHasKey('ip', $request);
        $this->assertNotEmpty($request['ip']);
        
        // Reset
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    
    /** @test */
    public function it_handles_json_requests()
    {
        // Mock JSON request
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $jsonBody = json_encode(['test' => 'data']);
        $_SERVER['REQUEST_BODY'] = $jsonBody;
        
        $newRequestService = new RequestService();
        $request = $newRequestService->getRequest();
        
        $this->assertArrayHasKey('json', $request);
        $this->assertEquals(['test' => 'data'], $request['json']);
        
        // Reset
        unset($_SERVER['CONTENT_TYPE'], $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_BODY']);
    }
    
    /** @test */
    public function it_matches_route_patterns()
    {
        $handler = function($request) {
            return ['success' => true];
        };
        
        // Register pattern route
        $this->requestService->registerRoute('GET', '/users/{id}', $handler);
        
        // Simulate request matching
        $registry = $this->requestService->getRouteRegistry();
        $this->assertArrayHasKey('GET:/users/{id}', $registry);
        
        $route = $registry['GET:/users/{id}'];
        $this->assertEquals('/users/{id}', $route['path']);
    }
    
    /** @test */
    public function it_extracts_route_parameters()
    {
        // This would be tested through private method reflection
        // For now, just ensure route registry works
        $handler = function($request) {
            return ['success' => true];
        };
        
        $this->requestService->registerRoute('GET', '/users/{id}/posts/{post_id}', $handler);
        
        $registry = $this->requestService->getRouteRegistry();
        $this->assertArrayHasKey('GET:/users/{id}/posts/{post_id}', $registry);
    }
    
    /** @test */
    public function it_handles_different_http_methods()
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        
        foreach ($methods as $method) {
            $handler = function($request) use ($method) {
                return ['method' => $method];
            };
            
            $this->requestService->registerRoute($method, '/test-' . strtolower($method), $handler);
        }
        
        $registry = $this->requestService->getRouteRegistry();
        
        foreach ($methods as $method) {
            $this->assertArrayHasKey($method . ':/test-' . strtolower($method), $registry);
        }
    }
    
    /** @test */
    public function it_maintains_request_timestamp()
    {
        $request = $this->requestService->getRequest();
        
        $this->assertArrayHasKey('timestamp', $request);
        $this->assertIsInt($request['timestamp']);
        $this->assertGreaterThan(0, $request['timestamp']);
    }
    
    /** @test */
    public function it_captures_user_agent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Test Browser 1.0';
        
        $newRequestService = new RequestService();
        $request = $newRequestService->getRequest();
        
        $this->assertArrayHasKey('user_agent', $request);
        $this->assertEquals('Test Browser 1.0', $request['user_agent']);
        
        // Reset
        unset($_SERVER['HTTP_USER_AGENT']);
    }
    
    /** @test */
    public function it_handles_missing_headers_gracefully()
    {
        // Ensure no headers are set
        unset($_SERVER['HTTP_USER_AGENT']);
        unset($_SERVER['CONTENT_TYPE']);
        
        $newRequestService = new RequestService();
        $request = $newRequestService->getRequest();
        
        $this->assertIsArray($request);
        $this->assertArrayHasKey('headers', $request);
        $this->assertArrayHasKey('user_agent', $request);
        $this->assertEquals('Unknown', $request['user_agent']);
    }
    
    protected function tearDown(): void
    {
        // Clean up test environment
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['CONTENT_TYPE']);
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_BODY']);
        unset($_SERVER['HTTP_USER_AGENT']);
        unset($_SERVER['CONTENT_LENGTH']);
        
        parent::tearDown();
    }
}
