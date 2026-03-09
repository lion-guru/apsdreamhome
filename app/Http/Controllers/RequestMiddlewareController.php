<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RequestMiddlewareService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Controller for Request Middleware operations
 */
class RequestMiddlewareController extends BaseController
{
    private RequestMiddlewareService $middlewareService;

    public function __construct(RequestMiddlewareService $middlewareService)
    {
        $this->middlewareService = $middlewareService;
    }

    /**
     * Get request metadata
     */
    public function getRequestMetadata(Request $request): JsonResponse
    {
        try {
            $metadata = $this->middlewareService->getRequestMetadata($request);

            return response()->json([
                'success' => true,
                'data' => $metadata
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get request metadata',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client information
     */
    public function getClientInfo(Request $request): JsonResponse
    {
        try {
            $clientInfo = $this->middlewareService->getClientInfo($request);

            return response()->json([
                'success' => true,
                'data' => $clientInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get client information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect suspicious activity
     */
    public function detectSuspiciousActivity(Request $request): JsonResponse
    {
        try {
            $suspicious = $this->middlewareService->detectSuspiciousActivity($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'suspicious_activity' => $suspicious,
                    'is_suspicious' => !empty($suspicious),
                    'risk_level' => $this->calculateRiskLevel($suspicious)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detect suspicious activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate JSON request
     */
    public function validateJsonRequest(Request $request): JsonResponse
    {
        try {
            $isValid = $this->middlewareService->validateJsonRequest($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid_json' => $isValid,
                    'content_type' => $request->header('Content-Type'),
                    'content_length' => $request->header('Content-Length')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate JSON request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sanitize request input
     */
    public function sanitizeInput(Request $request): JsonResponse
    {
        try {
            $originalInput = $request->all();
            $sanitizedRequest = $this->middlewareService->sanitizeInput($request);
            $sanitizedInput = $sanitizedRequest->all();

            return response()->json([
                'success' => true,
                'data' => [
                    'original_input' => $originalInput,
                    'sanitized_input' => $sanitizedInput,
                    'changed' => $originalInput !== $sanitizedInput
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sanitize input',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get middleware statistics
     */
    public function getMiddlewareStats(): JsonResponse
    {
        try {
            $stats = $this->middlewareService->getMiddlewareStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get middleware statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register custom middleware
     */
    public function registerMiddleware(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:middleware,name',
                'handler_type' => 'required|string|in:closure,class_method',
                'handler_code' => 'required|string'
            ]);

            $name = $validated['name'];
            $handler = $this->createHandler($validated['handler_type'], $validated['handler_code']);

            $this->middlewareService->registerMiddleware($name, $handler);

            return response()->json([
                'success' => true,
                'message' => "Middleware '{$name}' registered successfully",
                'data' => [
                    'name' => $name,
                    'handler_type' => $validated['handler_type']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register middleware',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Test middleware functionality
     */
    public function testMiddleware(Request $request): JsonResponse
    {
        try {
            $tests = [];

            // Test CORS handling
            $tests['cors_headers'] = $this->testCorsHeaders($request);

            // Test request validation
            $tests['request_validation'] = $this->testRequestValidation($request);

            // Test JSON validation
            $tests['json_validation'] = $this->testJsonValidation($request);

            // Test input sanitization
            $tests['input_sanitization'] = $this->testInputSanitization($request);

            // Test suspicious activity detection
            $tests['suspicious_detection'] = $this->testSuspiciousDetection($request);

            $allPassed = array_reduce($tests, fn($carry, $result) => $carry && $result, true);

            return response()->json([
                'success' => true,
                'message' => $allPassed ? 'All tests passed' : 'Some tests failed',
                'data' => [
                    'tests' => $tests,
                    'passed' => $allPassed,
                    'total' => count($tests)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test execution failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply middleware to request
     */
    public function applyMiddleware(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'middleware' => 'required|array',
                'middleware.*' => 'string'
            ]);

            $middleware = $validated['middleware'];
            
            // Create a closure to simulate the request processing
            $next = function($req) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request processed successfully',
                    'data' => $req->all()
                ]);
            };

            $response = $this->middlewareService->applyMiddleware($request, $next, $middleware);

            return response()->json([
                'success' => true,
                'message' => 'Middleware applied successfully',
                'data' => [
                    'middleware_applied' => $middleware,
                    'response_status' => $response->getStatusCode()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply middleware',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available middleware
     */
    public function getAvailableMiddleware(): JsonResponse
    {
        try {
            $globalMiddleware = [
                'cors' => 'Handle Cross-Origin Resource Sharing',
                'request.size' => 'Validate request size limits',
                'security.headers' => 'Add security headers',
                'request.logging' => 'Log request details',
                'rate.limit' => 'Apply rate limiting'
            ];

            $routeMiddleware = [
                'auth' => 'Require authentication',
                'auth.api' => 'Require API token authentication',
                'permission' => 'Require specific permission',
                'throttle' => 'Throttle requests',
                'validate' => 'Validate input data'
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'global_middleware' => $globalMiddleware,
                    'route_middleware' => $routeMiddleware
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available middleware',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create handler from code
     */
    private function createHandler(string $type, string $code): callable
    {
        if ($type === 'closure') {
            return eval("return function(\$request, \$next) { {$code} };");
        } else {
            // Handle class method type
            [$class, $method] = explode('@', $code);
            return [app($class), $method];
        }
    }

    /**
     * Calculate risk level
     */
    private function calculateRiskLevel(array $suspicious): string
    {
        $count = count($suspicious);
        
        if ($count === 0) return 'low';
        if ($count <= 2) return 'medium';
        if ($count <= 4) return 'high';
        return 'critical';
    }

    /**
     * Test CORS headers
     */
    private function testCorsHeaders(Request $request): bool
    {
        try {
            $response = $this->middlewareService->handleCors($request, function($req) {
                return response()->json(['test' => true]);
            });
            
            return $response->headers->has('Access-Control-Allow-Origin');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test request validation
     */
    private function testRequestValidation(Request $request): bool
    {
        try {
            $response = $this->middlewareService->validateRequestSize($request, function($req) {
                return response()->json(['test' => true]);
            });
            
            return $response->getStatusCode() !== 413;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test JSON validation
     */
    private function testJsonValidation(Request $request): bool
    {
        try {
            $isValid = $this->middlewareService->validateJsonRequest($request);
            return is_bool($isValid);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test input sanitization
     */
    private function testInputSanitization(Request $request): bool
    {
        try {
            $testRequest = new Request();
            $testRequest->merge(['test' => '<script>alert("xss")</script>']);
            
            $sanitized = $this->middlewareService->sanitizeInput($testRequest);
            
            return !str_contains($sanitized->input('test'), '<script>');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test suspicious detection
     */
    private function testSuspiciousDetection(Request $request): bool
    {
        try {
            $testRequest = new Request();
            $testRequest->merge(['input' => '<script>alert("xss")</script>']);
            
            $suspicious = $this->middlewareService->detectSuspiciousActivity($testRequest);
            
            return !empty($suspicious);
        } catch (\Exception $e) {
            return false;
        }
    }
}
