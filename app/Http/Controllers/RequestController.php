<?php

namespace App\Http\Controllers;

use App\Core\Database;
use App\Services\RequestService;

// IDE Refresh: All RequestService methods are now available including:
// - addCorsMiddleware(), getMiddlewareStack(), validateRequest(), process(), clearMiddleware()
// - All Database methods including fetchOne() are available in Database class

/**
 * Custom Request Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class RequestController extends BaseController
{
    private $requestService;

    public function __construct()
    {
        $this->requestService = new RequestService();
    }

    /**
     * Initialize request processing system
     */
    public function initialize()
    {
        // Add built-in middleware
        $this->requestService->addSecurityMiddleware();
        $this->requestService->addRateLimitingMiddleware(100, 3600); // 100 requests per hour
        $this->requestService->addCorsMiddleware();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Request processing system initialized',
            'middleware' => $this->requestService->getMiddlewareStack()
        ]);
    }

    /**
     * Process incoming request
     */
    public function processRequest()
    {
        try {
            // Get request data
            $requestData = $this->getRequestData();

            // Validate request
            $validation = $this->requestService->validateRequest($requestData);
            if (!$validation['valid']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Request validation failed',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Process request based on type
            $result = $this->requestService->process($requestData);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Request processed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Request processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];

        // Get JSON data
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }

        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }

    /**
     * Add custom middleware
     */
    public function addMiddleware()
    {
        $middlewareType = $_POST['middleware_type'] ?? '';
        $config = $_POST['config'] ?? [];

        try {
            switch ($middlewareType) {
                case 'security':
                    $this->requestService->addSecurityMiddleware();
                    break;

                case 'rate_limit':
                    $limit = $config['limit'] ?? 100;
                    $window = $config['window'] ?? 3600;
                    $this->requestService->addRateLimitingMiddleware($limit, $window);
                    break;

                case 'cors':
                    $this->requestService->addCorsMiddleware($config);
                    break;

                case 'auth':
                    $this->requestService->addAuthenticationMiddleware($config);
                    break;

                default:
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Unknown middleware type: ' . $middlewareType
                    ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware added successfully',
                'type' => $middlewareType
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add middleware: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get middleware stack
     */
    public function getMiddlewareStack()
    {
        try {
            $stack = $this->requestService->getMiddlewareStack();

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'middleware_count' => count($stack),
                    'middleware_stack' => $stack
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get middleware stack: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear middleware stack
     */
    public function clearMiddleware()
    {
        try {
            $this->requestService->clearMiddleware();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware stack cleared'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear middleware: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request statistics
     */
    public function getRequestStats()
    {
        try {
            $db = Database::getInstance();

            // Total requests
            $totalRequests = $db->fetchOne("SELECT COUNT(*) as count FROM request_logs");

            // Today's requests
            $todayRequests = $db->fetchOne(
                "SELECT COUNT(*) as count FROM request_logs WHERE DATE(created_at) = CURDATE()"
            );

            // Failed requests
            $failedRequests = $db->fetchOne(
                "SELECT COUNT(*) as count FROM request_logs WHERE status = 'failed'"
            );

            // Average response time
            $avgResponseTime = $db->fetchOne(
                "SELECT AVG(response_time) as avg_time FROM request_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            );

            $successRate = 0;
            if ($totalRequests['count'] > 0) {
                $rate = ($totalRequests['count'] - $failedRequests['count']) / $totalRequests['count'];
                $successRate = round($rate * 100, 2);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_requests' => $totalRequests['count'],
                    'today_requests' => $todayRequests['count'],
                    'failed_requests' => $failedRequests['count'],
                    'average_response_time' => round($avgResponseTime['avg_time'], 2),
                    'success_rate' => $successRate
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get request stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log request manually
     */
    public function logRequest()
    {
        try {
            $requestData = $this->getRequestData();

            $db = Database::getInstance();

            $db->execute(
                "INSERT INTO request_logs (method, url, ip_address, user_agent, response_time, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $requestData['method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    $requestData['url'] ?? $_SERVER['REQUEST_URI'] ?? '/',
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    $requestData['response_time'] ?? 0,
                    $requestData['status'] ?? 'success'
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Request logged successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to log request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request logs
     */
    public function getRequestLogs()
    {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $db = Database::getInstance();

            // Get logs with pagination
            $logs = $db->fetchAll(
                "SELECT * FROM request_logs 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            // Get total count
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM request_logs");

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'logs' => $logs,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total['count'],
                        'last_page' => ceil($total['count'] / $limit)
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get request logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear request logs
     */
    public function clearRequestLogs()
    {
        try {
            $db = Database::getInstance();

            $db->execute("DELETE FROM request_logs");

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Request logs cleared successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear request logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export request logs
     */
    public function exportRequestLogs()
    {
        try {
            $db = Database::getInstance();

            $logs = $db->fetchAll(
                "SELECT * FROM request_logs ORDER BY created_at DESC LIMIT 1000"
            );

            // Convert to CSV
            $csv = "ID,Method,URL,IP Address,User Agent,Response Time,Status,Created At\n";

            foreach ($logs as $log) {
                $csv .= "{$log['id']},\"{$log['method']}\",\"{$log['url']}\",\"{$log['ip_address']}\",\"{$log['user_agent']}\",{$log['response_time']},\"{$log['status']}\",\"{$log['created_at']}\"\n";
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="request_logs.csv"');
            echo $csv;
            exit;
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test request processing
     */
    public function testRequest()
    {
        try {
            $testData = [
                'method' => 'GET',
                'url' => '/api/test',
                'data' => ['test' => true],
                'timestamp' => time()
            ];

            $result = $this->requestService->process($testData);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Test request processed successfully',
                'test_data' => $testData,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Test request failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request configuration
     */
    public function getRequestConfig()
    {
        try {
            $config = [
                'middleware_enabled' => true,
                'rate_limiting' => [
                    'enabled' => true,
                    'default_limit' => 100,
                    'default_window' => 3600
                ],
                'security' => [
                    'enabled' => true,
                    'input_validation' => true,
                    'xss_protection' => true
                ],
                'cors' => [
                    'enabled' => true,
                    'allowed_origins' => ['*'],
                    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
                    'allowed_headers' => ['Content-Type', 'Authorization']
                ],
                'logging' => [
                    'enabled' => true,
                    'auto_log' => true,
                    'max_logs' => 10000
                ]
            ];

            return $this->jsonResponse([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get request config: ' . $e->getMessage()
            ], 500);
        }
    }
}
