<?php

namespace App\Http\Controllers;

use App\Services\SecurityServiceNew;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for Security Management operations
 */
class SecurityController extends BaseController
{
    private SecurityServiceNew $securityService;

    public function __construct(SecurityServiceNew $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Run comprehensive security tests
     */
    public function runTests(): JsonResponse
    {
        try {
            $results = $this->securityService->runSecurityTests();

            return response()->json([
                'success' => true,
                'message' => 'Security tests completed successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run security tests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get security score
     */
    public function getScore(): JsonResponse
    {
        try {
            $score = $this->securityService->getSecurityScore();

            return response()->json([
                'success' => true,
                'data' => $score
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get security score',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and download HTML security report
     */
    public function generateReport(): JsonResponse
    {
        try {
            $htmlReport = $this->securityService->generateHtmlReport();
            
            // Save report to file
            $reportPath = storage_path('app/security_reports/security_test_report_' . now()->format('Y-m-d_H-i-s') . '.html');
            $this->ensureReportDirectory();
            file_put_contents($reportPath, $htmlReport);

            return response()->json([
                'success' => true,
                'message' => 'Security report generated successfully',
                'data' => [
                    'report_path' => $reportPath,
                    'download_url' => route('security.download-report', basename($reportPath))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate security report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download security report
     */
    public function downloadReport(string $filename): JsonResponse
    {
        try {
            $reportPath = storage_path('app/security_reports/' . $filename);
            
            if (!file_exists($reportPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report file not found'
                ], 404);
            }

            return response()->download($reportPath, $filename, [
                'Content-Type' => 'text/html'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate input data
     */
    public function validateInput(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'input' => 'required|string',
                'type' => 'in:email,phone,general'
            ]);

            $input = $validated['input'];
            $type = $validated['type'] ?? 'general';

            $result = [
                'original' => $input,
                'sanitized' => $this->securityService->sanitizeInput($input),
                'is_valid' => true
            ];

            // Type-specific validation
            switch ($type) {
                case 'email':
                    $result['is_valid'] = $this->securityService->validateEmail($input);
                    break;
                case 'phone':
                    $result['is_valid'] = $this->securityService->validatePhone($input);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Input validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hash password
     */
    public function hashPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string|min:8'
            ]);

            $hashedPassword = $this->securityService->hashPassword($validated['password']);

            return response()->json([
                'success' => true,
                'message' => 'Password hashed successfully',
                'data' => [
                    'hashed_password' => $hashedPassword
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password hashing failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string',
                'hash' => 'required|string'
            ]);

            $isValid = $this->securityService->verifyPassword(
                $validated['password'],
                $validated['hash']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password verification failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): JsonResponse
    {
        try {
            $token = $this->securityService->generateCsrfToken();

            return response()->json([
                'success' => true,
                'data' => [
                    'csrf_token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CSRF token generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string'
            ]);

            $isValid = $this->securityService->validateCsrfToken($validated['token']);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CSRF token validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string',
                'max_attempts' => 'nullable|integer|min:1',
                'time_window' => 'nullable|integer|min:60'
            ]);

            $key = $validated['key'];
            $maxAttempts = $validated['max_attempts'] ?? 60;
            $timeWindow = $validated['time_window'] ?? 300;

            $isAllowed = $this->securityService->checkRateLimit($key, $maxAttempts, $timeWindow);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_allowed' => $isAllowed,
                    'key' => $key,
                    'max_attempts' => $maxAttempts,
                    'time_window' => $timeWindow
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit check failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Detect suspicious activity
     */
    public function detectSuspiciousActivity(Request $request): JsonResponse
    {
        try {
            $suspicious = $this->securityService->detectSuspiciousActivity($request);

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
                'message' => 'Suspicious activity detection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event' => 'required|string',
                'context' => 'nullable|array'
            ]);

            $this->securityService->logSecurityEvent(
                $validated['event'],
                $validated['context'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Security event logged successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log security event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get security recommendations
     */
    public function getRecommendations(): JsonResponse
    {
        try {
            $recommendations = $this->securityService->getSecurityRecommendations();

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get security recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get security dashboard
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $score = $this->securityService->getSecurityScore();
            $recommendations = $this->securityService->getSecurityRecommendations();

            $dashboard = [
                'security_score' => $score,
                'recommendations' => $recommendations,
                'last_tested' => $score['last_tested'],
                'status' => $score['status'],
                'summary' => [
                    'total_tests' => $score['total_tests'],
                    'passed_tests' => $score['passed_tests'],
                    'failed_tests' => $score['failed_tests'],
                    'score_percentage' => $score['score']
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get security dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test specific security component
     */
    public function testComponent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'component' => 'required|in:https,headers,input,session,database,upload,api,rate_limit,csrf,auth'
            ]);

            $component = $validated['component'];
            $result = ['component' => $component, 'status' => 'tested'];

            // Run specific component test
            switch ($component) {
                case 'https':
                    $this->securityService->testHttpsSecurity();
                    $result['message'] = 'HTTPS security test completed';
                    break;
                case 'headers':
                    $this->securityService->testSecurityHeaders();
                    $result['message'] = 'Security headers test completed';
                    break;
                case 'input':
                    $this->securityService->testInputValidation();
                    $result['message'] = 'Input validation test completed';
                    break;
                case 'session':
                    $this->securityService->testSessionSecurity();
                    $result['message'] = 'Session security test completed';
                    break;
                case 'database':
                    $this->securityService->testDatabaseSecurity();
                    $result['message'] = 'Database security test completed';
                    break;
                case 'upload':
                    $this->securityService->testFileUploadSecurity();
                    $result['message'] = 'File upload security test completed';
                    break;
                case 'api':
                    $this->securityService->testApiSecurity();
                    $result['message'] = 'API security test completed';
                    break;
                case 'rate_limit':
                    $this->securityService->testRateLimiting();
                    $result['message'] = 'Rate limiting test completed';
                    break;
                case 'csrf':
                    $this->securityService->testCsrfProtection();
                    $result['message'] = 'CSRF protection test completed';
                    break;
                case 'auth':
                    $this->securityService->testAuthenticationSecurity();
                    $result['message'] = 'Authentication security test completed';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Component test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate risk level based on suspicious activities
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
     * Ensure report directory exists
     */
    private function ensureReportDirectory(): void
    {
        $reportDir = storage_path('app/security_reports');
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
    }
}
