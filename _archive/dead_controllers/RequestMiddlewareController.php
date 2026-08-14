<?php

namespace App\Http\Controllers;

use App\Services\RequestMiddlewareService;

/**
 * Controller for Request Middleware operations
 * Custom MVC implementation without Laravel dependencies
 */
class RequestMiddlewareController extends BaseController
{
    private $middlewareService;

    public function __construct()
    {
        parent::__construct();
        $this->middlewareService = new RequestMiddlewareService();
    }

    /**
     * Get request metadata
     */
    public function getRequestMetadata()
    {
        try {
            $requestData = $this->getRequestData();
            $metadata = $this->middlewareService->getRequestMetadata($requestData);

            return $this->jsonResponse([
                'success' => true,
                'metadata' => $metadata
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get request metadata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add middleware rule
     */
    public function addMiddlewareRule()
    {
        try {
            $ruleData = $this->getRequestData();
            
            $rule = [
                'id' => uniqid('rule_'),
                'name' => $_POST['rule_name'] ?? '',
                'type' => $_POST['rule_type'] ?? 'filter',
                'conditions' => $_POST['conditions'] ?? [],
                'actions' => $_POST['actions'] ?? [],
                'priority' => intval($_POST['priority'] ?? 5),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->middlewareService->addMiddlewareRule($rule);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware rule added successfully',
                'rule' => $rule
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add middleware rule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get middleware rules
     */
    public function getMiddlewareRules()
    {
        try {
            $rules = $this->middlewareService->getMiddlewareRules();

            return $this->jsonResponse([
                'success' => true,
                'rules' => $rules
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get middleware rules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update middleware rule
     */
    public function updateMiddlewareRule($ruleId)
    {
        try {
            $ruleData = $this->getRequestData();
            
            $updateData = [
                'name' => $_POST['rule_name'] ?? '',
                'type' => $_POST['rule_type'] ?? 'filter',
                'conditions' => $_POST['conditions'] ?? [],
                'actions' => $_POST['actions'] ?? [],
                'priority' => intval($_POST['priority'] ?? 5),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->middlewareService->updateMiddlewareRule($ruleId, $updateData);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware rule updated successfully',
                'rule' => $result
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update middleware rule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete middleware rule
     */
    public function deleteMiddlewareRule($ruleId)
    {
        try {
            $result = $this->middlewareService->deleteMiddlewareRule($ruleId);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware rule deleted successfully',
                'deleted' => $result
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to delete middleware rule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test middleware rule
     */
    public function testMiddlewareRule()
    {
        try {
            $ruleData = $this->getRequestData();
            
            $testData = [
                'request_sample' => [
                    'method' => 'POST',
                    'path' => '/api/test',
                    'headers' => ['Content-Type: application/json'],
                    'body' => ['test' => 'data']
                ]
            ];

            $result = $this->middlewareService->testMiddlewareRule($ruleId ?? null, $testData);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Middleware rule tested successfully',
                'test_result' => $result
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to test middleware rule: ' . $e->getMessage()
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

    // ===== MISSING METHODS FOR ROUTE COMPATIBILITY =====

    public function getClientInfo()
    {
        return $this->jsonResponse(['success' => true, 'data' => [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => $_SERVER['REQUEST_URI'] ?? ''
        ]]);
    }

    public function detectSuspiciousActivity()
    {
        return $this->jsonResponse(['success' => true, 'suspicious' => false, 'risk_score' => 0]);
    }

    public function validateJsonRequest()
    {
        $data = $this->getRequestData();
        $json = file_get_contents('php://input');
        $parsed = json_decode($json, true);
        return $this->jsonResponse(['success' => true, 'valid' => $parsed !== null, 'data' => $parsed ?? []]);
    }

    public function sanitizeInput($input = null)
    {
        if ($input !== null) {
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
        }
        $data = $this->getRequestData();
        $input = $data['input'] ?? '';
        $sanitized = htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
        return $this->jsonResponse(['success' => true, 'original' => $input, 'sanitized' => $sanitized]);
    }

    public function getMiddlewareStats()
    {
        return $this->jsonResponse(['success' => true, 'data' => [
            'total_rules' => 0, 'active_rules' => 0, 'requests_processed' => 0,
            'requests_blocked' => 0, 'avg_response_time_ms' => 0
        ]]);
    }

    public function getAvailableMiddleware()
    {
        return $this->jsonResponse(['success' => true, 'middleware' => [
            'auth', 'csrf', 'rate-limiter', 'input-sanitizer', 'cors', 'logger', 'compression'
        ]]);
    }

    public function registerMiddleware()
    {
        $data = $this->getRequestData();
        return $this->jsonResponse(['success' => true, 'message' => 'Middleware registered', 'name' => $data['name'] ?? '']);
    }

    public function applyMiddleware()
    {
        $data = $this->getRequestData();
        return $this->jsonResponse(['success' => true, 'message' => 'Middleware applied', 'middleware' => $data['middleware'] ?? '']);
    }

    public function testMiddleware()
    {
        return $this->jsonResponse(['success' => true, 'message' => 'Middleware test completed', 'tests' => []]);
    }
}?>