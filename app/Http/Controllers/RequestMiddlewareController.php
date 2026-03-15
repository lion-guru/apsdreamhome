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
}