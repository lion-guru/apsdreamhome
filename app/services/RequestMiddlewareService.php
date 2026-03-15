<?php

/**
 * Request Middleware Service
 * Custom MVC implementation without Laravel dependencies
 * Handles request middleware management and processing
 */

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use Exception;

class RequestMiddlewareService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get request metadata
     */
    public function getRequestMetadata($request)
    {
        try {
            $metadata = [
                'ip_address' => $request['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $request['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'method' => $request['method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'path' => $request['path'] ?? $_SERVER['REQUEST_URI'] ?? '/',
                'headers' => $request['headers'] ?? [],
                'body' => $request['body'] ?? [],
                'query_params' => $request['query_params'] ?? [],
                'timestamp' => date('Y-m-d H:i:s'),
                'session_id' => session_id() ?? null
            ];

            return $metadata;

        } catch (Exception $e) {
            error_log("Request Metadata Error: " . $e->getMessage());
            return [
                'error' => 'Failed to get request metadata',
                'ip_address' => 'unknown',
                'user_agent' => 'unknown',
                'method' => 'GET',
                'path' => '/',
                'headers' => [],
                'body' => [],
                'query_params' => [],
                'timestamp' => date('Y-m-d H:i:s'),
                'session_id' => null
            ];
        }
    }

    /**
     * Add middleware rule
     */
    public function addMiddlewareRule($rule)
    {
        try {
            $ruleData = [
                'id' => uniqid('rule_'),
                'name' => Security::sanitize($rule['name'] ?? ''),
                'type' => Security::sanitize($rule['type'] ?? 'filter'),
                'conditions' => Security::sanitize($rule['conditions'] ?? []),
                'actions' => Security::sanitize($rule['actions'] ?? []),
                'priority' => intval($rule['priority'] ?? 5),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->execute(
                "INSERT INTO middleware_rules (id, name, type, conditions, actions, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $ruleData['id'],
                    $ruleData['name'],
                    $ruleData['type'],
                    json_encode($ruleData['conditions']),
                    json_encode($ruleData['actions']),
                    $ruleData['priority'],
                    $ruleData['status'],
                    $ruleData['created_at']
                ]
            );

            return $ruleData;

        } catch (Exception $e) {
            error_log("Add Middleware Rule Error: " . $e->getMessage());
            return [
                'error' => 'Failed to add middleware rule',
                'rule' => []
            ];
        }
    }

    /**
     * Get middleware rules
     */
    public function getMiddlewareRules()
    {
        try {
            $rules = $this->db->fetchAll(
                "SELECT 
                    id,
                    name,
                    type,
                    conditions,
                    actions,
                    priority,
                    status,
                    created_at
                 FROM middleware_rules 
                 ORDER BY priority ASC, created_at DESC"
            );

            return $rules;

        } catch (Exception $e) {
            error_log("Get Middleware Rules Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update middleware rule
     */
    public function updateMiddlewareRule($ruleId, $updateData)
    {
        try {
            $data = [
                'name' => Security::sanitize($updateData['name'] ?? ''),
                'type' => Security::sanitize($updateData['type'] ?? 'filter'),
                'conditions' => Security::sanitize($updateData['conditions'] ?? []),
                'actions' => Security::sanitize($updateData['actions'] ?? []),
                'priority' => intval($updateData['priority'] ?? 5),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->execute(
                "UPDATE middleware_rules SET name = ?, type = ?, conditions = ?, actions = ?, priority = ?, updated_at = ? WHERE id = ?",
                [
                    $data['name'],
                    $data['type'],
                    json_encode($data['conditions']),
                    json_encode($data['actions']),
                    $data['priority'],
                    $data['updated_at'],
                    $ruleId
                ]
            );

            return array_merge(['id' => $ruleId], $data);

        } catch (Exception $e) {
            error_log("Update Middleware Rule Error: " . $e->getMessage());
            return [
                'error' => 'Failed to update middleware rule',
                'rule' => []
            ];
        }
    }

    /**
     * Delete middleware rule
     */
    public function deleteMiddlewareRule($ruleId)
    {
        try {
            $this->db->execute(
                "DELETE FROM middleware_rules WHERE id = ?",
                [$ruleId]
            );

            return [
                'success' => true,
                'deleted_id' => $ruleId,
                'message' => 'Middleware rule deleted successfully'
            ];

        } catch (Exception $e) {
            error_log("Delete Middleware Rule Error: " . $e->getMessage());
            return [
                'error' => 'Failed to delete middleware rule',
                'deleted_id' => null
            ];
        }
    }

    /**
     * Test middleware rule
     */
    public function testMiddlewareRule($ruleId, $testData)
    {
        try {
            // Get the rule to test
            $rule = $this->db->fetchOne(
                "SELECT * FROM middleware_rules WHERE id = ?",
                [$ruleId]
            );

            if (!$rule) {
                return [
                    'success' => false,
                    'message' => 'Middleware rule not found',
                    'test_result' => null
                ];
            }

            // Simulate middleware processing
            $result = $this->processMiddleware($rule, $testData);

            return [
                'success' => true,
                'message' => 'Middleware rule tested successfully',
                'rule' => $rule,
                'test_result' => $result
            ];

        } catch (Exception $e) {
            error_log("Test Middleware Rule Error: " . $e->getMessage());
            return [
                'error' => 'Failed to test middleware rule',
                'test_result' => null
            ];
        }
    }

    /**
     * Process middleware simulation
     */
    private function processMiddleware($rule, $testData)
    {
        $conditions = json_decode($rule['conditions'], true) ?: [];
        $actions = json_decode($rule['actions'], true) ?: [];

        // Check conditions
        foreach ($conditions as $condition) {
            if (!$this->checkCondition($condition, $testData)) {
                return [
                    'passed' => false,
                    'failed_condition' => $condition,
                    'message' => 'Condition failed: ' . $condition['field'] . ' ' . $condition['operator'] . ' ' . $condition['value']
                ];
            }
        }

        // Execute actions if all conditions pass
        $executedActions = [];
        foreach ($actions as $action) {
            $executedActions[] = $this->executeAction($action, $testData);
        }

        return [
            'passed' => true,
            'conditions_checked' => count($conditions),
            'actions_executed' => count($executedActions),
            'executed_actions' => $executedActions
        ];
    }

    /**
     * Check single condition
     */
    private function checkCondition($condition, $testData)
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? '';
        $testValue = $testData[$field] ?? '';

        switch ($operator) {
            case 'equals':
                return $testValue === $value;
            case 'not_equals':
                return $testValue !== $value;
            case 'contains':
                return strpos($testValue, $value) !== false;
            case 'not_contains':
                return strpos($testValue, $value) === false;
            case 'greater_than':
                return is_numeric($testValue) && is_numeric($value) && $testValue > $value;
            case 'less_than':
                return is_numeric($testValue) && is_numeric($value) && $testValue < $value;
            case 'regex':
                return preg_match('/' . $value . '/', $testValue);
            default:
                return false;
        }
    }

    /**
     * Execute action
     */
    private function executeAction($action, $testData)
    {
        $actionType = $action['type'] ?? 'log';
        $target = $action['target'] ?? '';

        switch ($actionType) {
            case 'log':
                $message = 'Action executed: ' . ($action['message'] ?? 'Middleware action');
                error_log($message);
                return ['executed' => true, 'message' => $message];
            case 'modify_request':
                return ['executed' => true, 'message' => 'Request modified'];
            case 'block_request':
                return ['executed' => true, 'message' => 'Request blocked'];
            default:
                return ['executed' => false, 'message' => 'Unknown action type'];
        }
    }
}