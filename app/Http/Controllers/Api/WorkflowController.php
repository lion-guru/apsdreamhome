<?php

namespace App\Http\Controllers\Api;

use \Exception;
use App\Core\Cache;

class WorkflowController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update']]);
    }

    /**
     * List all workflows
     */
    public function index()
    {
        try {
            $cacheKey = 'workflows_list';
            $cache = Cache::getInstance();

            // Check cache first
            if ($cachedData = $cache->get($cacheKey)) {
                return $this->jsonSuccess($cachedData);
            }

            $workflowModel = $this->model('AIWorkflow');
            $workflowsRaw = $workflowModel->getAllWorkflows();
            $workflows = [];

            foreach ($workflowsRaw as $row) {
                $row['trigger_config'] = \json_decode($row['trigger_config'] ?? '', true);
                $row['actions'] = \json_decode($row['actions'] ?? '', true);
                $workflows[] = $row;
            }

            // Store in cache for 1 hour
            $cache->set($cacheKey, $workflows, 3600);

            return $this->jsonSuccess($workflows);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Create a new workflow
     */
    public function store()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $input = $this->request()->all();

            $required = ['name', 'trigger_type', 'actions'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return $this->jsonError("$field is required", 400);
                }
            }

            $workflowModel = $this->model('AIWorkflow');
            $data = [
                'name' => $input['name'],
                'description' => $input['description'] ?? '',
                'trigger_type' => $input['trigger_type'],
                'trigger_config' => isset($input['trigger_config']) ? \json_encode($input['trigger_config']) : null,
                'actions' => \json_encode($input['actions']),
                'is_active' => $input['is_active'] ?? 1,
                'created_by' => $user->uid
            ];

            if ($workflowModel->create($data)) {
                Cache::getInstance()->delete('workflows_list');
                return $this->jsonSuccess(null, 'Workflow created successfully', 201);
            }

            return $this->jsonError('Failed to create workflow', 500);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Update a workflow
     */
    public function update($id)
    {
        $method = $this->request()->getMethod();
        if ($method !== 'POST' && $method !== 'PUT') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $workflowModel = $this->model('AIWorkflow');
            $workflow = $workflowModel->find($id);

            if (!$workflow) {
                return $this->jsonError('Workflow not found', 404);
            }

            $input = $this->request()->all();
            $data = [];

            $fields = ['name', 'description', 'trigger_type', 'trigger_config', 'actions', 'is_active'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $data[$field] = \in_array($field, ['trigger_config', 'actions']) ? \json_encode($input[$field]) : $input[$field];
                }
            }

            if (empty($data)) {
                return $this->jsonError('No valid fields to update', 400);
            }

            if ($workflow->update($data)) {
                Cache::getInstance()->delete('workflows_list');
                return $this->jsonSuccess(null, 'Workflow updated successfully');
            }

            return $this->jsonError('Failed to update workflow', 500);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
