<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\WorkflowEngineService;

class WorkflowController extends BaseApiController
{
    private $workflowService;

    public function __construct()
    {
        parent::__construct();
        $this->workflowService = new WorkflowEngineService();
    }

    /**
     * GET /api/workflow — List all workflows with optional status filter
     */
    public function index()
    {
        try {
            $workflows = $this->workflowService->getAllWorkflows();

            foreach ($workflows as &$wf) {
                $wf['steps'] = $this->workflowService->getWorkflowSteps((int)$wf['id']);
                $stepCount = count($wf['steps']);
                $wf['step_count'] = $stepCount;
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $workflows,
                'count' => count($workflows)
            ]);
        } catch (\Exception $e) {
            error_log('WorkflowController::index error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/workflow/{id} — Show workflow detail (definition + steps + active instances)
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->jsonResponse(['success' => false, 'error' => 'Workflow ID required'], 400);
        }

        try {
            // id can be numeric (workflow_definitions.id) or string code
            if (is_numeric($id)) {
                $db = \App\Core\Database\Database::getInstance();
                $stmt = $db->getConnection()->prepare("SELECT * FROM workflow_definitions WHERE id = ?");
                $stmt->execute([(int)$id]);
                $wf = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } else {
                $wf = $this->workflowService->getWorkflowByCode($id);
            }

            if (!$wf) {
                return $this->jsonResponse(['success' => false, 'error' => 'Workflow not found'], 404);
            }

            $wf['steps'] = $this->workflowService->getWorkflowSteps((int)$wf['id']);

            return $this->jsonResponse([
                'success' => true,
                'data' => $wf
            ]);
        } catch (\Exception $e) {
            error_log('WorkflowController::show error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * POST /api/workflow — Start a new workflow instance
     * Body: { workflow_code, entity_type, entity_id, notes? }
     */
    public function store()
    {
        try {
            $input = $this->getJsonInput();

            $code = $input['workflow_code'] ?? '';
            $entityType = $input['entity_type'] ?? '';
            $entityId = (int)($input['entity_id'] ?? 0);
            $notes = $input['notes'] ?? '';

            if (empty($code) || empty($entityType) || !$entityId) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'workflow_code, entity_type, and entity_id are required'
                ], 400);
            }

            $userId = $this->getCurrentUserId() ?? $this->getCurrentAssociateId() ?? 1;

            $result = $this->workflowService->startWorkflow($code, $entityType, $entityId, $userId, $notes);

            if ($result['success']) {
                return $this->jsonResponse($result, 201);
            }

            return $this->jsonResponse($result, 422);
        } catch (\Exception $e) {
            error_log('WorkflowController::store error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * PUT /api/workflow/{id} — Process a workflow action (approve/reject/send_back)
     * Body: { action, comments? }
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->jsonResponse(['success' => false, 'error' => 'Workflow instance ID required'], 400);
        }

        try {
            $input = $this->getJsonInput();

            $action = $input['action'] ?? '';
            $comments = $input['comments'] ?? '';

            if (!in_array($action, ['approve', 'reject', 'send_back'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'action must be one of: approve, reject, send_back'
                ], 400);
            }

            $userId = $this->getCurrentUserId() ?? $this->getCurrentAssociateId() ?? 1;
            $userType = $this->isAdmin() ? 'admin' : 'user';

            $result = $this->workflowService->processAction((int)$id, $action, $userId, $userType, $comments);

            if ($result['success']) {
                return $this->jsonResponse($result);
            }

            return $this->jsonResponse($result, 422);
        } catch (\Exception $e) {
            error_log('WorkflowController::update error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * DELETE /api/workflow/{id} — Cancel a workflow instance
     */
    public function destroy($id = null)
    {
        if (!$id) {
            return $this->jsonResponse(['success' => false, 'error' => 'Workflow instance ID required'], 400);
        }

        try {
            $instance = $this->workflowService->getInstance((int)$id);

            if (!$instance) {
                return $this->jsonResponse(['success' => false, 'error' => 'Workflow instance not found'], 404);
            }

            if (in_array($instance['status'], ['approved', 'rejected', 'cancelled'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Cannot cancel a completed workflow'], 422);
            }

            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->getConnection()->prepare(
                "UPDATE workflow_instances SET status = 'cancelled', completed_at = NOW(), notes = CONCAT(COALESCE(notes,''), '\nCancelled by admin') WHERE id = ? AND status IN ('pending','in_progress')"
            );
            $stmt->execute([(int)$id]);

            return $this->jsonResponse(['success' => true, 'message' => 'Workflow instance cancelled']);
        } catch (\Exception $e) {
            error_log('WorkflowController::destroy error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }
}
